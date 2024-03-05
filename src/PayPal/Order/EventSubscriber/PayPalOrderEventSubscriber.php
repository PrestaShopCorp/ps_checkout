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

use DateTime;
use Exception;
use PrestaShop\Module\PrestashopCheckout\Checkout\CheckoutChecker;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdateOrderStatusCommand;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForApprovalReversedQuery;
use PrestaShop\Module\PrestashopCheckout\Order\Query\GetOrderForApprovalReversedQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\OrderStateMapper;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PsCheckoutCart;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalOrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @var CacheInterface
     */
    private $orderPayPalCache;

    /**
     * @var CheckoutChecker
     */
    private $checkoutChecker;

    /**
     * @var CheckTransitionPayPalOrderStatusService
     */
    private $checkTransitionPayPalOrderStatusService;

    /**
     * @var OrderStateMapper
     */
    private $orderStateMapper;

    /**
     * @var CommandBusInterface
     */
    private $commandBus;
    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;

    public function __construct(
        CommandBusInterface $commandBus,
        PsCheckoutCartRepository $psCheckoutCartRepository,
        CacheInterface $orderPayPalCache,
        CheckoutChecker $checkoutChecker,
        CheckTransitionPayPalOrderStatusService $checkTransitionPayPalOrderStatusService,
        OrderStateMapper $orderStateMapper,
        PayPalConfiguration $payPalConfiguration
    ) {
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
        $this->orderPayPalCache = $orderPayPalCache;
        $this->checkoutChecker = $checkoutChecker;
        $this->checkTransitionPayPalOrderStatusService = $checkTransitionPayPalOrderStatusService;
        $this->orderStateMapper = $orderStateMapper;
        $this->commandBus = $commandBus;
        $this->payPalConfiguration = $payPalConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalOrderCreatedEvent::class => [
                ['saveCreatedPayPalOrder'],
                ['updateCache'],
            ],
            PayPalOrderApprovedEvent::class => [
                ['saveApprovedPayPalOrder'],
                ['updateCache'],
                ['capturePayPalOrder'],
            ],
            PayPalOrderCompletedEvent::class => [
                ['saveCompletedPayPalOrder'],
                ['updateCache'],
            ],
            PayPalOrderApprovalReversedEvent::class => [
                ['saveApprovalReversedPayPalOrder'],
                ['setApprovalReversedOrderStatus'],
                ['clearCache'],
            ],
        ];
    }

    public function saveCreatedPayPalOrder(PayPalOrderCreatedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByCartId($event->getCartId());

        if ($psCheckoutCart && $psCheckoutCart->getPaypalOrderId()) {
            $psCheckoutCart->paypal_status = PayPalOrderStatus::CANCELED;
            $this->psCheckoutCartRepository->save($psCheckoutCart);
        }

        $order = $event->getOrderPayPal();
        $psCheckoutCart = new PsCheckoutCart();
        $psCheckoutCart->id_cart = $event->getCartId();
        $psCheckoutCart->paypal_funding = $order['payment_source'];
        $psCheckoutCart->paypal_order = $order['id'];
        $psCheckoutCart->paypal_status = $order['status'];
        $psCheckoutCart->paypal_intent = $order['intent'];
        $psCheckoutCart->paypal_token = $order['client_token'];
        $psCheckoutCart->paypal_token_expire = (new DateTime())->modify('+3550 seconds')->format('Y-m-d H:i:s');
        $psCheckoutCart->environment = $this->payPalConfiguration->getPaymentMode();
        $psCheckoutCart->isExpressCheckout = $event->isExpressCheckout();
        $psCheckoutCart->isHostedFields = $event->isHostedFields();

        $this->psCheckoutCartRepository->save($psCheckoutCart);
    }

    public function saveApprovedPayPalOrder(PayPalOrderApprovedEvent $event)
    {
        $this->updateCheckoutCartStatus($event, PayPalOrderStatus::APPROVED);
    }

    public function saveCompletedPayPalOrder(PayPalOrderCompletedEvent $event)
    {
        $this->updateCheckoutCartStatus($event, PayPalOrderStatus::COMPLETED);
    }

    public function saveApprovalReversedPayPalOrder(PayPalOrderApprovalReversedEvent $event)
    {
        $this->updateCheckoutCartStatus($event, PayPalOrderStatus::REVERSED);
    }

    private function updateCheckoutCartStatus(PayPalOrderEvent $event, $newOrderStatus)
    {
        try {
            $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

            if (false === $psCheckoutCart) {
                throw new PsCheckoutException(sprintf('PayPal Order %s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
            }

            if (!$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($psCheckoutCart->getPaypalStatus(), $newOrderStatus)) {
                return;
            }

            $psCheckoutCart->paypal_status = $newOrderStatus;
            $this->psCheckoutCartRepository->save($psCheckoutCart);
        } catch (Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to retrieve PrestaShop cart #%d', $event->getOrderPayPalId()->getValue()), PayPalOrderException::SESSION_EXCEPTION, $exception);
        }
    }

    public function capturePayPalOrder(PayPalOrderApprovedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('PayPal Order %s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        // TODO: Remove this condition when a flag will be added on checkout completed
        if ($psCheckoutCart->isExpressCheckout()) {
            return;
        }

        if ($psCheckoutCart->getPaypalStatus() === PayPalOrderStatus::COMPLETED) {
            return;
        }

        $this->checkoutChecker->continueWithAuthorization($psCheckoutCart->getIdCart(), $event->getOrderPayPal());

        $this->commandBus->handle(
            new CapturePayPalOrderCommand(
                $event->getOrderPayPalId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource()
            )
        );
    }

    public function setApprovalReversedOrderStatus(PayPalOrderApprovalReversedEvent $event)
    {
        try {
            /** @var GetOrderForApprovalReversedQueryResult $order */
            $order = $this->commandBus->handle(
                new GetOrderForApprovalReversedQuery(
                    $event->getOrderPayPalId()->getValue()
                )
            );
        } catch (OrderNotFoundException $exception) {
            return;
        }

        if ($order->hasBeenCanceled() || $order->hasBeenPaid()) {
            return;
        }

        $this->commandBus->handle(
            new UpdateOrderStatusCommand(
                $order->getOrderId()->getValue(),
                $this->orderStateMapper->getIdByKey(OrderStateConfigurationKeys::PS_CHECKOUT_STATE_CANCELED)
            )
        );
    }

    public function updateCache(PayPalOrderEvent $event)
    {
        $currentOrderPayPal = $this->orderPayPalCache->get($event->getOrderPayPalId()->getValue());
        $newOrderPayPal = $event->getOrderPayPal();

        if ($currentOrderPayPal && !$this->checkTransitionPayPalOrderStatusService->checkAvailableStatus($currentOrderPayPal['status'], $newOrderPayPal['status'])) {
            return;
        }

        $this->orderPayPalCache->set($event->getOrderPayPalId()->getValue(), $newOrderPayPal);
    }

    public function clearCache(PayPalOrderApprovalReversedEvent $event)
    {
        $this->orderPayPalCache->delete($event->getOrderPayPalId()->getValue());
    }
}
