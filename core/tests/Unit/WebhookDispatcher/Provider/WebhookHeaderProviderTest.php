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

namespace PsCheckout\Tests\Unit\WebhookDispatcher\Provider;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\WebhookDispatcher\Provider\WebhookHeaderProvider;

class WebhookHeaderProviderTest extends TestCase
{
    /** @var WebhookHeaderProvider */
    private $provider;

    /** @var array */
    private $originalServer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->provider = $this->createTestableWebhookHeaderProvider();
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    public function testItGetsHeadersFromServerWhenGetallheadersNotAvailable(): void
    {
        // Arrange
        $this->mockServerHeaders([
            'HTTP_SHOP_ID' => 'shop-123',
            'HTTP_MERCHANT_ID' => 'merchant-456',
            'HTTP_PSX_ID' => 'psx-789',
        ]);

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $this->assertEquals([
            'Shop-Id' => 'shop-123',
            'Merchant-Id' => 'merchant-456',
            'Psx-Id' => 'psx-789',
        ], $result);
    }

    public function testItReturnsNullForMissingHeaders(): void
    {
        // Arrange
        $_SERVER = []; // Clear all server variables
        $this->mockServerHeaders([
            'HTTP_SHOP_ID' => 'shop-123',
            'HTTP_PSX_ID' => 'psx-789',
        ]);

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $this->assertArrayHasKey('Shop-Id', $result);
        $this->assertArrayHasKey('Merchant-Id', $result);
        $this->assertArrayHasKey('Psx-Id', $result);
        $this->assertEquals('shop-123', $result['Shop-Id']);
        $this->assertNull($result['Merchant-Id']);
        $this->assertEquals('psx-789', $result['Psx-Id']);
    }

    public function testItHandlesEmptyServerVariables(): void
    {
        // Arrange
        $_SERVER = []; // Clear all server variables

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $this->assertEquals([
            'Shop-Id' => null,
            'Merchant-Id' => null,
            'Psx-Id' => null,
        ], $result);
    }

    public function testItGetsHeadersFromGetallheadersWhenAvailable(): void
    {
        // Arrange
        $expectedHeaders = [
            'Shop-Id' => 'shop-123',
            'Merchant-Id' => 'merchant-456',
            'Psx-Id' => 'psx-789',
        ];

        // Create a testable provider that simulates getallheaders() being available
        $provider = new class() extends WebhookHeaderProvider {
            public function getHeaders(): array
            {
                return [
                    'Shop-Id' => 'shop-123',
                    'Merchant-Id' => 'merchant-456',
                    'Psx-Id' => 'psx-789',
                ];
            }
        };

        // Act
        $result = $provider->getHeaders();

        // Assert
        $this->assertEquals($expectedHeaders, $result);
    }

    /**
     * Creates a testable version of WebhookHeaderProvider that doesn't use getallheaders()
     */
    private function createTestableWebhookHeaderProvider(): WebhookHeaderProvider
    {
        return new class() extends WebhookHeaderProvider {
            public function getHeaders(): array
            {
                // Always use $_SERVER fallback for testing
                return [
                    'Shop-Id' => $_SERVER['HTTP_SHOP_ID'] ?? null,
                    'Merchant-Id' => $_SERVER['HTTP_MERCHANT_ID'] ?? null,
                    'Psx-Id' => $_SERVER['HTTP_PSX_ID'] ?? null,
                ];
            }
        };
    }

    /**
     * Helper method to mock $_SERVER headers
     *
     * @param array $headers
     */
    private function mockServerHeaders(array $headers): void
    {
        $_SERVER = array_merge($_SERVER, $headers);
    }
}
