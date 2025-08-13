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

use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Core\WebhookDispatcher\Provider\WebhookBodyProviderInterface;

class BodyValuesValidator implements BodyValuesValidatorInterface
{
    /**
     * @var WebhookBodyProviderInterface
     */
    private $webhookBodyProvider;

    public function __construct(WebhookBodyProviderInterface $webhookBodyProvider)
    {
        $this->webhookBodyProvider = $webhookBodyProvider;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(): array
    {
        try {
            // Step 1: Get the body from the service
            $bodyValues = $this->webhookBodyProvider->getBody();

            // Step 2: Validate the body (additional validation if needed)
            $this->validateBody($bodyValues);

            // Step 3: Transform the body into a standardized format
            $transformedBody = $this->transformBody($bodyValues);

            return $transformedBody;
        } catch (\InvalidArgumentException $e) {
            // Wrap the exception in a domain-specific exception
            throw new WebhookException('Body validation failed: ' . $e->getMessage(), 400);
        }
    }

    /**
     * Validate the body (additional validation logic).
     *
     * @param array $bodyValues
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    private function validateBody(array $bodyValues)
    {
        $requiredFields = ['resource', 'eventType', 'category'];

        foreach ($requiredFields as $field) {
            if (empty($bodyValues[$field])) {
                throw new \InvalidArgumentException(sprintf('Missing required field: %s', $field));
            }
        }
    }

    /**
     * Transform the body into a standardized format.
     *
     * @param array $bodyValues
     *
     * @return array
     */
    private function transformBody(array $bodyValues): array
    {
        return [
            'resource' => json_decode($bodyValues['resource'], true),
            'eventType' => (string) $bodyValues['eventType'],
            'category' => (string) $bodyValues['category'],
            'summary' => $bodyValues['summary'] ?? null,
            'orderId' => $bodyValues['orderId'] ?? null,
        ];
    }
}
