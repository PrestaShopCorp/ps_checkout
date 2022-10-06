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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class WebhookHelper
{
    /**
     * Webhook payload
     *
     * @param string|null $content
     *
     * @return array{id: string, createTime: string, eventType: string, eventVersion: string, summary: string, resourceType: string, resource: array}
     *
     * @throws PsCheckoutException
     * @throws WebhookException
     */
    public function getPayload($content = null)
    {
        if (empty($content)) {
            $content = file_get_contents('php://input');
        }

        if (empty($content)) {
            throw new WebhookException('Body can\'t be empty', WebhookException::WEBHOOK_PAYLOAD_INVALID);
        }

        $payload = json_decode($content, true);
        $jsonError = json_last_error();

        if (null === $payload && JSON_ERROR_NONE !== $jsonError) {
            throw new PsCheckoutException('Json decode last error: ' . $jsonError, WebhookException::WEBHOOK_PAYLOAD_INVALID);
        }

        if (empty($payload['id'])) {
            throw new WebhookException('Webhook id is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['createTime'])) {
            throw new WebhookException('Webhook createTime is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['eventType'])) {
            throw new WebhookException('Webhook eventType is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['eventVersion'])) {
            throw new WebhookException('Webhook eventVersion is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['summary'])) {
            throw new WebhookException('Webhook summary is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['resourceType'])) {
            throw new WebhookException('Webhook resourceType is missing', WebhookException::WEBHOOK_PAYLOAD_EVENT_TYPE_MISSING);
        }

        if (empty($payload['resource'])) {
            throw new WebhookException('Webhook resource is missing', WebhookException::WEBHOOK_PAYLOAD_RESOURCE_MISSING);
        }

        return $payload;
    }
}
