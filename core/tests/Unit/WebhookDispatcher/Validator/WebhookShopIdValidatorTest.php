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
use PsCheckout\Core\WebhookDispatcher\Validator\WebhookShopIdValidator;
use PsCheckout\Infrastructure\Repository\PsAccountRepositoryInterface;

class WebhookShopIdValidatorTest extends TestCase
{
    /** @var WebhookShopIdValidator */
    private $validator;

    /** @var PsAccountRepositoryInterface|MockObject */
    private $psAccountRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->psAccountRepository = $this->createMock(PsAccountRepositoryInterface::class);
        $this->validator = new WebhookShopIdValidator($this->psAccountRepository);
    }

    public function testItValidatesMatchingShopId(): void
    {
        // Arrange
        $shopId = 'valid-shop-uuid';

        $this->psAccountRepository->expects($this->once())
            ->method('getShopUuid')
            ->willReturn($shopId);

        // Act
        $result = $this->validator->validate($shopId);

        // Assert
        $this->assertTrue($result);
    }

    public function testItThrowsExceptionWhenShopIdDoesNotMatch(): void
    {
        // Arrange
        $shopId = 'invalid-shop-uuid';
        $actualShopUuid = 'actual-shop-uuid';

        $this->psAccountRepository->expects($this->once())
            ->method('getShopUuid')
            ->willReturn($actualShopUuid);

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid Shop-Id');
        $this->expectExceptionCode(401);

        // Act
        $this->validator->validate($shopId);
    }

    public function testItThrowsExceptionWhenRepositoryFails(): void
    {
        // Arrange
        $shopId = 'shop-123';
        $errorMessage = 'Database error';

        $this->psAccountRepository->expects($this->once())
            ->method('getShopUuid')
            ->willThrowException(new \Exception($errorMessage));

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Failed to validate shop context: ' . $errorMessage);
        $this->expectExceptionCode(401);

        // Act
        $this->validator->validate($shopId);
    }
}
