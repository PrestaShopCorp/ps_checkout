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
use Exception;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeniedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPalError;
use PrestaShop\Module\PrestashopCheckout\PayPalProcessorResponse;

class CapturePayPalOrderCommandHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(CapturePayPalOrderCommand $capturePayPalOrderCommand)
    {
        try {
            $context = Context::getContext();
            $merchantId = Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT', null, null, $context->shop->id);
            $apiOrder = new Order($context->link);
            $response = $apiOrder->capture(
                $capturePayPalOrderCommand->getOrderId()->getValue(),
                $merchantId,
                $capturePayPalOrderCommand->getFundingSource()
            );

            if (false === $response['status']) {
                if (false === empty($response['body']['message'])) {
                    (new PayPalError($response['body']['message']))->throwException();
                }

                if (false === empty($response['exceptionMessage']) && false === empty($response['exceptionCode'])) {
                    throw new PsCheckoutException($response['exceptionMessage'], (int) $response['exceptionCode']);
                }

                throw new PsCheckoutException(isset($response['body']['error']) ? $response['body']['error'] : 'Unknown error', PsCheckoutException::UNKNOWN);
            }

            $capturePayload = $response['body']['purchase_units'][0]['payments']['captures'][0];

            /** @var \Ps_checkout $module */
            $module = \Module::getInstanceByName('ps_checkout');
            $module->getLogger()->debug(__CLASS__, [$capturePayload]);

            if (false === empty($capturePayload)) {
                $captureId = $capturePayload['id'];
                $captureStatus = $capturePayload['status'];

                if (
                    'DECLINED' === $captureStatus
                    && false === empty($response['body']['payment_source'])
                    && false === empty($response['body']['payment_source'][0]['card'])
                    && false === empty($capturePayload['processor_response'])
                ) {
                    $payPalProcessorResponse = new PayPalProcessorResponse(
                        isset($response['body']['payment_source'][0]['card']['brand']) ? $response['body']['payment_source'][0]['card']['brand'] : null,
                        isset($response['body']['payment_source'][0]['card']['type']) ? $response['body']['payment_source'][0]['card']['type'] : null,
                        isset($capturePayload['processor_response']['avs_code']) ? $$capturePayload['processor_response']['avs_code'] : null,
                        isset($capturePayload['processor_response']['cvv_code']) ? $$capturePayload['processor_response']['cvv_code'] : null,
                        isset($capturePayload['processor_response']['response_code']) ? $$capturePayload['processor_response']['response_code'] : null
                    );
                    $payPalProcessorResponse->throwException();
                }
            }

            // Event to emit (depending to order capture status)
            switch ($captureStatus) {
                case 'COMPLETED':
                    $this->eventDispatcher->dispatch(
                        new PayPalCaptureCompletedEvent(
                            $captureId,
                            $capturePayPalOrderCommand->getOrderId()->getValue(),
                            $capturePayload
                        )
                    );
                    break;
                case 'PENDING':
                    $this->eventDispatcher->dispatch(
                        new PayPalCapturePendingEvent(
                            $captureId,
                            $capturePayPalOrderCommand->getOrderId()->getValue(),
                            $capturePayload
                        )
                    );
                    break;
                case 'DECLINED':
                    $this->eventDispatcher->dispatch(
                        new PayPalCaptureDeniedEvent(
                            $captureId,
                            $capturePayPalOrderCommand->getOrderId()->getValue(),
                            $capturePayload
                        )
                    );
                    break;
            }
        } catch (Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to capture PayPal Order #%d', $capturePayPalOrderCommand->getOrderId()->getValue()), PayPalOrderException::CANNOT_CAPTURE_ORDER, $exception);
        }
    }
}
