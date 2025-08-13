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

namespace PsCheckout\Tests\Unit\WebhookDispatcher\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Core\WebhookDispatcher\Action\CheckPSLSignatureAction;

class CheckPSLSignatureActionTest extends TestCase
{
    /** @var CheckPSLSignatureAction */
    private $action;

    /** @var OrderHttpClientInterface|MockObject */
    private $orderHttpClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderHttpClient = $this->createMock(OrderHttpClientInterface::class);
        $this->action = new CheckPSLSignatureAction($this->orderHttpClient);
    }

    public function testItSuccessfullyValidatesPSLSignature(): void
    {
        // Arrange
        $bodyValues = ['test' => 'value'];
        $this->orderHttpClient->expects($this->once())
            ->method('getShopSignature')
            ->with($bodyValues)
            ->willReturn([
                'statusCode' => 200,
                'message' => 'VERIFIED',
            ]);

        // Act
        $result = $this->action->execute($bodyValues);

        // Assert
        $this->assertTrue($result);
    }

    public function testItThrowsExceptionWhenSignatureIsInvalid(): void
    {
        // Arrange
        $bodyValues = ['test' => 'value'];
        $this->orderHttpClient->expects($this->once())
            ->method('getShopSignature')
            ->with($bodyValues)
            ->willReturn([
                'statusCode' => 401,
                'message' => 'INVALID',
            ]);

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid PSL signature');
        $this->expectExceptionCode(401);

        // Act
        $this->action->execute($bodyValues);
    }

    public function testItThrowsExceptionWhenResponseIsInvalid(): void
    {
        // Arrange
        $bodyValues = ['test' => 'value'];
        $this->orderHttpClient->expects($this->once())
            ->method('getShopSignature')
            ->with($bodyValues)
            ->willReturn([
                'statusCode' => 200,
                'message' => 'INVALID',
            ]);

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid PSL signature');
        $this->expectExceptionCode(401);

        // Act
        $this->action->execute($bodyValues);
    }

    public function testItThrowsExceptionWhenResponseIsMissingRequiredFields(): void
    {
        // Arrange
        $bodyValues = ['test' => 'value'];
        $this->orderHttpClient->expects($this->once())
            ->method('getShopSignature')
            ->with($bodyValues)
            ->willReturn([
                'statusCode' => 200,
            ]);

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Invalid PSL signature');
        $this->expectExceptionCode(401);

        // Act
        $this->action->execute($bodyValues);
    }
}
