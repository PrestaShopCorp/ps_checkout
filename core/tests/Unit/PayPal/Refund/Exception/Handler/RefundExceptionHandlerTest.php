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

namespace PsCheckout\Tests\Unit\PayPal\Refund\Exception\Handler;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\PayPal\Refund\Exception\Handler\RefundExceptionHandler;
use PsCheckout\Core\PayPal\Refund\Exception\PayPalRefundException;
use PsCheckout\Presentation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class RefundExceptionHandlerTest extends TestCase
{
    /** @var RefundExceptionHandler */
    private $handler;

    /** @var TranslatorInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnCallback(function ($key) {
            return $key;
        });

        $this->logger = $this->createMock(LoggerInterface::class);

        $this->handler = new RefundExceptionHandler(
            $this->translator,
            $this->logger
        );
    }

    /**
     * @dataProvider payPalRefundExceptionProvider
     */
    public function testPayPalRefundExceptionReturns400(int $code, string $expectedMessage): void
    {
        $exception = new PayPalRefundException('test', $code);

        /** @var array{httpCode: int, status: bool, errors: array<string>} $result */
        $result = $this->handler->handle($exception);

        $this->assertSame(400, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame($expectedMessage, $result['errors'][0]);
    }

    /**
     * @return array<string, array{int, string}>
     */
    public function payPalRefundExceptionProvider(): array
    {
        return [
            'INVALID_ORDER_ID' => [
                PayPalRefundException::INVALID_ORDER_ID,
                'PayPal Order is invalid.',
            ],
            'INVALID_TRANSACTION_ID' => [
                PayPalRefundException::INVALID_TRANSACTION_ID,
                'PayPal Transaction is invalid.',
            ],
            'INVALID_CURRENCY' => [
                PayPalRefundException::INVALID_CURRENCY,
                'PayPal refund currency is invalid.',
            ],
            'INVALID_AMOUNT' => [
                PayPalRefundException::INVALID_AMOUNT,
                'PayPal refund amount is invalid.',
            ],
            'REFUND_FAILED' => [
                PayPalRefundException::REFUND_FAILED,
                'PayPal refund failed.',
            ],
        ];
    }

    /**
     * @dataProvider payPalExceptionProvider
     */
    public function testPayPalExceptionReturns400WithMessage(int $code, string $expectedMessage): void
    {
        $exception = new PayPalException('test', $code);

        $this->logger->expects($this->once())->method('error');

        /** @var array{httpCode: int, status: bool, errors: array<string>} $result */
        $result = $this->handler->handle($exception);

        $this->assertSame(400, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame($expectedMessage, $result['errors'][0]);
    }

    /**
     * @return array<string, array{int, string}>
     */
    public function payPalExceptionProvider(): array
    {
        return [
            'REFUND_TIME_LIMIT_EXCEEDED' => [
                PayPalException::REFUND_TIME_LIMIT_EXCEEDED,
                'The refund time limit has been exceeded for this transaction.',
            ],
            'REFUND_FAILED_INSUFFICIENT_FUNDS' => [
                PayPalException::REFUND_FAILED_INSUFFICIENT_FUNDS,
                'Refund failed due to insufficient funds in the PayPal account.',
            ],
            'REFUND_NOT_ALLOWED' => [
                PayPalException::REFUND_NOT_ALLOWED,
                'A full refund is not allowed because a partial refund has already been issued.',
            ],
            'REFUND_CAPTURE_CURRENCY_MISMATCH' => [
                PayPalException::REFUND_CAPTURE_CURRENCY_MISMATCH,
                'The refund currency must match the capture currency.',
            ],
            'REFUND_AMOUNT_EXCEEDED' => [
                PayPalException::REFUND_AMOUNT_EXCEEDED,
                'The refund amount exceeds the remaining capturable amount.',
            ],
            'CAPTURE_FULLY_REFUNDED' => [
                PayPalException::CAPTURE_FULLY_REFUNDED,
                'This capture has already been fully refunded.',
            ],
            'CAPTURE_DISPUTED_PARTIAL_REFUND_NOT_ALLOWED' => [
                PayPalException::CAPTURE_DISPUTED_PARTIAL_REFUND_NOT_ALLOWED,
                'A partial refund cannot be issued while there is an open dispute on this capture.',
            ],
            'REFUND_NOT_PERMITTED_DUE_TO_CHARGEBACK' => [
                PayPalException::REFUND_NOT_PERMITTED_DUE_TO_CHARGEBACK,
                'Refund is not permitted due to a chargeback on this transaction.',
            ],
            'MAX_NUMBER_OF_REFUNDS_EXCEEDED' => [
                PayPalException::MAX_NUMBER_OF_REFUNDS_EXCEEDED,
                'The maximum number of refunds for this capture has been reached.',
            ],
            'PARTIAL_REFUND_NOT_ALLOWED' => [
                PayPalException::PARTIAL_REFUND_NOT_ALLOWED,
                'Partial refund is not allowed for this capture. Only a full refund can be issued.',
            ],
            'PENDING_CAPTURE' => [
                PayPalException::PENDING_CAPTURE,
                'Cannot refund a pending capture. Please wait until the capture is completed.',
            ],
            'CANNOT_PROCESS_REFUNDS' => [
                PayPalException::CANNOT_PROCESS_REFUNDS,
                'PayPal cannot process refunds at this time. Please try again later.',
            ],
            'INVALID_REFUND_AMOUNT' => [
                PayPalException::INVALID_REFUND_AMOUNT,
                'The refund amount is invalid.',
            ],
            'REFUND_AMOUNT_TOO_LOW' => [
                PayPalException::REFUND_AMOUNT_TOO_LOW,
                'The refund amount is too low.',
            ],
            'TRANSACTION_DISPUTED' => [
                PayPalException::TRANSACTION_DISPUTED,
                'This transaction is under dispute. Refund cannot be processed.',
            ],
            'REFUND_IS_RESTRICTED' => [
                PayPalException::REFUND_IS_RESTRICTED,
                'Refund is restricted for this transaction.',
            ],
            'CURRENCY_MISMATCH' => [
                PayPalException::CURRENCY_MISMATCH,
                'The currency does not match the capture currency.',
            ],
        ];
    }

    public function testUnknownPayPalExceptionFallsBackToDefaultMessage(): void
    {
        $exception = new PayPalException('SOME_UNKNOWN_ERROR', 99999);

        $this->logger->expects($this->once())->method('error');

        /** @var array{httpCode: int, status: bool, errors: array<string>} $result */
        $result = $this->handler->handle($exception);

        $this->assertSame(400, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame('Refund cannot be processed by PayPal. (SOME_UNKNOWN_ERROR)', $result['errors'][0]);
    }

    public function testOrderExceptionFailedUpdateReturns200(): void
    {
        $exception = new OrderException('Failed', OrderException::FAILED_UPDATE_ORDER_STATUS);

        /** @var array{httpCode: int, status: bool, content: string} $result */
        $result = $this->handler->handle($exception);

        $this->assertSame(200, $result['httpCode']);
        $this->assertTrue($result['status']);
        $this->assertSame(
            'Refund has been processed by PayPal, but order status change or email sending failed.',
            $result['content']
        );
    }

    public function testOrderExceptionAlreadyThisStatusReturnsNull(): void
    {
        $exception = new OrderException('Already', OrderException::ORDER_HAS_ALREADY_THIS_STATUS);

        $result = $this->handler->handle($exception);

        $this->assertNull($result);
    }

    public function testOrderExceptionOtherCodeReturns500(): void
    {
        $exception = new OrderException('Something else', 99);

        /** @var array{httpCode: int, status: bool, errors: array<string>, error: string} $result */
        $result = $this->handler->handle($exception);

        $this->assertSame(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame('Something else', $result['errors'][0]);
    }

    public function testGenericExceptionReturns500(): void
    {
        $exception = new \RuntimeException('Unexpected error');

        $this->logger->expects($this->once())->method('error');

        /** @var array{httpCode: int, status: bool, errors: array<string>, error: string} $result */
        $result = $this->handler->handle($exception);

        $this->assertSame(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame('Refund cannot be processed by PayPal.', $result['errors'][0]);
        $this->assertSame('Unexpected error', $result['error']);
    }
}
