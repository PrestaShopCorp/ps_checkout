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

namespace PsCheckout\Core\WebhookDispatcher\Processor;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PaymentToken\Action\SavePaymentTokenActionInterface;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Handler\PayPalEventDispatcherInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\Webhook\Configuration\WebhookCategoryConfiguration;
use PsCheckout\Core\Webhook\Configuration\WebhookEventTypeConfiguration;
use PsCheckout\Core\WebhookDispatcher\Repository\WebhookEventRepositoryInterface;
use PsCheckout\Core\WebhookDispatcher\ValueObject\DispatchWebhookRequest;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class DispatchWebhookProcessor implements DispatchWebhookProcessorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var PayPalOrderCacheInterface
     */
    private $payPalOrderCache;

    /**
     * @var PayPalEventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SavePaymentTokenActionInterface
     */
    private $savePaymentTokenAction;

    /**
     * @var PaymentTokenRepositoryInterface
     */
    private $paymentTokenRepository;

    /**
     * @var WebhookEventRepositoryInterface
     */
    private $webhookEventRepository;

    /**
     * @var PayPalOrderRepositoryInterface
     */
    private $payPalOrderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        LoggerInterface $logger,
        PayPalOrderProviderInterface $payPalOrderProvider,
        PayPalEventDispatcherInterface $eventDispatcher,
        PayPalOrderCacheInterface $payPalOrderCache,
        SavePaymentTokenActionInterface $savePaymentTokenAction,
        PaymentTokenRepositoryInterface $paymentTokenRepository,
        WebhookEventRepositoryInterface $webhookEventRepository,
        PayPalOrderRepositoryInterface $payPalOrderRepository,
        CartRepositoryInterface $cartRepository,
        ContextInterface $context
    ) {
        $this->logger = $logger;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->payPalOrderCache = $payPalOrderCache;
        $this->savePaymentTokenAction = $savePaymentTokenAction;
        $this->paymentTokenRepository = $paymentTokenRepository;
        $this->webhookEventRepository = $webhookEventRepository;
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->cartRepository = $cartRepository;
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function process(DispatchWebhookRequest $dispatchWebhookRequest): bool
    {
        $this->log('DispatchWebHook', $dispatchWebhookRequest);

        if (!in_array(
            $dispatchWebhookRequest->getCategory(),
            [WebhookCategoryConfiguration::SHOP_NOTIFCICATION_ORDER_CHANGE, WebhookCategoryConfiguration::SVIX]
        )) {
            $this->log('DispatchWebHook ignored', $dispatchWebhookRequest);

            return true;
        }

        $orderId = $dispatchWebhookRequest->getOrderId();

        if (!$orderId) {
            throw new PsCheckoutException('orderId must not be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY);
        }

        $resourceId = $dispatchWebhookRequest->getResourceId();

        if (!$resourceId) {
            throw new PsCheckoutException('resourceId must not be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_RESOURCE_EMPTY);
        }

        if ($this->payPalOrderCache->has($orderId)) {
            $this->payPalOrderCache->delete($orderId);
        }

        // Atomic idempotency claim — drops duplicate deliveries and guards against concurrent retries.
        // PayPal reuses the same webhookId on retries; claim() handles all status transitions correctly.
        $claimed = $this->webhookEventRepository->claim(
            $dispatchWebhookRequest->getWebhookId(),
            $dispatchWebhookRequest->getEventType(),
            $resourceId
        );

        if (!$claimed) {
            $this->logger->info(
                'Webhook event already processed or currently processing, skipping.',
                [
                    'webhookId' => $dispatchWebhookRequest->getWebhookId(),
                    'eventType' => $dispatchWebhookRequest->getEventType(),
                    'orderId' => $orderId,
                    'resourceId' => $resourceId,
                ]
            );

            return true;
        }

        // All post-claim logic is wrapped so that any failure — including unexpected exceptions
        // like TypeError — calls markFailed() and leaves the row in a state that PayPal can retry,
        // rather than leaving it stuck in "processing" until the stale threshold.
        try {
            // Bootstrap context from the local pscheckout_order record.
            // Webhook requests carry no customer session cookie, so context->cart is empty or stale.
            $payPalOrder = $this->payPalOrderRepository->getOneBy(['id' => $orderId]);
            if ($payPalOrder) {
                $cart = $this->cartRepository->getOneBy(['id_cart' => $payPalOrder->getIdCart()]);
                if ($cart && $cart->id) {
                    $this->context->loadCartForWebhook($cart);
                }
            }

            $payPalOrderResponse = $this->payPalOrderProvider->getById($orderId);

            if (!$payPalOrderResponse) {
                $this->logger->warning(
                    'PayPal order not found',
                    [
                        'orderId' => $orderId,
                    ]
                );

                throw new PsCheckoutException('PayPal order not found', PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY);
            }

            if ($this->handlePaymentTokenEvents($dispatchWebhookRequest->getEventType(), $payPalOrderResponse)) {
                $this->webhookEventRepository->markSucceeded($dispatchWebhookRequest->getWebhookId());

                return true;
            }

            $this->eventDispatcher->dispatch($dispatchWebhookRequest->getEventType(), $payPalOrderResponse);
            $this->webhookEventRepository->markSucceeded($dispatchWebhookRequest->getWebhookId());
        } catch (PsCheckoutException $e) {
            if ($e->getCode() === PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS) {
                // The PS order was already created — either by the synchronous front-office path
                // (CapturePayPalOrderAction fires the same handler before PayPal sends the webhook)
                // or by a prior webhook delivery. Return 200 so PayPal does not retry.
                $this->webhookEventRepository->markSucceeded($dispatchWebhookRequest->getWebhookId());
                $this->logger->info(
                    'Order already exists for webhook, marking event succeeded.',
                    [
                        'webhookId' => $dispatchWebhookRequest->getWebhookId(),
                        'orderId' => $orderId,
                    ]
                );

                return true;
            }

            $this->webhookEventRepository->markFailed($dispatchWebhookRequest->getWebhookId(), $e->getMessage());
            $this->logger->error(
                'Error processing webhook',
                [
                    'payload' => $dispatchWebhookRequest->toArray(),
                    'exception' => $e->getMessage(),
                ]
            );

            throw $e;
        } catch (\Throwable $e) {
            $this->webhookEventRepository->markFailed($dispatchWebhookRequest->getWebhookId(), $e->getMessage());
            $this->logger->error(
                'Unexpected error processing webhook',
                [
                    'payload' => $dispatchWebhookRequest->toArray(),
                    'exception' => $e->getMessage(),
                ]
            );

            throw $e;
        }

        return true;
    }

    /**
     * Handles payment token related events
     *
     * @param string $eventType
     * @param PayPalOrderResponse $payPalOrderResponse
     *
     * @return bool
     */
    private function handlePaymentTokenEvents(string $eventType, PayPalOrderResponse $payPalOrderResponse): bool
    {
        switch ($eventType) {
            case WebhookEventTypeConfiguration::VAULT_PAYMENT_TOKEN_CREATED:
                $this->savePaymentTokenAction->execute($payPalOrderResponse);

                return true;

            case WebhookEventTypeConfiguration::VAULT_PAYMENT_TOKEN_DELETED:
                $this->paymentTokenRepository->delete($payPalOrderResponse->getVault()['id']);

                return true;

            case WebhookEventTypeConfiguration::VAULT_PAYMENT_TOKEN_DELETION_INITIATED:
                // NOTE: do nothing but call is valid
                return true;

            default:
                return false;
        }
    }

    private function log(string $message, DispatchWebhookRequest $dispatchWebhookRequest)
    {
        $this->logger->info(
            $message,
            [
                'merchantId' => $dispatchWebhookRequest->getMerchantId(),
                'shopId' => $dispatchWebhookRequest->getShopId(),
                'firebaseId' => $dispatchWebhookRequest->getFirebaseId(),
                'payload' => $dispatchWebhookRequest->toArray(),
            ]
        );
    }
}
