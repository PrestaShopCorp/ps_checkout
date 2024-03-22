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

use PrestaShop\Module\PrestashopCheckout\Api\Payment\PaymentService;
use PrestaShop\Module\PrestashopCheckout\Cart\CartRepositoryInterface;
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Exception\UnprocessableEntityException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CreatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CreatePayPalOrderPayloadBuilderInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\CreatePayPalOrderResponse;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\Serializer\ObjectSerializerInterface;
use PrestaShop\PrestaShop\Core\Foundation\IoC\Exception;

class CreatePayPalOrderCommandHandler
{
    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CreatePayPalOrderPayloadBuilderInterface
     */
    private $createPayPalOrderPayloadBuilder;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var PaymentService
     */
    private $paymentService;
    /**
     * @var ObjectSerializerInterface
     */
    private $objectSerializer;
    /**
     * @var PayPalCustomerRepository
     */
    private $payPalCustomerRepository;
    /**
     * @var PaymentTokenRepository
     */
    private $paymentTokenRepository;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        CreatePayPalOrderPayloadBuilderInterface $createPayPalOrderPayloadBuilder,
        EventDispatcherInterface $eventDispatcher,
        PaymentService $paymentService,
        ObjectSerializerInterface $objectSerializer,
        PayPalCustomerRepository $payPalCustomerRepository,
        PaymentTokenRepository $paymentTokenRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->createPayPalOrderPayloadBuilder = $createPayPalOrderPayloadBuilder;
        $this->eventDispatcher = $eventDispatcher;
        $this->paymentService = $paymentService;
        $this->objectSerializer = $objectSerializer;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->paymentTokenRepository = $paymentTokenRepository;
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
     */
    public function handle(CreatePayPalOrderCommand $command)
    {
        $cart = $this->cartRepository->getCartById($command->getCartId());
        // TODO: check if payment token belongs to current customer
        $payload = $this->createPayPalOrderPayloadBuilder->build($cart, $command->getFundingSource(), $command->vault(), $command->getPaymentTokenId());
        $order = $this->paymentService->createOrder($payload);
        $customerIntent = [];

        if ($command->vault()) {
            $customerIntent[] = PayPalOrder::CUSTOMER_INTENT_VAULT;

            try {
                $payPalCustomerId = new PayPalCustomerId($order->getPaymentSource()->getPaypal()->getAttributes()->getVault()->getCustomer()->getId());
                $customerId = new CustomerId($cart->getCustomer()->getId());
                $this->payPalCustomerRepository->save($customerId, $payPalCustomerId);
            } catch (\Exception $exception) {
            }
        }

        if ($command->favorite()) {
            $customerIntent[] = PayPalOrder::CUSTOMER_INTENT_FAVORITE;
            if ($command->getPaymentTokenId()) {
                $this->paymentTokenRepository->setTokenFavorite($command->getPaymentTokenId());
            }
        }

        if ($order->getPaymentSource()->getPaypal()->getAttributes()->getVault()->getCustomer()->getId()) {
            $this->eventDispatcher->dispatch(new PayPalOrderCreatedEvent(
            $order->getId(),
            $this->objectSerializer->toArray($order, false, true),
            $command->getCartId()->getValue(),
            $command->getFundingSource(),
            $command->isHostedFields(),
            $command->isExpressCheckout(),
            !empty($customerIntent) ? implode(',', $customerIntent) : null
        ));
        }
    }
}
