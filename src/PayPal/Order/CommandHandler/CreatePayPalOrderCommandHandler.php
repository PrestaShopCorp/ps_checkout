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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler;

use Exception;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\UnprocessableEntityException;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CreatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\ShopContext;

class CreatePayPalOrderCommandHandler
{
    /**
     * @var MaaslandHttpClient
     */
    private $maaslandHttpClient;
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var PayPalCustomerRepository
     */
    private $payPalCustomerRepository;
    /**
     * @var PaymentTokenRepository
     */
    private $paymentTokenRepository;
    /**
     * @var ShopContext
     */
    private $shopContext;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;

    public function __construct(
        MaaslandHttpClient $maaslandHttpClient,
        ShopContext $shopContext,
        PrestaShopContext $prestaShopContext,
        EventDispatcherInterface $eventDispatcher,
        PayPalCustomerRepository $payPalCustomerRepository,
        PaymentTokenRepository $paymentTokenRepository
    ) {
        $this->maaslandHttpClient = $maaslandHttpClient;
        $this->shopContext = $shopContext;
        $this->eventDispatcher = $eventDispatcher;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->prestaShopContext = $prestaShopContext;
    }

    /**
     * @param CreatePayPalOrderCommand $command
     *
     * @return void
     *
     * @throws CartNotFoundException
     * @throws PayPalOrderException
     * @throws InvalidRequestException
     * @throws NotAuthorizedException
     * @throws UnprocessableEntityException
     * @throws Exception
     * @throws PsCheckoutException
     */
    public function handle(CreatePayPalOrderCommand $command)
    {
        $cartPresenter = (new CartPresenter())->present();
        $builder = new OrderPayloadBuilder($cartPresenter);

        try {
            $customerId = $this->prestaShopContext->getCustomerId();
            $payPalCustomerId = $this->payPalCustomerRepository->findPayPalCustomerIdByCustomerId(new CustomerId($customerId));
        } catch (PsCheckoutException $exception) {
            $payPalCustomerId = null;
        }

        $customerIntent = [];

        if ($payPalCustomerId) {
            $builder->setPaypalCustomerId($payPalCustomerId->getValue());
        }

        if ($command->getPaymentTokenId()) {
            $customerIntent[] = PayPalOrder::CUSTOMER_INTENT_USES_VAULTING;
            $paymentToken = $this->paymentTokenRepository->findById($command->getPaymentTokenId());

            if (!$paymentToken || !$payPalCustomerId || $paymentToken->getPayPalCustomerId()->getValue() !== $payPalCustomerId->getValue()) {
                throw new PsCheckoutException('Payment token does not belong to the customer');
            }
            $builder->setPaypalVaultId($command->getPaymentTokenId()->getValue());
        }

        $builder->setIsCard($command->getFundingSource() === 'card' && ($command->isHostedFields() || $command->getPaymentTokenId()));
        $builder->setExpressCheckout($command->isExpressCheckout());
        $builder->setFundingSource($command->getFundingSource());
        $builder->setSavePaymentMethod($command->vault());
        $builder->setVault($command->getPaymentTokenId() || $command->vault());

        if ($this->shopContext->isShop17()) {
            // Build full payload in 1.7
            $builder->buildFullPayload();
        } else {
            // if on 1.6 always build minimal payload
            $builder->buildMinimalPayload();
        }

        $response = $this->maaslandHttpClient->createOrder($builder->presentPayload()->getArray());
        $order = json_decode($response->getBody(), true);

        if ($command->vault()) {
            $customerIntent[] = PayPalOrder::CUSTOMER_INTENT_VAULT;
            $customerIntent[] = PayPalOrder::CUSTOMER_INTENT_USES_VAULTING;
        }

        if ($command->favorite()) {
            $customerIntent[] = PayPalOrder::CUSTOMER_INTENT_FAVORITE;
        }

        $this->eventDispatcher->dispatch(new PayPalOrderCreatedEvent(
            $order['id'],
            $order,
            $command->getCartId()->getValue(),
            $command->getFundingSource(),
            $command->isHostedFields(),
            $command->isExpressCheckout(),
            $customerIntent,
            $command->getPaymentTokenId()
        ));
    }
}
