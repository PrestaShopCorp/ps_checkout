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

    /**
     * @dataProvider paymentSourceProvider
     */
    public function testMasksCustomerIdInCreateOrderRequestBody(string $source): void
    {
        $body = json_encode([
            'intent' => 'CAPTURE',
            'payment_source' => [
                $source => [
                    'vault_id' => self::VAULT_ID,
                    'attributes' => [
                        'customer' => [
                            'id' => self::CUSTOMER_ID,
                        ],
                        'verification' => [
                            'method' => 'SCA_WHEN_REQUIRED',
                        ],
                    ],
                ],
            ],
        ]);

        $logged = $this->valueAt($this->process(['method' => 'POST', 'body' => $body]), 'body');
        $this->assertIsString($logged);

        $this->assertStringNotContainsString(self::VAULT_ID, $logged);
        $this->assertStringNotContainsString(self::CUSTOMER_ID, $logged);

        $decoded = json_decode($logged, true);
        $this->assertIsArray($decoded);

        $this->assertSame('****1JaY', $this->valueAt($decoded, 'payment_source.' . $source . '.attributes.customer.id'));
        $this->assertSame('SCA_WHEN_REQUIRED', $this->valueAt($decoded, 'payment_source.' . $source . '.attributes.verification.method'));
    }

    /**
     * @return array<string, array{string}>
     */
    public function paymentSourceProvider(): array
    {
        return [
            'card' => ['card'],
            'paypal' => ['paypal'],
            'venmo' => ['venmo'],
            'apple_pay' => ['apple_pay'],
        ];
    }

    public function testMasksVaultIdsInOrderWebhookBody(): void
    {
        // Same shape as the DispatchWebhookProcessor log context: ['payload' => DispatchWebhookRequest::toArray()]
        $context = $this->process([
            'payload' => [
                'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
                'resource' => [
                    'id' => '5O190127TN364715T',
                    'payment_source' => [
                        'card' => [
                            'last_digits' => '1111',
                            'attributes' => [
                                'vault' => [
                                    'id' => self::VAULT_ID,
                                    'status' => 'VAULTED',
                                    'customer' => ['id' => self::CUSTOMER_ID],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame('****wbvp', $this->valueAt($context, 'payload.resource.payment_source.card.attributes.vault.id'));
        $this->assertSame('****1JaY', $this->valueAt($context, 'payload.resource.payment_source.card.attributes.vault.customer.id'));
        $this->assertSame('5O190127TN364715T', $this->valueAt($context, 'payload.resource.id'));
        $this->assertSame('1111', $this->valueAt($context, 'payload.resource.payment_source.card.last_digits'));
    }

    public function testMasksVaultIdsInDeeplyNestedContext(): void
    {
        $context = $this->process([
            'a' => ['b' => ['c' => ['payload' => ['resource' => ['payment_source' => ['card' => ['attributes' => [
                'vault' => ['id' => self::VAULT_ID],
            ]]]]]]]],
        ]);

        $this->assertSame('****wbvp', $this->valueAt($context, 'a.b.c.payload.resource.payment_source.card.attributes.vault.id'));
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

    public function testMasksVaultIdInVaultLinksOfCaptureResponseBody(): void
    {
        $tokenUrl = 'https://api.sandbox.paypal.com/v3/vault/payment-tokens/' . self::VAULT_ID;
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
                            'links' => [
                                ['href' => $tokenUrl, 'rel' => 'self', 'method' => 'GET'],
                                ['href' => $tokenUrl, 'rel' => 'delete', 'method' => 'DELETE'],
                            ],
                        ],
                    ],
                ],
            ],
            'links' => [
                ['href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/5O190127TN364715T', 'rel' => 'self', 'method' => 'GET'],
            ],
        ]);

        $logged = $this->valueAt($this->process(['status_code' => 201, 'body' => $body]), 'body');
        $this->assertIsString($logged);

        $this->assertStringNotContainsString(self::VAULT_ID, $logged);
        $this->assertStringNotContainsString(self::CUSTOMER_ID, $logged);

        $decoded = json_decode($logged, true);
        $this->assertIsArray($decoded);

        $this->assertSame('https://api.sandbox.paypal.com/v3/vault/payment-tokens/****wbvp', $this->valueAt($decoded, 'payment_source.card.attributes.vault.links.0.href'));
        $this->assertSame('https://api.sandbox.paypal.com/v3/vault/payment-tokens/****wbvp', $this->valueAt($decoded, 'payment_source.card.attributes.vault.links.1.href'));
        $this->assertSame('delete', $this->valueAt($decoded, 'payment_source.card.attributes.vault.links.1.rel'));
        $this->assertSame('https://api.sandbox.paypal.com/v2/checkout/orders/5O190127TN364715T', $this->valueAt($decoded, 'links.0.href'));
        $this->assertSame('5O190127TN364715T', $this->valueAt($decoded, 'id'));
    }

    /**
     * @dataProvider tokenUrlProvider
     */
    public function testMasksTokenIdInUrlsFoundInAnyStringValue(string $url, string $expected): void
    {
        $context = $this->process([
            'message' => 'PayPal responded with ' . $url . ' (self)',
            'url' => $url,
        ]);

        $this->assertSame($expected, $context['url']);
        $this->assertSame('PayPal responded with ' . $expected . ' (self)', $context['message']);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function tokenUrlProvider(): array
    {
        return [
            'paypal payment token' => [
                'https://api.sandbox.paypal.com/v3/vault/payment-tokens/' . self::VAULT_ID,
                'https://api.sandbox.paypal.com/v3/vault/payment-tokens/****wbvp',
            ],
            'paypal setup token' => [
                'https://api-m.paypal.com/v3/vault/setup-tokens/' . self::VAULT_ID . '?fields=all',
                'https://api-m.paypal.com/v3/vault/setup-tokens/****wbvp?fields=all',
            ],
            'module payment token route' => [
                'https://api.example.com/v1/vault-merchant/payment-token/MERCHANT123/' . self::VAULT_ID,
                'https://api.example.com/v1/vault-merchant/payment-token/MERCHANT123/****wbvp',
            ],
        ];
    }

    public function testRedactsPayPalIdTokenInIdentityResponseBody(): void
    {
        $idToken = 'eyJraWQiOiJkMTA2ZTUwNzY5ZjNiOWY0.eyJpc3MiOiJodHRwczovL2FwaS5wYXlwYWwuY29tIn0.c2lnbmF0dXJl';
        $body = json_encode(['id_token' => $idToken]);

        $logged = $this->valueAt($this->process(['status_code' => 200, 'body' => $body]), 'body');
        $this->assertIsString($logged);

        $this->assertStringNotContainsString($idToken, $logged);
        $this->assertSame('{"id_token":"[REDACTED]"}', $logged);
    }

    public function testMasksTokenUrlInRecordMessage(): void
    {
        $record = $this->processRecord([
            'message' => 'VaultController exception 0 : cURL error 28: Operation timed out for https://api.example.com/v1/vault-merchant/payment-token/MERCHANT123/' . self::VAULT_ID,
            'context' => [],
        ]);

        $this->assertSame('VaultController exception 0 : cURL error 28: Operation timed out for https://api.example.com/v1/vault-merchant/payment-token/MERCHANT123/****wbvp', $record['message']);
    }

    public function testMasksTokenUrlInExceptionChainOfContext(): void
    {
        $guzzleMessage = 'cURL error 28: Operation timed out after 30001 milliseconds with 0 bytes received (see https://curl.se/libcurl/c/libcurl-errors.html) for https://api.example.com/v1/vault-merchant/payment-token/MERCHANT123/' . self::VAULT_ID;
        $exception = new \Exception('Failed to delete payment token', 0, new \RuntimeException($guzzleMessage, 28));

        $context = $this->process(['controller' => 'VaultModuleFrontController', 'exception' => $exception]);

        $this->assertStringNotContainsString(self::VAULT_ID, (string) json_encode($context));
        $this->assertSame('Exception', $this->valueAt($context, 'exception.class'));
        $this->assertSame('Failed to delete payment token', $this->valueAt($context, 'exception.message'));
        $this->assertSame('RuntimeException', $this->valueAt($context, 'exception.previous.class'));
        $this->assertSame(28, $this->valueAt($context, 'exception.previous.code'));
        $previousMessage = $this->valueAt($context, 'exception.previous.message');
        $this->assertIsString($previousMessage);
        $this->assertStringEndsWith('/payment-token/MERCHANT123/****wbvp', $previousMessage);
        $previousFile = $this->valueAt($context, 'exception.previous.file');
        $this->assertIsString($previousFile);
        $this->assertStringStartsWith(__FILE__ . ':', $previousFile);
        $this->assertIsArray($this->valueAt($context, 'exception.previous.trace'));
        $this->assertSame('VaultModuleFrontController', $context['controller']);
    }

    public function testMasksVaultWebhookResourceAndResourceId(): void
    {
        $context = $this->process([
            'webhookId' => 'WH-1',
            'eventType' => 'VAULT.PAYMENT-TOKEN.CREATED',
            'orderId' => null,
            'resourceId' => self::VAULT_ID,
            'payload' => [
                'eventType' => 'VAULT.PAYMENT-TOKEN.CREATED',
                'resource' => [
                    'id' => self::VAULT_ID,
                    'customer' => ['id' => self::CUSTOMER_ID],
                    'payment_source' => ['card' => ['last_digits' => '1111']],
                    'links' => [['href' => 'https://api.paypal.com/v3/vault/payment-tokens/' . self::VAULT_ID, 'rel' => 'self']],
                ],
            ],
        ]);

        $this->assertStringNotContainsString(self::VAULT_ID, (string) json_encode($context));
        $this->assertStringNotContainsString(self::CUSTOMER_ID, (string) json_encode($context));
        $this->assertSame('****wbvp', $context['resourceId']);
        $this->assertSame('****wbvp', $this->valueAt($context, 'payload.resource.id'));
        $this->assertSame('****1JaY', $this->valueAt($context, 'payload.resource.customer.id'));
        $this->assertSame('VAULT.PAYMENT-TOKEN.CREATED', $this->valueAt($context, 'payload.eventType'));
        $this->assertSame('1111', $this->valueAt($context, 'payload.resource.payment_source.card.last_digits'));
    }

    public function testKeepsResourceIdOfOtherWebhooks(): void
    {
        $context = $this->process([
            'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
            'resourceId' => '3C679366HH908993F',
            'payload' => ['eventType' => 'PAYMENT.CAPTURE.COMPLETED', 'resource' => ['id' => '3C679366HH908993F']],
        ]);

        $this->assertSame('3C679366HH908993F', $context['resourceId']);
        $this->assertSame('3C679366HH908993F', $this->valueAt($context, 'payload.resource.id'));
    }

    public function testMasksRejectedVaultIdInValidationErrorBody(): void
    {
        $body = json_encode([
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [
                ['field' => '/payment_source/card/vault_id', 'value' => self::VAULT_ID, 'issue' => 'VAULT_TOKEN_NOT_FOUND'],
                ['field' => '/purchase_units/@reference_id==\'default\'/amount/value', 'value' => '10.00', 'issue' => 'INVALID_PARAMETER'],
            ],
            'debug_id' => 'abc123',
        ]);

        $logged = $this->valueAt($this->process(['status_code' => 422, 'body' => $body]), 'body');
        $this->assertIsString($logged);
        $decoded = json_decode($logged, true);
        $this->assertIsArray($decoded);

        $this->assertStringNotContainsString(self::VAULT_ID, $logged);
        $this->assertSame('****wbvp', $this->valueAt($decoded, 'details.0.value'));
        $this->assertSame('VAULT_TOKEN_NOT_FOUND', $this->valueAt($decoded, 'details.0.issue'));
        $this->assertSame('10.00', $this->valueAt($decoded, 'details.1.value'));
        $this->assertSame('abc123', $decoded['debug_id']);
    }

    public function testMasksIdentifiersInTruncatedJsonOfGuzzleErrorMessage(): void
    {
        $error = 'Client error: `POST https://api.example.com/v2/checkout/orders` resulted in a `422 Unprocessable Entity` response:'
            . "\n" . '{"name":"UNPROCESSABLE_ENTITY","details":[{"field":"/payment_source/card/vault_id","value":"' . self::VAULT_ID . '","iss (truncated...)';
        $request = '{"intent":"CAPTURE","payment_source":{"card":{"vault_id":"' . self::VAULT_ID . '","stored_credential":{"payment_initiator":"MERCHANT"}}}, "identity": {"id_token":"eyJhbGciOiJSUzI1NiJ9.x.y"';

        $context = $this->process(['error' => $error, 'request' => $request]);
        $loggedError = $context['error'];
        $loggedRequest = $context['request'];
        $this->assertIsString($loggedError);
        $this->assertIsString($loggedRequest);

        $this->assertStringNotContainsString(self::VAULT_ID, (string) json_encode($context));
        $this->assertStringContainsString('"value":"****wbvp","iss (truncated...)', $loggedError);
        $this->assertStringContainsString('resulted in a `422 Unprocessable Entity` response', $loggedError);
        $this->assertStringContainsString('"vault_id":"****wbvp"', $loggedRequest);
        $this->assertStringContainsString('"id_token":"[REDACTED]"', $loggedRequest);
        $this->assertStringContainsString('"payment_initiator":"MERCHANT"', $loggedRequest);
    }

    public function testKeepsLastFourCharactersWhenTokenUrlIsFollowedByPunctuation(): void
    {
        $context = $this->process([
            'error' => 'Client error: `DELETE https://api.example.com/v1/vault-merchant/payment-token/M123/' . self::VAULT_ID . '` resulted in a `404 Not Found` response',
            'note' => '(https://api.paypal.com/v3/vault/payment-tokens/' . self::VAULT_ID . '), then \'https://api.paypal.com/v3/vault/payment-tokens/' . self::VAULT_ID . '\'.',
        ]);

        $this->assertSame('Client error: `DELETE https://api.example.com/v1/vault-merchant/payment-token/M123/****wbvp` resulted in a `404 Not Found` response', $context['error']);
        $this->assertSame('(https://api.paypal.com/v3/vault/payment-tokens/****wbvp), then \'https://api.paypal.com/v3/vault/payment-tokens/****wbvp\'.', $context['note']);
    }

    public function testKeepsNumbersOfScrubbedJsonBody(): void
    {
        $body = '{"vault_id":"' . self::VAULT_ID . '","big":12345678901234567890,"amount":1.0,"count":3}';

        $logged = $this->valueAt($this->process(['body' => $body]), 'body');

        $this->assertSame('{"vault_id":"****wbvp","big":"12345678901234567890","amount":1.0,"count":3}', $logged);
    }

    public function testScrubsArrayHeldByPaymentIdentifierKey(): void
    {
        $context = $this->process([
            'vault_id' => ['id' => self::VAULT_ID, 'token' => 'abc', 'vault' => ['id' => self::VAULT_ID]],
        ]);

        $this->assertStringNotContainsString(self::VAULT_ID, (string) json_encode($context));
        $this->assertSame('[REDACTED]', $this->valueAt($context, 'vault_id.token'));
        $this->assertSame('****wbvp', $this->valueAt($context, 'vault_id.vault.id'));
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
     * @param array<string, mixed> $record
     *
     * @return array<string, mixed>
     */
    private function processRecord(array $record): array
    {
        $processor = new SensitiveDataProcessor();
        $processed = $processor($record);
        $this->assertIsString($processed['message']);

        return ['message' => $processed['message'], 'context' => $processed['context']];
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
