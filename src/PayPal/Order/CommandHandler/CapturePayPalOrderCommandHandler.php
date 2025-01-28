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

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Customer\Exception\CustomerException;
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
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\PayPalProcessorResponse;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use Psr\Log\LoggerInterface;
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

    /** @var PayPalConfiguration  */
    private $payPalConfiguration;

    /** @var PayPalOrderRepository */
    private $payPalOrderRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        MaaslandHttpClient       $maaslandHttpClient,
        EventDispatcherInterface $eventDispatcher,
        CacheInterface           $orderPayPalCache,
        PrestaShopContext        $prestaShopContext,
        PayPalCustomerRepository $payPalCustomerRepository,
        LoggerInterface          $logger,
        PayPalConfiguration      $payPalConfiguration,
        PayPalOrderRepository    $payPalOrderRepository
    ) {
        $this->maaslandHttpClient = $maaslandHttpClient;
        $this->eventDispatcher = $eventDispatcher;
        $this->orderPayPalCache = $orderPayPalCache;
        $this->prestaShopContext = $prestaShopContext;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->payPalConfiguration = $payPalConfiguration;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->logger = $logger;
    }

    public function handle(CapturePayPalOrderCommand $capturePayPalOrderCommand)
    {
        $merchantId = $this->payPalConfiguration->getMerchantId();

        $capturePayload = $this->buildCapturePayload($capturePayPalOrderCommand);

        $orderPayPal = $this->captureOrder($capturePayload);

        if (empty($orderPayPal)) {
            throw new \Exception('Order PayPal not found');
        }

        if (isset($orderPayPal['payment_source'][$capturePayPalOrderCommand->getFundingSource()]['attributes']['vault'])) {
            $vault = $orderPayPal['payment_source'][$capturePayPalOrderCommand->getFundingSource()]['attributes']['vault'];
            $vault = $this->savePrestaShopPayPalCustomerRelationship($vault);

            if (isset($vault['id'])) {
                $paymentToken = $this->createPaymentTokenEvent($capturePayPalOrderCommand, $orderPayPal, $vault, $merchantId);
            }
        }

        return $this->processTheCapture($orderPayPal);
    }

    private function captureOrder($captureOrderPayload)
    {
        try {
            $response = $this->maaslandHttpClient->captureOrder($captureOrderPayload);

            $orderPayPal = json_decode($response->getBody(), true);
            $payPalOrderFromCache = $this->orderPayPalCache->get($orderPayPal['id']);

            return array_replace_recursive($payPalOrderFromCache, $orderPayPal);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            return [];
        }
    }

    private function buildCapturePayload(CapturePayPalOrderCommand $capturePayPalOrderCommand)
    {
        $payload = [
            'mode' => $capturePayPalOrderCommand->getFundingSource(),
            'orderId' => $capturePayPalOrderCommand->getOrderId()->getValue(),
            'payee' => ['merchant_id' => $this->payPalConfiguration->getMerchantId()],
        ];

        $order = $this->payPalOrderRepository->getPayPalOrderById($capturePayPalOrderCommand->getOrderId());

        if ($order->checkCustomerIntent(PayPalOrder::CUSTOMER_INTENT_USES_VAULTING)) {
            $payload['vault'] = true;
        }

        return $payload;
    }

    private function savePrestaShopPayPalCustomerRelationship($vault)
    {
        try {
            $payPalCustomerId = new PayPalCustomerId($vault['customer']['id']);
            $customerId = new CustomerId($this->prestaShopContext->getCustomerId());
            $this->payPalCustomerRepository->save($customerId, $payPalCustomerId);

            return $vault;
        } catch (CustomerException $exception) {
            $this->logger->error($exception->getMessage());
            return [];
        } catch (\InvalidArgumentException $exception) {
            return [];
        } catch (\Exception $exception) {
            $this->logger->error(sprintf('An error occurs during process : %s', $exception->getMessage()));
            return [];
        }
    }

    private function createPaymentTokenEvent($capturePayPalOrderCommand, $orderPayPal, $vault, $merchantId)
    {
        $paymentToken = $vault;
        $paymentToken['metadata'] = [
            'order_id' => $orderPayPal['id'],
        ];

        $paymentSource = $orderPayPal['payment_source'];
        unset($paymentSource[$capturePayPalOrderCommand->getFundingSource()]['attributes']['vault']);
        $paymentSource[$capturePayPalOrderCommand->getFundingSource()]['verification_status'] = $paymentToken['status'];

        $paymentToken['payment_source'] = $paymentSource;

        $this->eventDispatcher->dispatch(new PaymentTokenCreatedEvent(
            $paymentToken,
            $merchantId
        ));

        return $paymentToken;
    }

    private function processTheCapture($orderPayPal)
    {
        $capturePayPal = $orderPayPal['purchase_units'][0]['payments']['captures'][0];

        if ($orderPayPal['status'] === PayPalOrderStatus::COMPLETED) {
            $this->logger->info('success !');
            $this->eventDispatcher->dispatch(new PayPalOrderCompletedEvent($orderPayPal['id'], $orderPayPal));
        }

        if ($capturePayPal['status'] === PayPalCaptureStatus::PENDING) {
            $this->eventDispatcher->dispatch(new PayPalCapturePendingEvent($capturePayPal['id'], $orderPayPal['id'], $capturePayPal));
        }

        if ($capturePayPal['status'] === PayPalCaptureStatus::COMPLETED) {
            $this->logger->info('Capture payment completed');
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

        return $capturePayPal;
    }
}
