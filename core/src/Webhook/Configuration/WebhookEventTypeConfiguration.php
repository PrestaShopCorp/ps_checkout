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

namespace PsCheckout\Core\Webhook\Configuration;

class WebhookEventTypeConfiguration
{
    // Webhook Event Type constants directly related to PayPal webhook event types
    public const VAULT_PAYMENT_TOKEN_CREATED_PAYPAL = 'VAULT.PAYMENT-TOKEN.CREATED';

    public const VAULT_PAYMENT_TOKEN_DELETED_PAYPAL = 'VAULT.PAYMENT-TOKEN.DELETED';

    public const VAULT_PAYMENT_TOKEN_DELETION_INITIATED_PAYPAL = 'VAULT.PAYMENT-TOKEN.DELETION-INITIATED';

    public const PAYMENT_CAPTURE_COMPLETED_PAYPAL = 'PAYMENT.CAPTURE.COMPLETED';

    public const PAYMENT_CAPTURE_PENDING_PAYPAL = 'PAYMENT.CAPTURE.PENDING';

    public const PAYMENT_CAPTURE_DENIED_PAYPAL = 'PAYMENT.CAPTURE.DENIED';

    public const PAYMENT_CAPTURE_REFUNDED_PAYPAL = 'PAYMENT.CAPTURE.REFUNDED';

    public const PAYMENT_CAPTURE_REVERSED_PAYPAL = 'PAYMENT.CAPTURE.REVERSED';

    public const PAYMENT_AUTHORIZATION_CREATED_PAYPAL = 'PAYMENT.AUTHORIZATION.CREATED';

    public const PAYMENT_AUTHORIZATION_VOIDED_PAYPAL = 'PAYMENT.AUTHORIZATION.VOIDED';

    public const CHECKOUT_ORDER_APPROVED_PAYPAL = 'CHECKOUT.ORDER.APPROVED';

    public const CHECKOUT_ORDER_COMPLETED_PAYPAL = 'CHECKOUT.ORDER.COMPLETED';

    public const CHECKOUT_PAYMENT_APPROVAL_REVERSED_PAYPAL = 'CHECKOUT.PAYMENT-APPROVAL.REVERSED';

    // Webhook Event types used by Maasland
    public const VAULT_PAYMENT_TOKEN_CREATED = 'VaultPaymentTokenCreated';

    public const VAULT_PAYMENT_TOKEN_DELETED = 'VaultPaymentTokenDeleted';

    public const VAULT_PAYMENT_TOKEN_DELETION_INITIATED = 'VaultPaymentTokenDeletionInitiated';

    public const PAYMENT_CAPTURE_COMPLETED = 'PaymentCaptureCompleted';

    public const PAYMENT_CAPTURE_PENDING = 'PaymentCapturePending';

    public const PAYMENT_CAPTURE_DENIED = 'PaymentCaptureDenied';

    public const PAYMENT_CAPTURE_REFUNDED = 'PaymentCaptureRefunded';

    public const PAYMENT_CAPTURE_REVERSED = 'PaymentCaptureReversed';

    public const PAYMENT_AUTHORIZATION_CREATED = 'PaymentAuthorizationCreated';

    public const PAYMENT_AUTHORIZATION_VOIDED = 'PaymentAuthorizationVoided';

    public const CHECKOUT_ORDER_APPROVED = 'CheckoutOrderApproved';

    public const CHECKOUT_ORDER_COMPLETED = 'CheckoutOrderCompleted';

    public const CHECKOUT_PAYMENT_APPROVAL_REVERSED = 'CheckoutPaymentApprovalReversed';

    /** @var array<string, string> */
    public const MAPPING = [
        self::VAULT_PAYMENT_TOKEN_CREATED_PAYPAL => self::VAULT_PAYMENT_TOKEN_CREATED,
        self::VAULT_PAYMENT_TOKEN_DELETED_PAYPAL => self::VAULT_PAYMENT_TOKEN_DELETED,
        self::VAULT_PAYMENT_TOKEN_DELETION_INITIATED_PAYPAL => self::VAULT_PAYMENT_TOKEN_DELETION_INITIATED,
        self::PAYMENT_CAPTURE_COMPLETED_PAYPAL => self::PAYMENT_CAPTURE_COMPLETED,
        self::PAYMENT_CAPTURE_PENDING_PAYPAL => self::PAYMENT_CAPTURE_PENDING,
        self::PAYMENT_CAPTURE_DENIED_PAYPAL => self::PAYMENT_CAPTURE_DENIED,
        self::PAYMENT_CAPTURE_REFUNDED_PAYPAL => self::PAYMENT_CAPTURE_REFUNDED,
        self::PAYMENT_CAPTURE_REVERSED_PAYPAL => self::PAYMENT_CAPTURE_REVERSED,
        self::PAYMENT_AUTHORIZATION_CREATED_PAYPAL => self::PAYMENT_AUTHORIZATION_CREATED,
        self::PAYMENT_AUTHORIZATION_VOIDED_PAYPAL => self::PAYMENT_AUTHORIZATION_VOIDED,
        self::CHECKOUT_ORDER_APPROVED_PAYPAL => self::CHECKOUT_ORDER_APPROVED,
        self::CHECKOUT_ORDER_COMPLETED_PAYPAL => self::CHECKOUT_ORDER_COMPLETED,
        self::CHECKOUT_PAYMENT_APPROVAL_REVERSED_PAYPAL => self::CHECKOUT_PAYMENT_APPROVAL_REVERSED,
    ];

    /**
     * Get the PayPal webhook -> Maasland format event type
     *
     * @param string $eventType The webhook event type
     *
     * @return string|null
     */
    public static function getMappedEventType(string $eventType): ?string
    {
        return self::MAPPING[$eventType] ?? null;
    }
}
