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

use Configuration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeclinedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\PayPalCaptureStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPalProcessorResponse;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use Psr\SimpleCache\CacheInterface;

class CapturePayPalOrderCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var CacheInterface
     */
    private $orderPayPalCache;

    /**
     * @var MaaslandHttpClient
     */
    private $maaslandHttpClient;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;
    /**
     * @var PayPalCustomerRepository
     */
    private $payPalCustomerRepository;
    /**
     * @var PayPalOrderRepository
     */
    private $payPalOrderRepository;

    public function __construct(
        MaaslandHttpClient $maaslandHttpClient,
        EventDispatcherInterface $eventDispatcher,
        CacheInterface $orderPayPalCache,
        PrestaShopContext $prestaShopContext,
        PayPalCustomerRepository $payPalCustomerRepository,
        PayPalOrderRepository $payPalOrderRepository
    ) {
        $this->maaslandHttpClient = $maaslandHttpClient;
        $this->eventDispatcher = $eventDispatcher;
        $this->orderPayPalCache = $orderPayPalCache;
        $this->prestaShopContext = $prestaShopContext;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->payPalOrderRepository = $payPalOrderRepository;
    }

    public function handle(CapturePayPalOrderCommand $capturePayPalOrderCommand)
    {
        $merchantId = Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT', null, null, $this->prestaShopContext->getShopId());

        $payload = [
            'mode' => $capturePayPalOrderCommand->getFundingSource(),
            'orderId' => $capturePayPalOrderCommand->getOrderId()->getValue(),
            'payee' => ['merchant_id' => $merchantId],
        ];

        $order = $this->payPalOrderRepository->getPayPalOrderById($capturePayPalOrderCommand->getOrderId());

        if ($order->checkCustomerIntent(PayPalOrder::CUSTOMER_INTENT_USES_VAULTING)) {
            $payload['vault'] = true;
        }

        $response = $this->maaslandHttpClient->captureOrder($payload);

        $orderPayPal = json_decode($response->getBody(), true);

        $payPalOrderFromCache = $this->orderPayPalCache->get($orderPayPal['id']);

        $orderPayPal = array_replace_recursive($payPalOrderFromCache, $orderPayPal);

        $capturePayPal = $orderPayPal['purchase_units'][0]['payments']['captures'][0];

        if (isset($orderPayPal['payment_source'][$capturePayPalOrderCommand->getFundingSource()]['attributes']['vault'])) {
            $vault = $orderPayPal['payment_source'][$capturePayPalOrderCommand->getFundingSource()]['attributes']['vault'];
            if (isset($vault['customer']['id'])) {
                try {
                    $payPalCustomerId = new PayPalCustomerId($vault['customer']['id']);
                    $customerId = new CustomerId($this->prestaShopContext->getCustomerId());
                    $this->payPalCustomerRepository->save($customerId, $payPalCustomerId);
                } catch (\Exception $exception) {
                }
            }

            if (isset($vault['id'])) {
                $resource = $vault;
                $resource['metadata'] = [
                    'order_id' => $orderPayPal['id'],
                ];
                $paymentSource = $orderPayPal['payment_source'];
                unset($paymentSource[$capturePayPalOrderCommand->getFundingSource()]['attributes']['vault']);
                $resource['payment_source'] = $paymentSource;
                $resource['payment_source'][$capturePayPalOrderCommand->getFundingSource()]['verification_status'] = $resource['status'];

                $this->eventDispatcher->dispatch(new PaymentTokenCreatedEvent(
                    $resource,
                    $merchantId
                ));
            }
        }

        if ($orderPayPal['status'] === PayPalOrderStatus::COMPLETED) {
            $this->eventDispatcher->dispatch(new PayPalOrderCompletedEvent($orderPayPal['id'], $orderPayPal));
        }

        if ($capturePayPal['status'] === PayPalCaptureStatus::PENDING) {
            $this->eventDispatcher->dispatch(new PayPalCapturePendingEvent($capturePayPal['id'], $orderPayPal['id'], $capturePayPal));
        }

        if ($capturePayPal['status'] === PayPalCaptureStatus::COMPLETED) {
            $this->eventDispatcher->dispatch(new PayPalCaptureCompletedEvent($capturePayPal['id'], $orderPayPal['id'], $capturePayPal));
        }

        if ($capturePayPal['status'] === PayPalCaptureStatus::DECLINED || $capturePayPal['status'] === PayPalCaptureStatus::FAILED) {
            $this->eventDispatcher->dispatch(new PayPalCaptureDeclinedEvent($capturePayPal['id'], $orderPayPal['id'], $capturePayPal));
        }

        if (
            PayPalCaptureStatus::DECLINED === $capturePayPal['status']
            && false === empty($orderPayPal['payment_source'])
            && false === empty($orderPayPal['payment_source']['card'])
            && false === empty($capturePayPal['processor_response'])
        ) {
            $payPalProcessorResponse = new PayPalProcessorResponse(
                isset($orderPayPal['payment_source']['card']['brand']) ? $orderPayPal['payment_source']['card']['brand'] : null,
                isset($orderPayPal['payment_source']['card']['type']) ? $orderPayPal['payment_source']['card']['type'] : null,
                isset($capturePayPal['processor_response']['avs_code']) ? $capturePayPal['processor_response']['avs_code'] : null,
                isset($capturePayPal['processor_response']['cvv_code']) ? $capturePayPal['processor_response']['cvv_code'] : null,
                isset($capturePayPal['processor_response']['response_code']) ? $capturePayPal['processor_response']['response_code'] : null
            );
            $payPalProcessorResponse->throwException();
        } elseif (PayPalCaptureStatus::DECLINED === $capturePayPal['status'] || PayPalCaptureStatus::FAILED === $capturePayPal['status']) {
            throw new PsCheckoutException('PayPal declined the capture', PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED);
        }
    }
}
