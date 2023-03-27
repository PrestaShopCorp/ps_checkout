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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\Order\Command\CreateOrderCommand;
use PrestaShop\Module\PrestashopCheckout\Order\CommandHandler\CreateOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\Order\State\CommandHandler\UpdateOrderStateCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\QueryHandler\GetPayPalOrderQueryHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeniedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureRefundedEvent;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShop\Module\PrestashopCheckout\Session\Command\UpdatePsCheckoutSessionCommand;
use PrestaShop\Module\PrestashopCheckout\Session\CommandHandler\UpdatePsCheckoutSessionCommandHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PayPalCaptureEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var UpdatePsCheckoutSessionCommandHandler
     */
    private $updatePsCheckoutSessionCommandHandler;

    /**
     * @var GetPayPalOrderQueryHandler
     */
    private $getPayPalOrderQueryHandler;

    /**
     * @var CreateOrderCommandHandler;
     */
    private $createOrderCommandHandler;
    /**
     * @var UpdateOrderStateCommandHandler
     */
    private $updateOrderStateCommandHandler;

    const CAPTURE_STATUS_PENDING = 'PENDING';
    const CAPTURE_STATE_ID_PENDING = 2;

    const CAPTURE_STATUS_DENIED = 'DENIED';
    const CAPTURE_STATE_ID_DENIED = 2;

    const CAPTURE_STATUS_VOIDED = 'VOIDED';
    const CAPTURE_STATE_ID_VOIDED = 2;

    const CAPTURE_STATUS_COMPLETED = 'COMPLETED';
    const CAPTURE_STATE_ID_COMPLETED = 2;

    const CAPTURE_STATUS_DECLINED = 'DECLINED';
    const CAPTURE_STATE_ID_DECLINED = 2;

    const CAPTURE_STATUS_REFUNDED = 'REFUNDED';
    const CAPTURE_STATE_ID_REFUNDED = 2;



    public function __construct(UpdatePsCheckoutSessionCommandHandler $updatePsCheckoutSessionCommandHandler,GetPayPalOrderQueryHandler $getPayPalOrderQueryHandler,CreateOrderCommandHandler $createOrderCommandHandler,UpdateOrderStateCommandHandler $updateOrderStateCommandHandler)
    {
        $this->updatePsCheckoutSessionCommandHandler = $updatePsCheckoutSessionCommandHandler;
        $this->getPayPalOrderQueryHandler = $getPayPalOrderQueryHandler;
        $this->createOrderCommandHandler = $createOrderCommandHandler;
        $this->updateOrderStateCommandHandler = $updateOrderStateCommandHandler;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PayPalCaptureCompletedEvent::class => 'onPayPalCaptureCompleted',
            PayPalCaptureDeniedEvent::class => 'onPayPalCaptureDenied',
            PayPalCaptureRefundedEvent::class => 'onPayPalCaptureRefunded',
        ];
    }

    /**
     * @param PayPalCaptureCompletedEvent $event
     *
     * @return void
     */
    public function onPayPalCaptureCompleted(PayPalCaptureCompletedEvent $event)
    {
        // Update data on pscheckout_cart table
        $psCheckoutCartRepository = new PsCheckoutCartRepository();
        $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalCaptureId()->getValue());
        $this->updatePsCheckoutSessionCommandHandler->handle(
            new UpdatePsCheckoutSessionCommand(
                $psCheckoutCart->getPaypalOrderId(),
                $event->getCartId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource(),
                $psCheckoutCart->getPaypalIntent(),
                self::CAPTURE_STATUS_COMPLETED,
                $event->getToken(),
                (new DateTime())->setTimestamp($event->getCreatedAt())->modify("+{$event->getExpireIn()} seconds")->format('Y-m-d H:i:s'),
                $psCheckoutCart->paypal_authorization_expire,
                $psCheckoutCart->isHostedFields(),
                $psCheckoutCart->isExpressCheckout()
            )
        );
        // Check if an Order on PrestaShop already exist
        $getPayPalOrderQueryResult = $this->getPayPalOrderQueryHandler->handle(new GetPayPalOrderQuery($event->getPayPalCaptureId()->getValue()));
        if(!isset($getPayPalOrderQueryResult->getOrder()['id_order']))
        {
            $this->createOrderCommandHandler->handle(new CreateOrderCommand($psCheckoutCart->getIdCart(),'PayPal',self::CAPTURE_STATE_ID_COMPLETED,'PayPal',$event->getPayPalCaptureId()->getValue(),'beacuoup'));
        }
        // Check if the OrderState of Order on PrestaShop need to be updated

    }

    /**
     * @param PayPalCaptureDeniedEvent $event
     *
     * @return void
     */
    public function onPayPalCaptureDenied(PayPalCaptureDeniedEvent $event)
    {
        // Update data on pscheckout_cart table
        $psCheckoutCartRepository = new PsCheckoutCartRepository();
        $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalCaptureId()->getValue());
        $this->updatePsCheckoutSessionCommandHandler->handle(
            new UpdatePsCheckoutSessionCommand(
                $psCheckoutCart->getPaypalOrderId(),
                $event->getCartId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource(),
                $psCheckoutCart->getPaypalIntent(),
                self::CAPTURE_STATUS_DENIED,
                $event->getToken(),
                (new DateTime())->setTimestamp($event->getCreatedAt())->modify("+{$event->getExpireIn()} seconds")->format('Y-m-d H:i:s'),
                $psCheckoutCart->paypal_authorization_expire,
                $psCheckoutCart->isHostedFields(),
                $psCheckoutCart->isExpressCheckout()
            )
        );
        // Check if an Order on PrestaShop already exist
        $getPayPalOrderQueryResult = $this->getPayPalOrderQueryHandler->handle(new GetPayPalOrderQuery($event->getPayPalCaptureId()->getValue()));
        if(!isset($getPayPalOrderQueryResult->getOrder()['id_order']))
        {
            $this->createOrderCommandHandler->handle(new CreateOrderCommand($psCheckoutCart->getIdCart(),'PayPal',2,'PayPal',$event->getPayPalCaptureId()->getValue(),'beacuoup'));
        }
        // Check if the OrderState of Order on PrestaShop need to be updated
    }

    /**
     * @param PayPalCaptureRefundedEvent $event
     *
     * @return void
     */
    public function onPayPalCaptureRefunded(PayPalCaptureRefundedEvent $event)
    {
        // Update data on pscheckout_cart table
        $psCheckoutCartRepository = new PsCheckoutCartRepository();
        $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($event->getPayPalCaptureId()->getValue());
        $this->updatePsCheckoutSessionCommandHandler->handle(
            new UpdatePsCheckoutSessionCommand(
                $psCheckoutCart->getPaypalOrderId(),
                $event->getCartId()->getValue(),
                $psCheckoutCart->getPaypalFundingSource(),
                $psCheckoutCart->getPaypalIntent(),
                self::CAPTURE_STATUS_REFUNDED,
                $event->getToken(),
                (new DateTime())->setTimestamp($event->getCreatedAt())->modify("+{$event->getExpireIn()} seconds")->format('Y-m-d H:i:s'),
                $psCheckoutCart->paypal_authorization_expire,
                $psCheckoutCart->isHostedFields(),
                $psCheckoutCart->isExpressCheckout()
            )
        );
        // Check if an Order on PrestaShop already exist
        $getPayPalOrderQueryResult = $this->getPayPalOrderQueryHandler->handle(new GetPayPalOrderQuery($event->getPayPalCaptureId()->getValue()));
        if(!isset($getPayPalOrderQueryResult->getOrder()['id_order']))
        {
            $this->createOrderCommandHandler->handle(new CreateOrderCommand($psCheckoutCart->getIdCart(),'PayPal',2,'PayPal',$event->getPayPalCaptureId()->getValue(),'beacuoup'));
        }
        // Check if the OrderState of Order on PrestaShop need to be updated
        if($psCheckoutCart->getPaypalStatus() !== self::CAPTURE_STATUS_REFUNDED)
        {
            $this->updateOrderStateCommandHandler->handle(new updateOrderStateCommand());
        }
        // Check if refund has been executed on PrestaShop
    }
}
