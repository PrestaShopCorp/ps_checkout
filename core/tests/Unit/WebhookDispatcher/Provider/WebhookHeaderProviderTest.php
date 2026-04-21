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
        $this->provider = new WebhookHeaderProvider();
        $this->originalServer = $_SERVER;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->originalServer;
        parent::tearDown();
    }

    public function testItGetsHeadersFromServer(): void
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
            'shopId' => 'shop-123',
            'merchantId' => 'merchant-456',
            'firebaseId' => 'psx-789',
            'Shop-Id' => 'shop-123',
            'Merchant-Id' => 'merchant-456',
            'Psx-Id' => 'psx-789',
            'Svix-Id' => null,
            'Svix-Timestamp' => null,
            'Svix-Signature' => null,
            'User-Agent' => null,
        ], $result);
    }

    public function testItGetsSvixHeaders(): void
    {
        // Arrange
        $_SERVER = [];
        $this->mockServerHeaders([
            'HTTP_SVIX_ID' => 'msg_abc123',
            'HTTP_SVIX_TIMESTAMP' => '1677812345',
            'HTTP_SVIX_SIGNATURE' => 'v1,base64signature==',
        ]);

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $this->assertEquals([
            'shopId' => null,
            'merchantId' => null,
            'firebaseId' => null,
            'Shop-Id' => null,
            'Merchant-Id' => null,
            'Psx-Id' => null,
            'Svix-Id' => 'msg_abc123',
            'Svix-Timestamp' => '1677812345',
            'Svix-Signature' => 'v1,base64signature==',
            'User-Agent' => null,
        ], $result);
    }

    public function testItGetsBothMaaslandAndSvixHeaders(): void
    {
        // Arrange
        $_SERVER = [];
        $this->mockServerHeaders([
            'HTTP_SHOP_ID' => 'shop-123',
            'HTTP_MERCHANT_ID' => 'merchant-456',
            'HTTP_PSX_ID' => 'psx-789',
            'HTTP_SVIX_ID' => 'msg_abc123',
            'HTTP_SVIX_TIMESTAMP' => '1677812345',
            'HTTP_SVIX_SIGNATURE' => 'v1,base64signature==',
            'HTTP_USER_AGENT' => 'Svix-Webhooks/1.0',
        ]);

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $this->assertEquals([
            'shopId' => 'shop-123',
            'merchantId' => 'merchant-456',
            'firebaseId' => 'psx-789',
            'Shop-Id' => 'shop-123',
            'Merchant-Id' => 'merchant-456',
            'Psx-Id' => 'psx-789',
            'Svix-Id' => 'msg_abc123',
            'Svix-Timestamp' => '1677812345',
            'Svix-Signature' => 'v1,base64signature==',
            'User-Agent' => 'Svix-Webhooks/1.0',
        ], $result);
    }

    public function testItReturnsNullForMissingSvixHeaders(): void
    {
        // Arrange
        $_SERVER = [];
        $this->mockServerHeaders([
            'HTTP_SVIX_ID' => 'msg_abc123',
            // HTTP_SVIX_TIMESTAMP and HTTP_SVIX_SIGNATURE intentionally absent
        ]);

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $this->assertSame('msg_abc123', $result['Svix-Id']);
        $this->assertNull($result['Svix-Timestamp']);
        $this->assertNull($result['Svix-Signature']);
    }

    public function testItReturnsNullForMissingMaaslandHeaders(): void
    {
        // Arrange
        $_SERVER = [];
        $this->mockServerHeaders([
            'HTTP_SHOP_ID' => 'shop-123',
            // HTTP_MERCHANT_ID and HTTP_PSX_ID intentionally absent
        ]);

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $this->assertSame('shop-123', $result['Shop-Id']);
        $this->assertNull($result['Merchant-Id']);
        $this->assertNull($result['Psx-Id']);
    }

    public function testItGetsUserAgentHeader(): void
    {
        // Arrange
        $_SERVER = [];
        $this->mockServerHeaders([
            'HTTP_USER_AGENT' => 'Svix-Webhooks/1.0',
        ]);

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $this->assertSame('Svix-Webhooks/1.0', $result['User-Agent']);
    }

    public function testItReturnsAllExpectedHeaderKeys(): void
    {
        // Arrange
        $_SERVER = [];

        // Act
        $result = $this->provider->getHeaders();

        // Assert
        $expectedKeys = [
            'shopId',
            'merchantId',
            'firebaseId',
            'Shop-Id',
            'Merchant-Id',
            'Psx-Id',
            'Svix-Id',
            'Svix-Timestamp',
            'Svix-Signature',
            'User-Agent',
        ];
        $this->assertSame($expectedKeys, array_keys($result));
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
