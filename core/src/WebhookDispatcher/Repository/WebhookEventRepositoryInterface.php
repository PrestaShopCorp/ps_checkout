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

namespace PsCheckout\Core\WebhookDispatcher\Repository;

interface WebhookEventRepositoryInterface
{
    /**
     * Atomically claims a webhook event for processing.
     *
     * PayPal reuses the same webhookId on retries, so claim() must distinguish:
     *   - New event             → INSERT succeeds                         → return true
     *   - Concurrent duplicate → row exists, status=processing (recent)  → return false
     *   - Retry after crash    → row exists, status=processing (stale)   → reclaim, return true
     *   - Retry after failure  → row exists, status=failed               → reclaim, return true
     *   - Retry after success  → row exists, status=succeeded            → return false
     *
     * @param string $webhookId  PayPal webhook event ID (idempotency key)
     * @param string $eventType  e.g. PAYMENT.CAPTURE.COMPLETED
     * @param string $resourceId resource.id from the webhook payload (capture, refund, authorization, or order ID) — must not be empty
     *
     * @return bool True if the event was claimed and processing should proceed
     */
    public function claim(string $webhookId, string $eventType, string $resourceId): bool;

    /**
     * Marks a previously claimed webhook event as successfully processed.
     *
     * @param string $webhookId
     *
     * @return void
     */
    public function markSucceeded(string $webhookId): void;

    /**
     * Marks a previously claimed webhook event as failed.
     *
     * @param string $webhookId
     * @param string $error     Error message for observability
     *
     * @return void
     */
    public function markFailed(string $webhookId, string $error): void;
}
