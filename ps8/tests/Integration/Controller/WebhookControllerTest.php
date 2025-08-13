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

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Webhook\Service\WebhookSecretToken;

class WebhookControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->client = new Client([
            'base_uri' => __PS_BASE_URI__,
            'http_errors' => false,
        ]);
    }

    public function tearDown(): void
    {
        parent::tearDown();
    }

    private function sendWebhookRequest(array $payload, string $secret, int $expectedStatusCode, array $expectedResponse)
    {
        \Configuration::updateValue(WebhookSecretToken::PS_CHECKOUT_WEBHOOK_SECRET, 'very-secret-key');

        $headers = [
            'Content-Type' => 'application/json',
            'webhook-secret' => $secret,
        ];

        $response = $this->client->post(Context::getContext()->link->getModuleLink('ps_checkout', 'webhook'), [
            'headers' => $headers,
            'body' => json_encode($payload),
        ]);

        $this->assertEquals($expectedStatusCode, $response->getStatusCode());
        $responseBody = json_decode((string)$response->getBody(), true);
        $this->assertEquals($expectedResponse, $responseBody);
    }

    public function testValidWebhook()
    {
        $payload = [
            'id' => '123456',
            'createTime' => '2025-01-17T12:00:00Z',
            'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
            'eventVersion' => '1.0',
            'summary' => 'Hello',
            'resourceType' => 'sale',
            'resource' => [
                'id' => 'PAY-12345',
                'configuration' => ['configuration' => ['name' => 'PS_CHECKOUT_test', 'value' => 1]],
                'state' => 'completed',
            ],
        ];

        $this->sendWebhookRequest($payload, 'very-secret-key', 200, ['httpCode' => 200]);

        $configValue = (int) \Configuration::get('PS_CHECKOUT_test');
        $this->assertEquals(1, $configValue);

        //NOTE: unset values
        \Configuration::updateValue(WebhookSecretToken::PS_CHECKOUT_WEBHOOK_SECRET, '');
    }

    public function testWebhookSecretMismatch()
    {
        $payload = [
            'id' => '123461',
            'createTime' => '2025-01-17T14:30:00Z',
            'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
            'eventVersion' => '1.0',
            'summary' => 'Hello',
            'resourceType' => 'sale',
            'resource' => [
                'id' => 'PAY-12350',
                'configuration' => ['configuration' => ['name' => 'PS_CHECKOUT_test', 'value' => 0]],
                'state' => 'failed',
            ],
        ];

        $this->sendWebhookRequest($payload, 'wrong-secret-key', 401, ['httpCode' => 401, 'error' => 'Webhook secret mismatch']);

        \Configuration::updateValue(WebhookSecretToken::PS_CHECKOUT_WEBHOOK_SECRET, '');
    }

    public function testMissingPayloadFields()
    {
        $fieldsToTest = ['id', 'createTime', 'eventType', 'eventVersion', 'summary', 'resourceType', 'resource'];

        foreach ($fieldsToTest as $field) {
            $payload = [
                'id' => '123456',
                'createTime' => '2025-01-17T12:00:00Z',
                'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
                'eventVersion' => '1.0',
                'summary' => 'Hello',
                'resourceType' => 'sale',
                'resource' => [
                    'id' => 'PAY-12345',
                    'configuration' => ['configuration' => ['name' => 'PS_CHECKOUT_test', 'value' => 1]],
                    'state' => 'completed',
                ],
            ];

            unset($payload[$field]);

            $expectedError = ['httpCode' => 400, 'error' => sprintf('Webhook %s is missing', $field)];

            $this->sendWebhookRequest($payload, 'very-secret-key', 400, $expectedError);
        }

        \Configuration::updateValue(WebhookSecretToken::PS_CHECKOUT_WEBHOOK_SECRET, '');
    }

    public function testEmptyPayload()
    {
        $payload = [];

        $this->sendWebhookRequest($payload, 'very-secret-key', 400, ['httpCode' => 400, 'error' => 'Webhook id is missing']);

        \Configuration::updateValue(WebhookSecretToken::PS_CHECKOUT_WEBHOOK_SECRET, '');
    }
}
