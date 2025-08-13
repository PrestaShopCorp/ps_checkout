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

namespace PsCheckout\Core\PayPal\Order\Action;

use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalCaptureStatus;
use PsCheckout\Core\PayPal\Order\Handler\EventHandlerInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Response\PayPalProcessorResponse;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class CapturePayPalOrderAction implements CapturePayPalOrderActionInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var OrderHttpClientInterface
     */
    private $orderHttpClient;

    /**
     * @var PayPalOrderCacheInterface
     */
    private $payPalOrderCache;

    /**
     * @var EventHandlerInterface
     */
    private $orderCompletedEventHandler;

    /**
     * @var EventHandlerInterface
     */
    private $paymentPendingEventHandler;

    /**
     * @var EventHandlerInterface
     */
    private $paymentCompletedEventHandler;

    /**
     * @var EventHandlerInterface
     */
    private $paymentDeniedEventHandler;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    public function __construct(
        ConfigurationInterface $configuration,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        OrderHttpClientInterface $orderHttpClient,
        PayPalOrderCacheInterface $payPalOrderCache,
        EventHandlerInterface $orderCompletedEventHandler,
        EventHandlerInterface $paymentPendingEventHandler,
        EventHandlerInterface $paymentCompletedEventHandler,
        EventHandlerInterface $paymentDeniedEventHandler,
        PayPalOrderProviderInterface $payPalOrderProvider
    ) {
        $this->configuration = $configuration;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderHttpClient = $orderHttpClient;
        $this->payPalOrderCache = $payPalOrderCache;
        $this->orderCompletedEventHandler = $orderCompletedEventHandler;
        $this->paymentPendingEventHandler = $paymentPendingEventHandler;
        $this->paymentCompletedEventHandler = $paymentCompletedEventHandler;
        $this->paymentDeniedEventHandler = $paymentDeniedEventHandler;
        $this->payPalOrderProvider = $payPalOrderProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PayPalOrderResponse $payPalOrder): PayPalOrderResponse
    {
        $payload = [
            'mode' => $payPalOrder->getFundingSource() ?:
                $this->payPalOrderRepository->getOneBy(['id' => $payPalOrder->getId()])->getFundingSource(),
            'orderId' => $payPalOrder->getId(),
            'payee' => ['merchant_id' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)],
        ];

        $order = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrder->getId()]);

        if (in_array(PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_USES_VAULTING, $order->getCustomerIntent())) {
            $payload['vault'] = true;
        }

        $response = $this->orderHttpClient->captureOrder($payload);

        $orderPayPal = json_decode($response->getBody(), true);
        $cachedOrder = $this->payPalOrderCache->getValue($orderPayPal['id']);

        $this->payPalOrderCache->set($orderPayPal['id'], array_replace_recursive($cachedOrder, $orderPayPal));

        $payPalOrderResponse = $this->payPalOrderProvider->getById($orderPayPal['id']);

        if (!$payPalOrderResponse) {
            throw new PsCheckoutException('Capture declined', PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED);
        }

        if ($payPalOrderResponse->getStatus() === PayPalCaptureStatus::COMPLETED) {
            $this->orderCompletedEventHandler->handle($payPalOrderResponse);
        }

        if ($payPalOrderResponse->getCapture()['status'] === PayPalCaptureStatus::PENDING) {
            $this->paymentPendingEventHandler->handle($payPalOrderResponse);
        }

        if ($payPalOrderResponse->getCapture()['status'] === PayPalCaptureStatus::COMPLETED) {
            $this->paymentCompletedEventHandler->handle($payPalOrderResponse);
        }

        if ($payPalOrderResponse->getCapture()['status'] === PayPalCaptureStatus::DECLINED || $payPalOrderResponse->getCapture()['status'] === PayPalCaptureStatus::FAILED) {
            $this->paymentDeniedEventHandler->handle($payPalOrderResponse);
        }

        if (
            PayPalCaptureStatus::DECLINED === $payPalOrderResponse->getCapture()['status']
            && $payPalOrderResponse->getPaymentSource()
            && $payPalOrderResponse->getCard()
            && $payPalOrderResponse->getCapture()['processor_response']
        ) {
            $payPalProcessorResponse = new PayPalProcessorResponse(
                $payPalOrderResponse->getCard()['brand'] ?: null,
                $payPalOrderResponse->getCard()['brand']['type'] ?: null,
                $payPalOrderResponse->getCapture()['processor_response']['avs_code'] ?? null,
                $payPalOrderResponse->getCapture()['processor_response']['cvv_code'] ?? null,
                $payPalOrderResponse->getCapture()['processor_response']['payment_advice_code'] ?? null,
                $payPalOrderResponse->getCapture()['processor_response']['response_code'] ?? null
            );
            $message = 'The card processor declined the transaction';
            if ($payPalProcessorResponse->getResponseCode()) {
                $message .= ', ' . $payPalProcessorResponse->getResponseCodeDescription();
            }

            throw new PsCheckoutException($message, PsCheckoutException::PAYPAL_PAYMENT_CARD_ERROR);
        } elseif (PayPalCaptureStatus::DECLINED === $payPalOrderResponse->getCapture()['status'] || PayPalCaptureStatus::FAILED === $payPalOrderResponse->getCapture()['status']) {
            throw new PsCheckoutException('PayPal declined the capture', PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED);
        }

        return $payPalOrderResponse;
    }
}
