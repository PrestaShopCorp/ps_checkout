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
            $transformedHeaders = $this->transformHeaders($headers);

            return $transformedHeaders;
        } catch (\InvalidArgumentException $e) {
            // Wrap the exception in a domain-specific exception
            throw new WebhookException('Header validation failed: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Validate headers (additional validation logic).
     *
     * @param array $headers
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function validateHeaders(array $headers)
    {
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

    /**
     * Transform headers into a standardized format.
     *
     * @param array $headers
     *
     * @return array
     */
    private function transformHeaders(array $headers): array
    {
        return [
            'shopId' => $headers['Shop-Id'],
            'merchantId' => $headers['Merchant-Id'],
            'firebaseId' => $headers['Psx-Id'],
        ];
    }
}
