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

namespace PrestaShop\Module\PrestashopCheckout\Webhook;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class WebhookEventConfigurationUpdatedHandler implements WebhookEventHandlerInterface
{
    /**
     * @var PrestaShopConfiguration
     */
    private $configuration;

    /**
     * @param PrestaShopConfiguration $configuration
     */
    public function __construct(PrestaShopConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param array{id: string, createTime: string, eventType: string, eventVersion: string, summary: string, resourceType: string, resource: array} $payload
     *
     * @return bool
     */
    public function supports(array $payload)
    {
        return !empty($payload['eventType']) && $payload['eventType'] === 'PRESTASHOP.CONFIGURATION.UPDATED';
    }

    /**
     * @param array{id: string, createTime: string, eventType: string, eventVersion: string, summary: string, resourceType: string, resource: array} $payload
     *
     * @return bool
     *
     * @throws WebhookException
     * @throws PsCheckoutException
     */
    public function handle(array $payload)
    {
        $this->assertPayloadIsValid($payload);

        foreach ($payload['resource']['configuration'] as $configuration) {
            $this->configuration->set($configuration['name'], $configuration['value']);
        }

        return true;
    }

    /**
     * @param array{id: string, createTime: string, eventType: string, eventVersion: string, summary: string, resourceType: string, resource: array} $payload
     *
     * @throws WebhookException
     */
    private function assertPayloadIsValid(array $payload)
    {
        if (empty($payload['resource']['configuration']) || !is_array($payload['resource']['configuration'])) {
            throw new WebhookException('Configuration list is empty', WebhookException::WEBHOOK_PAYLOAD_CONFIGURATION_LIST_MISSING);
        }

        foreach ($payload['resource']['configuration'] as $configuration) {
            if (empty($configuration['name']) || 0 !== strpos($configuration['name'], 'PS_CHECKOUT_')) {
                throw new WebhookException('Configuration name is invalid', WebhookException::WEBHOOK_PAYLOAD_CONFIGURATION_NAME_INVALID);
            }
        }
    }
}
