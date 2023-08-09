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
use Context;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeclinedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\PayPalCaptureStatus;
use PrestaShop\Module\PrestashopCheckout\PayPalError;
use PrestaShop\Module\PrestashopCheckout\PayPalProcessorResponse;

class CapturePayPalOrderCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(CapturePayPalOrderCommand $capturePayPalOrderCommand)
    {
        $context = Context::getContext();
        $merchantId = Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT', null, null, $context->shop->id);
        $apiOrder = new Order($context->link);
        $response = $apiOrder->capture(
            $capturePayPalOrderCommand->getOrderId()->getValue(),
            $merchantId,
            $capturePayPalOrderCommand->getFundingSource()
        );

        if (false === $response['status']) {
            if (isset($response['body']['details'][0]['issue'])) {
                (new PayPalError($response['body']['details'][0]['issue']))->throwException();
            }

            if (isset($response['body']['name'])) {
                (new PayPalError($response['body']['name']))->throwException();
            }

            if (false === empty($response['exceptionMessage']) && false === empty($response['exceptionCode'])) {
                throw new PsCheckoutException($response['exceptionMessage'], (int) $response['exceptionCode']);
            }

            throw new PsCheckoutException(isset($response['body']['error']) ? $response['body']['error'] : 'Unknown error', PsCheckoutException::UNKNOWN);
        }

        $orderPayPal = $response['body'];
        $capturePayPal = $orderPayPal['purchase_units'][0]['payments']['captures'][0];

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
