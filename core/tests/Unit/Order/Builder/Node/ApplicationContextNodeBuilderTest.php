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

namespace PsCheckout\Tests\Core\Order\Builder\Node;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\Node\ApplicationContextNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;

class ApplicationContextNodeBuilderTest extends TestCase
{
    /**
     * @dataProvider applicationContextDataProvider
     */
    public function testBuild(bool $isExpressCheckout, string $expectedShippingPreference): void
    {
        // Arrange
        $configurationMock = $this->createMock(ConfigurationInterface::class);
        $configurationMock->method('get')
            ->with('PS_SHOP_NAME')
            ->willReturn('Test Shop');

        $linkMock = $this->createMock(LinkInterface::class);
        $linkMock->method('getModuleLink')
            ->willReturnMap([
                ['validate', [], 'https://example.com/validate'],
                ['cancel', [], 'https://example.com/cancel'],
            ]);

        $builder = new ApplicationContextNodeBuilder($configurationMock, $linkMock);
        $builder->setIsExpressCheckout($isExpressCheckout);

        // Act
        $result = $builder->build();

        // Assert
        $this->assertArrayHasKey('application_context', $result);
        $this->assertEquals('Test Shop', $result['application_context']['brand_name']);
        $this->assertEquals($expectedShippingPreference, $result['application_context']['shipping_preference']);
        $this->assertEquals('https://example.com/validate', $result['application_context']['return_url']);
        $this->assertEquals('https://example.com/cancel', $result['application_context']['cancel_url']);
    }

    public function applicationContextDataProvider(): array
    {
        return [
            'Express Checkout' => [
                true,
                'GET_FROM_FILE',
            ],
            'Regular Checkout' => [
                false,
                'SET_PROVIDED_ADDRESS',
            ],
        ];
    }

    public function testBuildWithEmptyShopName(): void
    {
        // Arrange
        $configurationMock = $this->createMock(ConfigurationInterface::class);
        $configurationMock->method('get')
            ->with('PS_SHOP_NAME')
            ->willReturn(''); // Empty shop name

        $linkMock = $this->createMock(LinkInterface::class);
        $linkMock->method('getModuleLink')
            ->willReturnMap([
                ['validate', [], 'https://example.com/validate'],
                ['cancel', [], 'https://example.com/cancel'],
            ]);

        $builder = new ApplicationContextNodeBuilder($configurationMock, $linkMock);
        $builder->setIsExpressCheckout(false);

        // Act
        $result = $builder->build();

        // Assert
        $this->assertArrayHasKey('application_context', $result);
        $this->assertEquals('', $result['application_context']['brand_name']); // Expect empty brand name
        $this->assertEquals('SET_PROVIDED_ADDRESS', $result['application_context']['shipping_preference']);
        $this->assertEquals('https://example.com/validate', $result['application_context']['return_url']);
        $this->assertEquals('https://example.com/cancel', $result['application_context']['cancel_url']);
    }

    public function testBuildWithNullShopName(): void
    {
        // Arrange
        $configurationMock = $this->createMock(ConfigurationInterface::class);
        $configurationMock->method('get')
            ->with('PS_SHOP_NAME')
            ->willReturn(null); // Null shop name

        $linkMock = $this->createMock(LinkInterface::class);
        $linkMock->method('getModuleLink')
            ->willReturnMap([
                ['validate', [], 'https://example.com/validate'],
                ['cancel', [], 'https://example.com/cancel'],
            ]);

        $builder = new ApplicationContextNodeBuilder($configurationMock, $linkMock);
        $builder->setIsExpressCheckout(true);

        // Act
        $result = $builder->build();

        // Assert
        $this->assertArrayHasKey('application_context', $result);
        $this->assertNull($result['application_context']['brand_name']); // Expect null brand name
        $this->assertEquals('GET_FROM_FILE', $result['application_context']['shipping_preference']);
        $this->assertEquals('https://example.com/validate', $result['application_context']['return_url']);
        $this->assertEquals('https://example.com/cancel', $result['application_context']['cancel_url']);
    }

    public function testBuildWithCustomReturnAndCancelUrls(): void
    {
        // Arrange
        $configurationMock = $this->createMock(ConfigurationInterface::class);
        $configurationMock->method('get')
            ->with('PS_SHOP_NAME')
            ->willReturn('Custom Shop');

        $linkMock = $this->createMock(LinkInterface::class);
        $linkMock->method('getModuleLink')
            ->willReturnMap([
                ['validate', [], 'https://custom.com/validate'],
                ['cancel', [], 'https://custom.com/cancel'],
            ]);

        $builder = new ApplicationContextNodeBuilder($configurationMock, $linkMock);
        $builder->setIsExpressCheckout(false);

        // Act
        $result = $builder->build();

        // Assert
        $this->assertArrayHasKey('application_context', $result);
        $this->assertEquals('Custom Shop', $result['application_context']['brand_name']);
        $this->assertEquals('SET_PROVIDED_ADDRESS', $result['application_context']['shipping_preference']);
        $this->assertEquals('https://custom.com/validate', $result['application_context']['return_url']);
        $this->assertEquals('https://custom.com/cancel', $result['application_context']['cancel_url']);
    }

    public function testSetIsExpressCheckout(): void
    {
        // Arrange
        $configurationMock = $this->createMock(ConfigurationInterface::class);
        $linkMock = $this->createMock(LinkInterface::class);

        $builder = new ApplicationContextNodeBuilder($configurationMock, $linkMock);

        // Act
        $result = $builder->setIsExpressCheckout(true);

        // Assert
        $this->assertInstanceOf(ApplicationContextNodeBuilder::class, $result);
    }
}
