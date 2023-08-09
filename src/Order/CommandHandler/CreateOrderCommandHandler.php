<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\Order\CommandHandler;

use Cart;
use Currency;
use Exception;
use Order;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Context\ContextStateManager;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider;
use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Event\OrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateInstaller;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\OrderStateMapper;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShopCollection;
use PrestaShopDatabaseException;
use PrestaShopException;
use Ps_checkout;
use PsCheckoutCart;
use Validate;

class CreateOrderCommandHandler extends AbstractOrderCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ContextStateManager
     */
    private $contextStateManager;

    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @var OrderStateMapper
     */
    private $psOrderStateMapper;

    /**
     * @var Ps_checkout
     */
    private $module;

    /**
     * @var CheckOrderAmount
     */
    private $checkOrderAmount;

    public function __construct(
        ContextStateManager $contextStateManager,
        EventDispatcherInterface $eventDispatcher,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        OrderStateMapper $psOrderStateMapper,
        Ps_checkout $module,
        CheckOrderAmount $checkOrderAmount
    ) {
        $this->contextStateManager = $contextStateManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->psOrderStateMapper = $psOrderStateMapper;
        $this->module = $module;
        $this->checkOrderAmount = $checkOrderAmount;
    }

    /**
     * @param CreateOrderCommand $command
     *
     * @return void
     *
     * @throws CartException
     * @throws OrderException
     * @throws OrderNotFoundException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws PsCheckoutException
     */
    public function handle(CreateOrderCommand $command)
    {
        /** @var PsCheckoutCart $psCheckoutCart */
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($command->getOrderPayPalId()->getValue());

        if (Validate::isLoadedObject($this->contextStateManager->getContext()->cart) && (int) $this->contextStateManager->getContext()->cart->id === $psCheckoutCart->getIdCart()) {
            $cart = $this->contextStateManager->getContext()->cart;
        } else {
            $cart = new Cart($psCheckoutCart->getIdCart());
        }

        if (!Validate::isLoadedObject($cart)) {
            throw new PsCheckoutException('Cart not found', PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        $orders = new PrestaShopCollection(Order::class);
        $orders->where('id_cart', '=', (int) $cart->id);

        if ($orders->count()) {
            return;
        }

        $products = $cart->getProducts(true);

        if (empty($products)) {
            throw new PsCheckoutException(sprintf('Cart with id %s has no product. Cannot create the order.', var_export($cart->id, true)), PsCheckoutException::CART_PRODUCT_MISSING);
        }

        $fundingSource = $psCheckoutCart->getPaypalFundingSource();
        $transactionId = $orderStateId = $paidAmount = '';
        $capture = $command->getCapturePayPal();
        $currencyId = (int) $cart->id_currency;

        if ($capture) {
            $transactionId = $capture['id'];
            $paidAmount = $capture['status'] === 'COMPLETED' ? $capture['amount']['value'] : '';
            $currencyId = Currency::getIdByIsoCode($capture['amount']['currency_code'], (int) $cart->id_shop);
        }

        try {
            if ($paidAmount) {
                switch ($this->checkOrderAmount->checkAmount((string) $paidAmount, (string) $cart->getOrderTotal(true, \Cart::BOTH))) {
                    case CheckOrderAmount::ORDER_NOT_FULL_PAID:
                        $orderStateId = $this->psOrderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PARTIALLY_PAID);
                        break;
                    case CheckOrderAmount::ORDER_FULL_PAID:
                        $orderStateId = $this->psOrderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_COMPLETED);
                        break;
                    case CheckOrderAmount::ORDER_TO_MUCH_PAID:
                        $orderStateId = $this->psOrderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_COMPLETED);
                }
            } else {
                $orderStateId = $this->psOrderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING);
            }
        } catch (OrderStateException $exception) {
            if ($exception->getCode() === OrderStateException::INVALID_MAPPING) {
                (new OrderStateInstaller())->install();
            }
            $orderStateId = $this->psOrderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_PENDING);
        }

        /** @var FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->module->getService('ps_checkout.funding_source.translation');

        if ($this->shouldSetCartContext($this->contextStateManager->getContext(), $cart)) {
            $this->setCartContext($this->contextStateManager, $cart);
        }

        $extraVars = [];

        // Transaction identifier is needed only when an OrderPayment will be created
        // It requires a positive paid amount and an OrderState that's consider the associated order as validated.
        if ($paidAmount && $transactionId) {
            $extraVars['transaction_id'] = $transactionId;
        }

        try {
            $this->module->validateOrder(
                (int) $cart->id,
                $orderStateId,
                $paidAmount,
                $fundingSourceTranslationProvider->getPaymentMethodName($fundingSource),
                null,
                $extraVars,
                $currencyId,
                false,
                $cart->secure_key
            );
        } catch (Exception $exception) {
            throw new OrderException(sprintf('Failed to create order from Cart #%s.', var_export($cart->id, true)), OrderException::FAILED_ADD_ORDER, $exception);
        }

        $orders = new PrestaShopCollection(Order::class);
        $orders->where('id_cart', '=', (int) $cart->id);

        if (!$orders->count()) {
            throw new OrderNotFoundException(sprintf('Unable to retrieve order identifier from Cart #%s.', var_export($cart->id, true)), OrderNotFoundException::NOT_FOUND);
        }

        foreach ($orders as $order) {
            $this->eventDispatcher->dispatch(new OrderCreatedEvent((int) $order->id, (int) $cart->id));
        }
    }
}
