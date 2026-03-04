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

namespace PsCheckout\Core\Tests\Unit\PayPal\Payment\Authorization\Processor;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Payment\Authorization\Configuration\AuthorizationAction;
use PsCheckout\Core\PayPal\Payment\Authorization\Action\AuthorizationActionInterface;
use PsCheckout\Core\PayPal\Payment\Authorization\Processor\AuthorizationActionProcessor;
use Psr\Log\LoggerInterface;

/**
 * @covers \PsCheckout\Core\PayPal\Payment\Authorization\Processor\AuthorizationActionProcessor
 */
class AuthorizationActionProcessorTest extends TestCase
{
    /**
     * @var MockObject|AuthorizationActionInterface
     */
    private $captureAction;

    /**
     * @var MockObject|AuthorizationActionInterface
     */
    private $voidAction;

    /**
     * @var MockObject|AuthorizationActionInterface
     */
    private $reauthorizeAction;

    /**
     * @var MockObject|PayPalOrderProviderInterface
     */
    private $payPalOrderProvider;

    /**
     * @var MockObject|LoggerInterface
     */
    private $logger;

    /**
     * @var AuthorizationActionProcessor
     */
    private $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->captureAction = $this->createMock(AuthorizationActionInterface::class);
        $this->captureAction->method('supports')->willReturnCallback(function ($action) {
            return $action === AuthorizationAction::CAPTURE;
        });

        $this->voidAction = $this->createMock(AuthorizationActionInterface::class);
        $this->voidAction->method('supports')->willReturnCallback(function ($action) {
            return $action === AuthorizationAction::VOID;
        });

