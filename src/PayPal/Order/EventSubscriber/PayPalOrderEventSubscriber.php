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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Card3DSecure;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\SavePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderNotApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Ps_checkout;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalOrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var Ps_checkout
     */
    private $module;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @var CacheInterface
     */
    private $orderPayPalCache;

    /**
     * @param Ps_checkout $module
     * @param LoggerInterface $logger
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     * @param CacheInterface $orderPayPalCache
     */
    public function __construct(
        Ps_checkout $module,
        LoggerInterface $logger,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        CacheInterface $orderPayPalCache
    ) {
        $this->module = $module;
        $this->logger = $logger;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->orderPayPalCache = $orderPayPalCache;
    }

    /**
     * @return array[]
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalOrderCreatedEvent::class => [
                ['savePayPalOrder'],
                ['updateCache'],
            ],
            PayPalOrderApprovedEvent::class => [
                ['savePayPalOrder'],
                ['updateCache'],
                ['capturePayPalOrder'],
            ],
            PayPalOrderNotApprovedEvent::class => [
                ['savePayPalOrder'],
                ['updateCache'],
            ],
            PayPalOrderCompletedEvent::class => [
                ['savePayPalOrder'],
                ['updateCache'],
            ],
            PayPalOrderApprovalReversedEvent::class => [
                ['savePayPalOrder'],
                ['updateCache'],
            ],
        ];
    }

    /**
     * @param $event
     *
     * @return void
     *
     * @throws PayPalOrderException
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     * @throws \PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException
     */
    public function savePayPalOrder($event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('order #%s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        switch (get_class($event)) {
            case PayPalOrderCreatedEvent::class:
                $orderStatus = 'CREATED';
                break;
            case PayPalOrderApprovedEvent::class:
                $orderStatus = 'APPROVED';
                break;
            case PayPalOrderCompletedEvent::class:
                $orderStatus = 'COMPLETED';
                break;
            case PayPalOrderApprovalReversedEvent::class:
                $orderStatus = 'PENDING_APPROVAL';
                break;
            case PayPalOrderNotApprovedEvent::class:
                $orderStatus = 'PENDING';
                break;
            default:
                $orderStatus = '';
        }

        // COMPLETED is a final status, always ensure we don't update to previous status due to outdated webhook for example
        if ($psCheckoutCart->getPaypalStatus() === 'COMPLETED') {
            return;
        }

        if ($psCheckoutCart->getPaypalStatus() !== $orderStatus) {
            $this->module->getService('ps_checkout.bus.command')->handle(new SavePayPalOrderCommand(
                $event->getOrderPayPalId()->getValue(),
                $orderStatus,
                $event->getOrderPayPal()
            ));
        }
    }

    /**
     * @param PayPalOrderApprovedEvent $event
     *
     * @return void
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     * @throws PayPalOrderException
     * @throws \Exception
     */
    public function capturePayPalOrder(PayPalOrderApprovedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('order #%s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        if ($psCheckoutCart->getPaypalStatus() === 'COMPLETED') {
            return;
        }

        if ($psCheckoutCart->isExpressCheckout()) {
            return;
        }

        // Verification du panier : service ?
        $cart = new \Cart($psCheckoutCart->id_cart);

        if (!$cart->hasProducts()) {
            throw new PsCheckoutException(sprintf('Cart with id #%s has no product. Cannot capture the order.', var_export($cart->id, true)), PsCheckoutException::CART_PRODUCT_MISSING);
        }

        if ($cart->isAllProductsInStock() !== true ||
            (method_exists($cart, 'checkAllProductsAreStillAvailableInThisState') && $cart->checkAllProductsAreStillAvailableInThisState() !== true) ||
            (method_exists($cart, 'checkAllProductsHaveMinimalQuantities') && $cart->checkAllProductsHaveMinimalQuantities() !== true)
        ) {
            throw new PsCheckoutException(sprintf('Cart with id #%s contains products unavailable. Cannot capture the order.', var_export($cart->id, true)), PsCheckoutException::CART_PRODUCT_UNAVAILABLE);
        }

        if (!\Customer::customerHasAddress($cart->id_customer, $cart->id_address_invoice)) {
            throw new PsCheckoutException(sprintf('Invoice address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_invoice, true)), PsCheckoutException::CART_ADDRESS_INVOICE_INVALID);
        }

        if (!$cart->isVirtualCart() && !\Customer::customerHasAddress($cart->id_customer, $cart->id_address_delivery)) {
            throw new PsCheckoutException(sprintf('Delivery address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_delivery, true)), PsCheckoutException::CART_ADDRESS_DELIVERY_INVALID);
        }

        if (!$cart->isVirtualCart() && !array_key_exists((int) $cart->id_address_delivery, $cart->getDeliveryOptionList())) {
            throw new PsCheckoutException(sprintf('No delivery option selected for address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_delivery, true)), PsCheckoutException::CART_DELIVERY_OPTION_INVALID);
        }

        $orderPayPal = $event->getOrderPayPal();

        if ($psCheckoutCart->isHostedFields()) {
            $card3DSecure = (new Card3DSecure())->continueWithAuthorization($orderPayPal);

            $this->logger->info(
                '3D Secure authentication result',
                [
                    'authentication_result' => isset($order['payment_source']['card']['authentication_result']) ? $order['payment_source']['card']['authentication_result'] : null,
                    'decision' => str_replace(
                        [
                            (string) Card3DSecure::NO_DECISION,
                            (string) Card3DSecure::PROCEED,
                            (string) Card3DSecure::REJECT,
                            (string) Card3DSecure::RETRY,
                        ],
                        [
                            \Configuration::get('PS_CHECKOUT_LIABILITY_SHIFT_REQ') ? 'Rejected, no liability shift' : 'Proceed, without liability shift',
                            'Proceed, liability shift is possible',
                            'Rejected',
                            'Retry, ask customer to retry',
                        ],
                        (string) $card3DSecure
                    ),
                ]
            );

            switch ($card3DSecure) {
                case Card3DSecure::REJECT:
                    throw new PsCheckoutException('Card Strong Customer Authentication failure', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_FAILURE);
                case Card3DSecure::RETRY:
                    throw new PsCheckoutException('Card Strong Customer Authentication must be retried.', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN);
                case Card3DSecure::NO_DECISION:
                    if (\Configuration::get('PS_CHECKOUT_LIABILITY_SHIFT_REQ')) {
                        throw new PsCheckoutException('No liability shift to card issuer', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN);
                    }
                    break;
            }
        }

        // Check if PayPal order amount is the same than the cart amount : we tolerate a difference of more or less 0.05
        $paypalOrderAmount = (float) sprintf('%01.2f', $orderPayPal['purchase_units'][0]['amount']['value']);
        $cartAmount = (float) sprintf('%01.2f', (new \Cart($psCheckoutCart->getIdCart()))->getOrderTotal(true, \Cart::BOTH));

        if ($paypalOrderAmount + 0.05 < $cartAmount || $paypalOrderAmount - 0.05 > $cartAmount) {
            throw new PsCheckoutException('The transaction amount does not match with the cart amount.', PsCheckoutException::DIFFERENCE_BETWEEN_TRANSACTION_AND_CART);
        }

        $this->module->getService('ps_checkout.bus.command')->handle(
            new CapturePayPalOrderCommand(
                $event->getOrderPayPalId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource()
            )
        );
    }

    /**
     * @param PayPalOrderEvent $event
     *
     * @return void
     */
    public function updateCache(PayPalOrderEvent $event)
    {
        $this->orderPayPalCache->set($event->getOrderPayPalId()->getValue(), $event->getOrderPayPal());
    }
}
