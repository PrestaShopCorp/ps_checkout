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

namespace Tests\Unit\PsCheckout\Infrastructure\Logger\Processor;

use PHPUnit\Framework\TestCase;
use PsCheckout\Infrastructure\Logger\Processor\SensitiveDataProcessor;

class SensitiveDataProcessorTest extends TestCase
{
    const VAULT_ID = '8kk8451t5rbxwbvp';

    const CUSTOMER_ID = 'qLz7mL1JaY';

    public function testMasksVaultIdInJsonRequestBody(): void
    {
        $body = json_encode([
            'intent' => 'CAPTURE',
            'payment_source' => [
                'card' => [
                    'vault_id' => self::VAULT_ID,
                ],
            ],
        ]);

        $logged = $this->valueAt($this->process(['method' => 'POST', 'body' => $body]), 'body');
        $this->assertIsString($logged);

        $this->assertStringNotContainsString(self::VAULT_ID, $logged);
        $this->assertStringContainsString('"vault_id":"****wbvp"', $logged);
        $this->assertStringContainsString('"intent":"CAPTURE"', $logged);
    }

    public function testMasksVaultAndCustomerIdsInJsonResponseBody(): void
    {
        $body = json_encode([
            'id' => '5O190127TN364715T',
            'status' => 'COMPLETED',
            'payment_source' => [
                'card' => [
                    'last_digits' => '1111',
                    'attributes' => [
                        'vault' => [
                            'id' => self::VAULT_ID,
                            'status' => 'VAULTED',
                            'customer' => [
                                'id' => self::CUSTOMER_ID,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $logged = $this->valueAt($this->process(['status_code' => 201, 'body' => $body]), 'body');
        $this->assertIsString($logged);

        $this->assertStringNotContainsString(self::VAULT_ID, $logged);
        $this->assertStringNotContainsString(self::CUSTOMER_ID, $logged);

        $decoded = json_decode($logged, true);
        $this->assertIsArray($decoded);

        $this->assertSame('****wbvp', $this->valueAt($decoded, 'payment_source.card.attributes.vault.id'));
        $this->assertSame('****1JaY', $this->valueAt($decoded, 'payment_source.card.attributes.vault.customer.id'));
        $this->assertSame('VAULTED', $this->valueAt($decoded, 'payment_source.card.attributes.vault.status'));
        $this->assertSame('5O190127TN364715T', $this->valueAt($decoded, 'id'));
        $this->assertSame('COMPLETED', $this->valueAt($decoded, 'status'));
        $this->assertSame('1111', $this->valueAt($decoded, 'payment_source.card.last_digits'));
    }

    public function testMasksVaultIdInPaymentTokenUrl(): void
    {
        $context = $this->process([
            'method' => 'DELETE',
            'url' => 'https://api.example.com/v1/vault-merchant/payment-token/MERCHANT123/' . self::VAULT_ID,
        ]);

        $this->assertSame('https://api.example.com/v1/vault-merchant/payment-token/MERCHANT123/****wbvp', $context['url']);
    }

    public function testMasksVaultIdsInArrayContext(): void
    {
        $context = $this->process([
            'payload' => [
                'resource' => [
                    'id' => '5O190127TN364715T',
                    'payment_source' => [
                        'paypal' => [
                            'attributes' => [
                                'vault' => [
                                    'id' => self::VAULT_ID,
                                    'customer' => ['id' => self::CUSTOMER_ID],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('****wbvp', $this->valueAt($context, 'payload.resource.payment_source.paypal.attributes.vault.id'));
        $this->assertSame('****1JaY', $this->valueAt($context, 'payload.resource.payment_source.paypal.attributes.vault.customer.id'));
        $this->assertSame('5O190127TN364715T', $this->valueAt($context, 'payload.resource.id'));
    }

    public function testMasksTopLevelVaultContext(): void
    {
        $context = $this->process([
            'vault' => [
                'id' => self::VAULT_ID,
                'status' => 'VAULTED',
                'customer' => ['id' => self::CUSTOMER_ID],
                'metadata' => ['order_id' => '5O190127TN364715T'],
            ],
        ]);

        $this->assertSame('****wbvp', $this->valueAt($context, 'vault.id'));
        $this->assertSame('****1JaY', $this->valueAt($context, 'vault.customer.id'));
        $this->assertSame('5O190127TN364715T', $this->valueAt($context, 'vault.metadata.order_id'));
    }

    /**
     * @dataProvider identifierKeyProvider
     */
    public function testMasksPaymentIdentifierKeys(string $key): void
    {
        $context = $this->process([$key => self::VAULT_ID]);

        $this->assertSame('****wbvp', $context[$key]);
    }

    /**
     * @return array<string, array{string}>
     */
    public function identifierKeyProvider(): array
    {
        return [
            'vault_id' => ['vault_id'],
            'vaultId' => ['vaultId'],
            'customer_id' => ['customer_id'],
        ];
    }

    public function testFullyRedactsShortIdentifiers(): void
    {
        $context = $this->process(['vault_id' => 'abcd']);

        $this->assertSame('[REDACTED]', $context['vault_id']);
    }

    public function testRedactsExistingSensitiveKeysInJsonBody(): void
    {
        $context = $this->process(['body' => '{"token":"abc123","status":"OK"}']);

        $this->assertSame('{"token":"[REDACTED]","status":"OK"}', $context['body']);
    }

    /**
     * @dataProvider untouchedBodyProvider
     */
    public function testLeavesBodiesWithoutIdentifiersUntouched(string $body): void
    {
        $context = $this->process(['body' => $body]);

        $this->assertSame($body, $context['body']);
    }

    /**
     * @return array<string, array{string}>
     */
    public function untouchedBodyProvider(): array
    {
        return [
            'json without identifiers' => ["{\n  \"id\": \"5O190127TN364715T\",\n  \"links\": [{\"href\": \"https://example.com/a/b\"}]\n}"],
            'plain text' => ['Forbidden'],
            'invalid json' => ['{"status": "OK"'],
            'empty' => [''],
        ];
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<mixed>
     */
    private function process(array $context): array
    {
        $processor = new SensitiveDataProcessor();
        $record = $processor(['message' => 'test', 'context' => $context]);
        $this->assertIsArray($record['context']);

        return $record['context'];
    }

    /**
     * Reads a nested value by dot-separated path, e.g. "payload.resource.id".
     *
     * @param array<mixed> $data
     * @param string $path
     *
     * @return mixed
     */
    private function valueAt(array $data, string $path)
    {
        $value = $data;

        foreach (explode('.', $path) as $key) {
            $this->assertIsArray($value);
            $this->assertArrayHasKey($key, $value);
            $value = $value[$key];
        }

        return $value;
    }
}
