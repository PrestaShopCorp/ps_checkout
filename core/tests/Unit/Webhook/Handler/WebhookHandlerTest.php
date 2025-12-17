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
use PsCheckout\Core\Webhook\Handler\WebhookEventHandlerInterface;
use PsCheckout\Core\Webhook\Handler\WebhookHandler;
use PsCheckout\Core\Webhook\Service\WebhookTokenInterface;
use PsCheckout\Core\Webhook\WebhookException;

class WebhookHandlerTest extends TestCase
{
    /** @var WebhookHandler */
    private $handler;

    /** @var WebhookTokenInterface|MockObject */
    private $webhookSecretTokenService;

    /** @var WebhookEventHandlerInterface|MockObject */
    private $eventHandler1;

    /** @var WebhookEventHandlerInterface|MockObject */
    private $eventHandler2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->webhookSecretTokenService = $this->createMock(WebhookTokenInterface::class);
        $this->eventHandler1 = $this->createMock(WebhookEventHandlerInterface::class);
        $this->eventHandler2 = $this->createMock(WebhookEventHandlerInterface::class);

        $this->handler = new WebhookHandler(
            $this->webhookSecretTokenService,
            [$this->eventHandler1, $this->eventHandler2]
        );
    }

    public function testItAuthenticatesValidToken(): void
    {
        // Arrange
        $token = 'valid-token';

        $this->webhookSecretTokenService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn(true);

        // Act
        $result = $this->handler->authenticate($token);

        // Assert
        $this->assertTrue($result);
    }

    public function testItRejectsInvalidToken(): void
    {
        // Arrange
        $token = 'invalid-token';

        $this->webhookSecretTokenService->expects($this->once())
            ->method('validateToken')
            ->with($token)
            ->willReturn(false);

        // Act
        $result = $this->handler->authenticate($token);

        // Assert
        $this->assertFalse($result);
    }

    public function testItHandlesPayloadWithSupportedHandler(): void
    {
        // Arrange
        $payload = ['eventType' => 'TEST_EVENT'];

        $this->eventHandler1->expects($this->once())
            ->method('supports')
            ->with($payload)
            ->willReturn(true);

        $this->eventHandler1->expects($this->once())
            ->method('handle')
            ->with($payload)
            ->willReturn(true);

        $this->eventHandler2->expects($this->never())
            ->method('supports');

        // Act
        $result = $this->handler->handle($payload);

        // Assert
        $this->assertTrue($result);
    }

    public function testItChecksAllHandlersUntilSupported(): void
    {
        // Arrange
        $payload = ['eventType' => 'TEST_EVENT'];

        $this->eventHandler1->expects($this->once())
            ->method('supports')
            ->with($payload)
            ->willReturn(false);

        $this->eventHandler2->expects($this->once())
            ->method('supports')
            ->with($payload)
            ->willReturn(true);

        $this->eventHandler2->expects($this->once())
            ->method('handle')
            ->with($payload)
            ->willReturn(true);

        // Act
        $result = $this->handler->handle($payload);

        // Assert
        $this->assertTrue($result);
    }

    public function testItThrowsExceptionForUnsupportedWebhook(): void
    {
        // Arrange
        $payload = ['eventType' => 'UNSUPPORTED_EVENT'];

        $this->eventHandler1->expects($this->once())
            ->method('supports')
            ->with($payload)
            ->willReturn(false);

        $this->eventHandler2->expects($this->once())
            ->method('supports')
            ->with($payload)
            ->willReturn(false);

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Unsupported webhook');
        $this->expectExceptionCode(WebhookException::WEBHOOK_PAYLOAD_UNSUPPORTED);

        // Act
        $this->handler->handle($payload);
    }

    public function testItPassesHandlerExceptionsThrough(): void
    {
        // Arrange
        $payload = ['eventType' => 'TEST_EVENT'];

        $this->eventHandler1->expects($this->once())
            ->method('supports')
            ->with($payload)
            ->willReturn(true);

        $this->eventHandler1->expects($this->once())
            ->method('handle')
            ->with($payload)
            ->willThrowException(new WebhookException('Handler error', 400));

        // Assert
        $this->expectException(WebhookException::class);
        $this->expectExceptionMessage('Handler error');
        $this->expectExceptionCode(400);

        // Act
        $this->handler->handle($payload);
    }
}
