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
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Core\WebhookDispatcher\Provider\WebhookHeaderProviderInterface;
use PsCheckout\Core\WebhookDispatcher\Validator\HeaderValuesValidator;

class HeaderValuesValidatorTest extends TestCase
{
    /** @var HeaderValuesValidator */
    private $validator;

    /** @var WebhookHeaderProviderInterface|MockObject */
    private $webhookHeaderProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookHeaderProvider = $this->createMock(WebhookHeaderProviderInterface::class);
        $this->validator = new HeaderValuesValidator($this->webhookHeaderProvider);
    }

    public function testItValidatesAndTransformsValidHeaders(): void
    {
        // Arrange
        $headers = [
            'Shop-Id' => 'shop-123',
            'Merchant-Id' => 'merchant-456',
            'Psx-Id' => 'firebase-789',
        ];

        $this->webhookHeaderProvider->expects($this->once())
            ->method('getHeaders')
            ->willReturn($headers);

        // Act
        $result = $this->validator->validate();

        // Assert
        $this->assertEquals([
            'shopId' => 'shop-123',
            'merchantId' => 'merchant-456',
            'firebaseId' => 'firebase-789',
        ], $result);
    }

    /**
     * @dataProvider provideInvalidHeaders
     */
    public function testItThrowsExceptionForMissingHeaders(array $headers, string $expectedMessage, int $expectedCode): void
    {
        // Arrange
        $this->webhookHeaderProvider->expects($this->once())
            ->method('getHeaders')
            ->willReturn($headers);

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Header validation failed: ' . $expectedMessage);
        $this->expectExceptionCode(400);

        // Act
        $this->validator->validate();
    }

    public function provideInvalidHeaders(): array
    {
        return [
            'missing_shop_id' => [
                'headers' => [
                    'Merchant-Id' => 'merchant-456',
                    'Psx-Id' => 'firebase-789',
                ],
                'expectedMessage' => 'Shop-Id can\'t be empty',
                'expectedCode' => PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_EMPTY,
            ],
            'missing_merchant_id' => [
                'headers' => [
                    'Shop-Id' => 'shop-123',
                    'Psx-Id' => 'firebase-789',
                ],
                'expectedMessage' => 'Merchant-Id can\'t be empty',
                'expectedCode' => PsCheckoutException::PSCHECKOUT_WEBHOOK_MERCHANT_ID_EMPTY,
            ],
            'missing_psx_id' => [
                'headers' => [
                    'Shop-Id' => 'shop-123',
                    'Merchant-Id' => 'merchant-456',
                ],
                'expectedMessage' => 'Psx-Id can\'t be empty',
                'expectedCode' => PsCheckoutException::PSCHECKOUT_WEBHOOK_PSX_ID_EMPTY,
            ],
            'empty_shop_id' => [
                'headers' => [
                    'Shop-Id' => '',
                    'Merchant-Id' => 'merchant-456',
                    'Psx-Id' => 'firebase-789',
                ],
                'expectedMessage' => 'Shop-Id can\'t be empty',
                'expectedCode' => PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_EMPTY,
            ],
        ];
    }

    public function testItThrowsExceptionWhenHeaderProviderFails(): void
    {
        // Arrange
        $errorMessage = 'Failed to get headers';
        $this->webhookHeaderProvider->expects($this->once())
            ->method('getHeaders')
            ->willThrowException(new \InvalidArgumentException($errorMessage));

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Header validation failed: ' . $errorMessage);
        $this->expectExceptionCode(400);

        // Act
        $this->validator->validate();
    }
}
