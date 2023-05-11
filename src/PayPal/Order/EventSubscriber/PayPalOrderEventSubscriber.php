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

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Command\UpdatePayPalOrderMatriceCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Card3DSecure;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\PrunePayPalOrderCacheCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\UpdatePayPalOrderCacheCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovalReversedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderFetchedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderNotApprovedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePsCheckoutSessionCommand;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalOrderEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @param CommandBusInterface $commandBus
     */
    public function __construct(
        CommandBusInterface $commandBus,
        LoggerInterface $logger,
        PsCheckoutCartRepository $psCheckoutCartRepository
    ) {
        $this->commandBus = $commandBus;
        $this->logger = $logger;
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalOrderCreatedEvent::class => [
                ['updatePayPalOrder'],
                ['prunePayPalOrderCache'],
            ],
            PayPalOrderApprovedEvent::class => [
                ['updatePayPalOrder'],
                ['capturePayPalOrder'],
                ['prunePayPalOrderCache'],
            ],
            PayPalOrderNotApprovedEvent::class => [
                ['updatePayPalOrder'],
            ],
            PayPalOrderCompletedEvent::class => [
                ['updatePayPalOrder'],
                ['updatePayPalOrderMatrice'],
                ['prunePayPalOrderCache'],
            ],
            PayPalOrderApprovalReversedEvent::class => [
                ['updatePayPalOrder'],
                ['prunePayPalOrderCache'],
            ],
            PayPalOrderFetchedEvent::class => [
                ['updatePayPalOrderCache'],
            ],
        ];
    }

    /**
     * @param PayPalOrderEvent $event
     *
     * @return void
     */
    public function updatePayPalOrder($event)
    {
        // @todo We don't have a dedicated table for order data storage in database yet
        // But we can save some data in current pscheckout_cart table

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

        $this->commandBus->handle(new UpdatePsCheckoutSessionCommand(
            $event->getOrderPayPalId()->getValue(),
            $psCheckoutCart->getIdCart(),
            $psCheckoutCart->getPaypalFundingSource(),
            $psCheckoutCart->getPaypalIntent(),
            $orderStatus,
            $psCheckoutCart->getPaypalClientToken(),
            $psCheckoutCart->paypal_token_expire,
            $psCheckoutCart->paypal_authorization_expire,
            $psCheckoutCart->isHostedFields(),
            $psCheckoutCart->isExpressCheckout()
        ));
    }

    /**
     * @param PayPalOrderApprovedEvent $event
     *
     * @return void
     *
     * @throws PsCheckoutException
     * @throws \PrestaShopException
     * @throws PayPalOrderException
     */
    public function capturePayPalOrder(PayPalOrderApprovedEvent $event)
    {
        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($event->getOrderPayPalId()->getValue());

        if (false === $psCheckoutCart) {
            throw new PsCheckoutException(sprintf('order #%s is not linked to a cart', $event->getOrderPayPalId()->getValue()), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        // ExpressCheckout require buyer select a delivery option, we have to check if cart is ready to payment
        if ($psCheckoutCart->isExpressCheckout() && $psCheckoutCart->getPaypalFundingSource() === 'paypal') {
            $this->logger->info('PayPal Order cannot be captured.');
            return;
        }

        // @todo Always check if Cart is ready to payment before (quantities, stocks, invoice address, delivery address, delivery option...)

        if ($psCheckoutCart->isHostedFields()) {
            $card3DSecure = (new Card3DSecure())->continueWithAuthorization($event->getOrder());

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

        // This should mainly occur for APMs
        $this->commandBus->handle(
            new CapturePayPalOrderCommand(
                $event->getOrderPayPalId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource()
            )
        );
    }

    public function updatePayPalOrderCache(PayPalOrderEvent $event)
    {
        $this->commandBus->handle(new UpdatePayPalOrderCacheCommand(
            $event->getOrderPayPalId()->getValue(),
            $event->getOrder()
        ));
    }

    /**
     * @param PayPalOrderEvent $event
     *
     * @return void
     */
    public function prunePayPalOrderCache(PayPalOrderEvent $event)
    {
        $this->commandBus->handle(
            new PrunePayPalOrderCacheCommand($event->getOrderPayPalId()->getValue())
        );
    }

    public function updatePayPalOrderMatrice(PayPalOrderCompletedEvent $event)
    {
        $this->commandBus->handle(
            new UpdatePayPalOrderMatriceCommand($event->getOrderPayPalId()->getValue())
        );
    }

    public function updateOrderStatus(PayPalOrderCompletedEvent $event)
    {
        // TODO : Check if PrestaShop order status need to be updated
    }
}
