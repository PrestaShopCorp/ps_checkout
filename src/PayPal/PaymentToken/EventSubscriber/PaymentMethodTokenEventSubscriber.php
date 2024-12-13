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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\EventSubscriber;

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Command\SavePaymentTokenCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Entity\PaymentToken;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenDeletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenDeletionInitiatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use Ps_checkout;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaymentMethodTokenEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var PayPalOrderRepository
     */
    private $payPalOrderRepository;
    /**
     * @var PaymentTokenRepository
     */
    private $paymentTokenRepository;

    public function __construct(PayPalOrderRepository $payPalOrderRepository, PaymentTokenRepository $paymentTokenRepository)
    {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            PaymentTokenCreatedEvent::class => [
                ['saveCreatedPaymentMethodToken'],
            ],
            PaymentTokenDeletedEvent::class => [
                ['deletePaymentMethodToken'],
            ],
            PaymentTokenDeletionInitiatedEvent::class => [
                [''], // No sé
            ],
        ];
    }

    public function saveCreatedPaymentMethodToken(PaymentTokenCreatedEvent $event)
    {
        $resource = $event->getResource();
        $orderId = isset($resource['metadata']['order_id']) ? $resource['metadata']['order_id'] : null;
        $setFavorite = false;

        if ($orderId) {
            try {
                $order = $this->payPalOrderRepository->getPayPalOrderById(new PayPalOrderId($orderId));
                $setFavorite = $order->checkCustomerIntent(PayPalOrder::CUSTOMER_INTENT_FAVORITE);
            } catch (\Exception $exception) {
            }
        }

        $token = new PaymentToken(
            (new PaymentTokenId($resource['id']))->getValue(),
            (new PayPalCustomerId($resource['customer']['id']))->getValue(),
            array_keys($resource['payment_source'])[0],
            $resource,
            $event->getMerchantId(),
            $resource['payment_source'][array_keys($resource['payment_source'])[0]]['verification_status'],
            $setFavorite
        );
        $this->paymentTokenRepository->save($token);

        if ($setFavorite) {
            $this->paymentTokenRepository->setTokenFavorite($token->getId());
        }
    }

    public function deletePaymentMethodToken(PaymentTokenDeletedEvent $event)
    {
        $this->paymentTokenRepository->deleteById($event->getResource()['id']);
    }
}