        $this->reauthorizeAction = $this->createMock(AuthorizationActionInterface::class);
        $this->reauthorizeAction->method('supports')->willReturnCallback(function ($action) {
            return $action === AuthorizationAction::REAUTHORIZE;
        });

        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->processor = new AuthorizationActionProcessor(
            [$this->captureAction, $this->voidAction, $this->reauthorizeAction],
            $this->payPalOrderProvider,
            $this->logger
        );
    }

    public function testProcessWithNullOrderId(): void
    {
        $result = $this->processor->process('capture', null);

        $this->assertArrayHasKey('httpCode', $result);
        $this->assertEquals(400, $result['httpCode']);
        $this->assertFalse($result['status']);
    }

    public function testProcessWithEmptyOrderId(): void
    {
        $result = $this->processor->process('capture', '');

        $this->assertArrayHasKey('httpCode', $result);
        $this->assertEquals(400, $result['httpCode']);
        $this->assertFalse($result['status']);
    }

    public function testProcessWithInvalidAction(): void
    {
        $result = $this->processor->process('invalid_action', 'ORDER-123');

        $this->assertArrayHasKey('httpCode', $result);
        $this->assertEquals(400, $result['httpCode']);
        $this->assertFalse($result['status']);
    }

    public function testProcessCaptureActionSuccess(): void
    {
        $orderId = 'ORDER-123';
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willReturn($payPalOrderResponse);

        $this->captureAction
            ->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse);

        $this->voidAction
            ->expects($this->never())
            ->method('execute');

        $this->reauthorizeAction
            ->expects($this->never())
            ->method('execute');

        $result = $this->processor->process('capture', $orderId);

        $this->assertTrue($result['status']);
        $this->assertArrayNotHasKey('httpCode', $result);
        $this->assertArrayNotHasKey('error', $result);
    }

    public function testProcessVoidActionSuccess(): void
    {
        $orderId = 'ORDER-456';
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willReturn($payPalOrderResponse);

        $this->voidAction
            ->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse);

        $this->captureAction
            ->expects($this->never())
            ->method('execute');

        $this->reauthorizeAction
            ->expects($this->never())
            ->method('execute');

        $result = $this->processor->process('void', $orderId);

        $this->assertTrue($result['status']);
        $this->assertArrayNotHasKey('httpCode', $result);
        $this->assertArrayNotHasKey('error', $result);
    }

    public function testProcessReauthorizeActionSuccess(): void
    {
        $orderId = 'ORDER-789';
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willReturn($payPalOrderResponse);

        $this->reauthorizeAction
            ->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse);

        $this->captureAction
            ->expects($this->never())
            ->method('execute');

        $this->voidAction
            ->expects($this->never())
            ->method('execute');

        $result = $this->processor->process('reauthorize', $orderId);

        $this->assertTrue($result['status']);
        $this->assertArrayNotHasKey('httpCode', $result);
        $this->assertArrayNotHasKey('error', $result);
    }

    public function testProcessWithPayPalOrderProviderException(): void
    {
        $orderId = 'ORDER-ERROR';
        $exceptionMessage = 'PayPal order not found';
        $exceptionCode = 404;

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willThrowException(new Exception($exceptionMessage, $exceptionCode));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to execute authorization action: ' . $exceptionMessage);

        $this->captureAction
            ->expects($this->never())
            ->method('execute');

        $result = $this->processor->process('capture', $orderId);

        $this->assertArrayHasKey('httpCode', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertEquals($exceptionMessage, $result['error']['message']);
        $this->assertEquals($exceptionCode, $result['error']['code']);
    }

    public function testProcessWithActionExecutionException(): void
    {
        $orderId = 'ORDER-EXEC-ERROR';
        $exceptionMessage = 'Failed to capture authorization';
        $exceptionCode = 500;
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willReturn($payPalOrderResponse);

        $this->captureAction
            ->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse)
            ->willThrowException(new Exception($exceptionMessage, $exceptionCode));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to execute authorization action: ' . $exceptionMessage);

        $result = $this->processor->process('capture', $orderId);

        $this->assertArrayHasKey('httpCode', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertEquals($exceptionMessage, $result['error']['message']);
        $this->assertEquals($exceptionCode, $result['error']['code']);
    }

    public function testProcessWithVoidActionExecutionException(): void
    {
        $orderId = 'ORDER-VOID-ERROR';
        $exceptionMessage = 'Failed to void authorization';
        $exceptionCode = 422;
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willReturn($payPalOrderResponse);

        $this->voidAction
            ->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse)
            ->willThrowException(new Exception($exceptionMessage, $exceptionCode));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to execute authorization action: ' . $exceptionMessage);

        $result = $this->processor->process('void', $orderId);

        $this->assertArrayHasKey('httpCode', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertEquals($exceptionMessage, $result['error']['message']);
        $this->assertEquals($exceptionCode, $result['error']['code']);
    }

    public function testProcessWithReauthorizeActionExecutionException(): void
    {
        $orderId = 'ORDER-REAUTH-ERROR';
        $exceptionMessage = 'Failed to reauthorize authorization';
        $exceptionCode = 403;
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willReturn($payPalOrderResponse);

        $this->reauthorizeAction
            ->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse)
            ->willThrowException(new Exception($exceptionMessage, $exceptionCode));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to execute authorization action: ' . $exceptionMessage);

        $result = $this->processor->process('reauthorize', $orderId);

        $this->assertArrayHasKey('httpCode', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertEquals($exceptionMessage, $result['error']['message']);
        $this->assertEquals($exceptionCode, $result['error']['code']);
    }

    public function testProcessCaptureActionWithPayload(): void
    {
        $orderId = 'ORDER-PAYLOAD';
        $payload = [
            'amount' => [
                'value' => '50.00',
                'currency_code' => 'EUR',
            ],
        ];
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willReturn($payPalOrderResponse);

        $this->captureAction
            ->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse, $payload);

        $result = $this->processor->process('capture', $orderId, $payload);

        $this->assertTrue($result['status']);
        $this->assertArrayNotHasKey('httpCode', $result);
        $this->assertArrayNotHasKey('error', $result);
    }

    public function testProcessWithExceptionCodeZero(): void
    {
        $orderId = 'ORDER-NO-CODE';
        $exceptionMessage = 'Unknown error';
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider
            ->expects($this->once())
            ->method('getById')
            ->with($orderId)
            ->willReturn($payPalOrderResponse);

        $this->captureAction
            ->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse)
            ->willThrowException(new Exception($exceptionMessage));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with('Failed to execute authorization action: ' . $exceptionMessage);

        $result = $this->processor->process('capture', $orderId);

        $this->assertArrayHasKey('httpCode', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertEquals(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertEquals($exceptionMessage, $result['error']['message']);
        $this->assertEquals(0, $result['error']['code']);
    }
}
