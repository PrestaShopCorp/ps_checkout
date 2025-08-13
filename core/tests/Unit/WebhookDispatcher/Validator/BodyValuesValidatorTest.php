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

namespace PsCheckout\Tests\Unit\WebhookDispatcher\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Core\WebhookDispatcher\Provider\WebhookBodyProviderInterface;
use PsCheckout\Core\WebhookDispatcher\Validator\BodyValuesValidator;

class BodyValuesValidatorTest extends TestCase
{
    /** @var BodyValuesValidator */
    private $validator;

    /** @var WebhookBodyProviderInterface|MockObject */
    private $webhookBodyProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookBodyProvider = $this->createMock(WebhookBodyProviderInterface::class);
        $this->validator = new BodyValuesValidator($this->webhookBodyProvider);
    }

    public function testItValidatesAndTransformsValidBody(): void
    {
        // Arrange
        $bodyValues = [
            'resource' => ['id' => '123', 'status' => 'completed'],
            'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
            'category' => 'PAYMENT',
            'summary' => 'Payment completed',
            'orderId' => 'ORDER-123',
        ];

        $this->webhookBodyProvider->expects($this->once())
            ->method('getBody')
            ->willReturn($bodyValues);

        // Act
        $result = $this->validator->validate();

        // Assert
        $this->assertEquals([
            'resource' => ['id' => '123', 'status' => 'completed'],
            'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
            'category' => 'PAYMENT',
            'summary' => 'Payment completed',
            'orderId' => 'ORDER-123',
        ], $result);
    }

    public function testItTransformsBodyWithOptionalFields(): void
    {
        // Arrange
        $bodyValues = [
            'resource' => ['id' => '123'],
            'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
            'category' => 'PAYMENT',
        ];

        $this->webhookBodyProvider->expects($this->once())
            ->method('getBody')
            ->willReturn($bodyValues);

        // Act
        $result = $this->validator->validate();

        // Assert
        $this->assertEquals([
            'resource' => ['id' => '123'],
            'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
            'category' => 'PAYMENT',
            'summary' => null,
            'orderId' => null,
        ], $result);
    }

    /**
     * @dataProvider provideMissingRequiredFields
     */
    public function testItThrowsExceptionForMissingRequiredFields(array $bodyValues, string $expectedMessage): void
    {
        // Arrange
        $this->webhookBodyProvider->expects($this->once())
            ->method('getBody')
            ->willReturn($bodyValues);

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Body validation failed: Missing required field: ' . $expectedMessage);
        $this->expectExceptionCode(400);

        // Act
        $this->validator->validate();
    }

    public function provideMissingRequiredFields(): array
    {
        return [
            'missing_resource' => [
                'bodyValues' => [
                    'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
                    'category' => 'PAYMENT',
                ],
                'expectedMessage' => 'resource',
            ],
            'missing_eventType' => [
                'bodyValues' => [
                    'resource' => ['id' => '123'],
                    'category' => 'PAYMENT',
                ],
                'expectedMessage' => 'eventType',
            ],
            'missing_category' => [
                'bodyValues' => [
                    'resource' => ['id' => '123'],
                    'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
                ],
                'expectedMessage' => 'category',
            ],
            'empty_resource' => [
                'bodyValues' => [
                    'resource' => '',
                    'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
                    'category' => 'PAYMENT',
                ],
                'expectedMessage' => 'resource',
            ],
        ];
    }

    public function testItThrowsExceptionWhenBodyProviderFails(): void
    {
        // Arrange
        $errorMessage = 'Failed to get body';
        $this->webhookBodyProvider->expects($this->once())
            ->method('getBody')
            ->willThrowException(new \InvalidArgumentException($errorMessage));

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Body validation failed: ' . $errorMessage);
        $this->expectExceptionCode(400);

        // Act
        $this->validator->validate();
    }
}
