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

namespace PsCheckout\Core\WebhookDispatcher\Validator;

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Core\WebhookDispatcher\Provider\WebhookHeaderProviderInterface;

class HeaderValuesValidator implements HeaderValuesValidatorInterface
{
    /**
     * @var WebhookHeaderProviderInterface
     */
    private $webhookHeaderProvider;

    public function __construct(WebhookHeaderProviderInterface $webhookHeaderProvider)
    {
        $this->webhookHeaderProvider = $webhookHeaderProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): array
    {
        try {
            // Step 1: Get headers from the service
            $headers = $this->webhookHeaderProvider->getHeaders();

            // Step 2: Validate headers (additional validation if needed)
            $this->validateHeaders($headers);

            // Step 3: Transform headers into a standardized format
            return $this->transformHeaders($headers);
        } catch (\InvalidArgumentException $e) {
            // Wrap the exception in a domain-specific exception
            throw new WebhookException('Header validation failed: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Validate headers (additional validation logic).
     *
     * @param array<string, string|null> $headers
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function validateHeaders(array $headers)
    {
        if (isset($headers['User-Agent']) && preg_match('/[Ss]vix/m', $headers['User-Agent'])) {
            if (empty($headers['Svix-Id'])) {
                throw new \InvalidArgumentException('Svix-Id can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_EMPTY);
            }

            if (empty($headers['Svix-Timestamp'])) {
                throw new \InvalidArgumentException('Svix-Timestamp can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_MERCHANT_ID_EMPTY);
            }

            if (empty($headers['Svix-Signature'])) {
                throw new \InvalidArgumentException('Svix-Signature can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_PSX_ID_EMPTY);
            }
        } else {
            if (empty($headers['Shop-Id'])) {
                throw new \InvalidArgumentException('Shop-Id can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_EMPTY);
            }

            if (empty($headers['Merchant-Id'])) {
                throw new \InvalidArgumentException('Merchant-Id can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_MERCHANT_ID_EMPTY);
            }

            if (empty($headers['Psx-Id'])) {
                throw new \InvalidArgumentException('Psx-Id can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_PSX_ID_EMPTY);
            }
        }
    }

    /**
     * Transform headers into a standardized format.
     *
     * @param array<string, string|null> $headers
     *
     * @return array{
     *     shopId: string|null,
     *     merchantId: string|null,
     *     firebaseId: string|null,
     *     Svix-Id: string|null,
     *     Svix-Timestamp: string|null,
     *     Svix-Signature: string|null,
     * }
     */
    private function transformHeaders(array $headers): array
    {
        return [
            'shopId' => $headers['Shop-Id'] ?? null,
            'merchantId' => $headers['Merchant-Id'] ?? null,
            'firebaseId' => $headers['Psx-Id'] ?? null,
            'Svix-Id' => $headers['Svix-Id'] ?? null,
            'Svix-Timestamp' => $headers['Svix-Timestamp'] ?? null,
            'Svix-Signature' => $headers['Svix-Signature'] ?? null,
        ];
    }
}
