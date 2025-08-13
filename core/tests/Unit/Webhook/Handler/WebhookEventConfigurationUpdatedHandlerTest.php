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

namespace PsCheckout\Tests\Unit\Webhook\Handler;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Webhook\Handler\WebhookEventConfigurationUpdatedHandler;
use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class WebhookEventConfigurationUpdatedHandlerTest extends TestCase
{
    /** @var WebhookEventConfigurationUpdatedHandler */
    private $handler;

    /** @var ConfigurationInterface|MockObject */
    private $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->handler = new WebhookEventConfigurationUpdatedHandler($this->configuration);
    }

    public function testItSupportsConfigurationUpdatedEvent(): void
    {
        // Arrange
        $payload = [
            'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
        ];

        // Act
        $result = $this->handler->supports($payload);

        // Assert
        $this->assertTrue($result);
    }

    public function testItDoesNotSupportOtherEvents(): void
    {
        // Arrange
        $payload = [
            'eventType' => 'SOME.OTHER.EVENT',
        ];

        // Act
        $result = $this->handler->supports($payload);

        // Assert
        $this->assertFalse($result);
    }

    public function testItHandlesValidConfigurationUpdate(): void
    {
        // Arrange
        $payload = [
            'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
            'resource' => [
                'configuration' => [
                    [
                        'name' => 'PS_CHECKOUT_TEST',
                        'value' => 'test_value',
                    ],
                    [
                        'name' => 'PS_CHECKOUT_OTHER',
                        'value' => 'other_value',
                    ],
                ],
            ],
        ];

        $this->configuration->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                ['PS_CHECKOUT_TEST', 'test_value'],
                ['PS_CHECKOUT_OTHER', 'other_value']
            );

        // Act
        $result = $this->handler->handle($payload);

        // Assert
        $this->assertTrue($result);
    }

    public function testItThrowsExceptionForMissingConfiguration(): void
    {
        // Arrange
        $payload = [
            'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
            'resource' => [],
        ];

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Configuration list is empty');
        $this->expectExceptionCode(WebhookException::WEBHOOK_PAYLOAD_CONFIGURATION_LIST_MISSING);

        // Act
        $this->handler->handle($payload);
    }

    public function testItThrowsExceptionForInvalidConfigurationName(): void
    {
        // Arrange
        $payload = [
            'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
            'resource' => [
                'configuration' => [
                    [
                        'name' => 'INVALID_PREFIX_TEST',
                        'value' => 'test_value',
                    ],
                ],
            ],
        ];

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Configuration name is invalid');
        $this->expectExceptionCode(WebhookException::WEBHOOK_PAYLOAD_CONFIGURATION_NAME_INVALID);

        // Act
        $this->handler->handle($payload);
    }

    public function testItThrowsExceptionForEmptyConfigurationName(): void
    {
        // Arrange
        $payload = [
            'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
            'resource' => [
                'configuration' => [
                    [
                        'name' => '',
                        'value' => 'test_value',
                    ],
                ],
            ],
        ];

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Configuration name is invalid');
        $this->expectExceptionCode(WebhookException::WEBHOOK_PAYLOAD_CONFIGURATION_NAME_INVALID);

        // Act
        $this->handler->handle($payload);
    }

    public function testItThrowsExceptionForNonArrayConfiguration(): void
    {
        // Arrange
        $payload = [
            'eventType' => 'PRESTASHOP.CONFIGURATION.UPDATED',
            'resource' => [
                'configuration' => 'not_an_array',
            ],
        ];

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Configuration list is empty');
        $this->expectExceptionCode(WebhookException::WEBHOOK_PAYLOAD_CONFIGURATION_LIST_MISSING);

        // Act
        $this->handler->handle($payload);
    }
}
