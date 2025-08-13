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
use PsCheckout\Core\WebhookDispatcher\ValueObject\DispatchWebhookRequest;
use Psr\Log\LoggerInterface;

class DispatchWebhookProcessor implements DispatchWebhookProcessorInterface
{
    const PS_CHECKOUT_VAULT_PAYMENT_TOKEN_CREATED = 'VaultPaymentTokenCreated';

    const PS_CHECKOUT_VAULT_PAYMENT_TOKEN_DELETED = 'VaultPaymentTokenDeleted';

    const PS_CHECKOUT_VAULT_PAYMENT_TOKEN_DELETION_INITIATED = 'VaultPaymentTokenDeletionInitiated';

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

    public function __construct(
        LoggerInterface $logger,
        PayPalOrderProviderInterface $payPalOrderProvider,
        PayPalEventDispatcherInterface $eventDispatcher,
        PayPalOrderCacheInterface $payPalOrderCache,
        SavePaymentTokenActionInterface $savePaymentTokenAction,
        PaymentTokenRepositoryInterface $paymentTokenRepository
    ) {
        $this->logger = $logger;
        $this->payPalOrderProvider = $payPalOrderProvider;
        $this->eventDispatcher = $eventDispatcher;
        $this->payPalOrderCache = $payPalOrderCache;
        $this->savePaymentTokenAction = $savePaymentTokenAction;
        $this->paymentTokenRepository = $paymentTokenRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function process(DispatchWebhookRequest $dispatchWebhookRequest): bool
    {
        $this->log('DispatchWebHook', $dispatchWebhookRequest);

        if ('ShopNotificationOrderChange' !== $dispatchWebhookRequest->getCategory()) {
            $this->log('DispatchWebHook ignored', $dispatchWebhookRequest);

            return true;
        }

        if (!$dispatchWebhookRequest->getOrderId()) {
            throw new PsCheckoutException('orderId must not be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY);
        }

        if ($this->payPalOrderCache->has($dispatchWebhookRequest->getOrderId())) {
            $this->payPalOrderCache->delete($dispatchWebhookRequest->getOrderId());
        }

        $payPalOrderResponse = $this->payPalOrderProvider->getById($dispatchWebhookRequest->getOrderId());

        if (!$payPalOrderResponse) {
            $this->logger->warning(
                'PayPal order not found',
                [
                    $dispatchWebhookRequest->getOrderId() => 'orderId',
                ]
            );

            throw new PsCheckoutException('PayPal order not found', PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY);
        }

        if ($this->handlePaymentTokenEvents($dispatchWebhookRequest->getEventType(), $payPalOrderResponse)) {
            return true;
        }

        try {
            $this->eventDispatcher->dispatch($dispatchWebhookRequest->getEventType(), $payPalOrderResponse);
        } catch (PsCheckoutException $e) {
            $this->logger->error(
                'Error processing webhook',
                [
                    'payload' => $dispatchWebhookRequest->toArray(),
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
            case self::PS_CHECKOUT_VAULT_PAYMENT_TOKEN_CREATED:
                $this->savePaymentTokenAction->execute($payPalOrderResponse);

                return true;

            case self::PS_CHECKOUT_VAULT_PAYMENT_TOKEN_DELETED:
                $this->paymentTokenRepository->delete($payPalOrderResponse->getVault()['id']);

                return true;

            case self::PS_CHECKOUT_VAULT_PAYMENT_TOKEN_DELETION_INITIATED:
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
