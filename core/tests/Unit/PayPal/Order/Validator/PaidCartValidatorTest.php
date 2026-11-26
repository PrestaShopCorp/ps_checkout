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

namespace PsCheckout\Core\Tests\Unit\PayPal\Order\Validator;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderCapture;
use PsCheckout\Core\PayPal\Order\Exception\CartAlreadyPaidException;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderCaptureRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Validator\PaidCartValidator;
use PsCheckout\Infrastructure\Repository\ReadOnlyCollectionRepositoryInterface;
use Psr\Log\LoggerInterface;

class PaidCartValidatorTest extends TestCase
{
    /** @var ReadOnlyCollectionRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $orderRepository;

    /** @var PayPalOrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderRepository;

    /** @var PayPalOrderCaptureRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderCaptureRepository;

    /** @var PayPalOrderProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderProvider;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var PaidCartValidator */
    private $validator;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(ReadOnlyCollectionRepositoryInterface::class);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->payPalOrderCaptureRepository = $this->createMock(PayPalOrderCaptureRepositoryInterface::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->validator = new PaidCartValidator(
            $this->orderRepository,
            $this->payPalOrderRepository,
            $this->payPalOrderCaptureRepository,
            $this->payPalOrderProvider,
            $this->logger
        );
    }

    public function testRefusesWhenCartAlreadyHasAnOrder(): void
    {
        $this->orderRepository->method('getAllBy')->with(['id_cart' => 42])->willReturn([new \stdClass()]);

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ALREADY_PAID);

        $this->validator->validate(42);
    }

    public function testPassesWhenCartHasNoPreviousPayPalOrder(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->with(42)->willReturn(null);

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->validator->validate(42);
    }

    /**
     * @dataProvider provideStatusesThatCannotBePaid
     */
    public function testPassesWithoutCallingPayPalWhenPreviousPayPalOrderCannotBePaid(string $status): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock($status));

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->validator->validate(42);
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideStatusesThatCannotBePaid(): array
    {
        return [
            'CANCELED' => ['CANCELED'],
            'VOIDED' => ['VOIDED'],
        ];
    }

    public function testPassesWhenPreviousPayPalOrderIsNotCompletedAtPayPal(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('CREATED'));
        $this->payPalOrderProvider->method('getById')->with('ORDER-A')->willReturn($this->createPayPalOrderResponseMock('APPROVED'));

        // No refusal: nothing is logged about a paid cart.
        $this->logger->expects($this->never())->method('warning');

        $this->validator->validate(42);
    }

    public function testRefusesWithThePaidPayPalOrderWhenPreviousPayPalOrderIsCompletedAtPayPal(): void
    {
        $response = $this->createPayPalOrderResponseMock('COMPLETED');
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('APPROVED'));
        $this->payPalOrderProvider->method('getById')->with('ORDER-A')->willReturn($response);

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('not created'),
                $this->callback(function (array $context): bool {
                    return $context['cart_id'] === 42 && $context['paypal_order_id'] === 'ORDER-A';
                })
            );

        try {
            $this->validator->validate(42);
            $this->fail('The paid cart was not refused');
        } catch (CartAlreadyPaidException $exception) {
            $this->assertSame(PsCheckoutException::CART_ALREADY_PAID, $exception->getCode());
            $this->assertSame($response, $exception->getPaidPayPalOrder());
        }
    }

    public function testPassesAndLogsWhenPayPalCannotBeRead(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('CREATED'));
        $this->payPalOrderProvider->method('getById')->willThrowException(new \RuntimeException('cURL error 28'));

        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('could not be read'),
                $this->callback(function (array $context): bool {
                    return $context['cart_id'] === 42 && $context['paypal_order_id'] === 'ORDER-A';
                })
            );

        $this->validator->validate(42);
    }

    /**
     * PayPal answers a declined capture with order status COMPLETED and capture status DECLINED.
     *
     * @dataProvider provideCaptureStatusesThatAreNotAPayment
     */
    public function testPassesWhenTheCaptureOfThePreviousPayPalOrderDidNotSucceed(string $captureStatus): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('COMPLETED'));
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock('COMPLETED', $captureStatus));

        // No refusal: nothing is logged about a paid cart.
        $this->logger->expects($this->never())->method('warning');

        $this->validator->validate(42);
    }

    /**
     * @return array<string, array{string}>
     */
    public function provideCaptureStatusesThatAreNotAPayment(): array
    {
        return [
            'DECLINED' => ['DECLINED'],
            'FAILED' => ['FAILED'],
        ];
    }

    public function testRefusesWhenTheCaptureOfThePreviousPayPalOrderIsPending(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('APPROVED'));
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock('COMPLETED', 'PENDING'));

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ALREADY_PAID);

        $this->validator->validate(42);
    }

    public function testRefusesWhenThePreviousPayPalOrderIsAuthorized(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('APPROVED'));
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock('COMPLETED', null, 'CREATED'));

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ALREADY_PAID);

        $this->validator->validate(42);
    }

    public function testPassesWhenTheAuthorizationOfThePreviousPayPalOrderWasDenied(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('COMPLETED'));
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock('COMPLETED', null, 'DENIED'));

        // No refusal: nothing is logged about a paid cart.
        $this->logger->expects($this->never())->method('warning');

        $this->validator->validate(42);
    }

    public function testPassesWhenTheAuthorizationsOfThePreviousPayPalOrderCannotBeRead(): void
    {
        $response = $this->createMock(PayPalOrderResponse::class);
        $response->method('getStatus')->willReturn('COMPLETED');
        $response->method('getCaptures')->willReturn([]);
        $response->method('getAuthorizations')->willThrowException(new \RuntimeException('No purchase units found in the order response.'));

        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('COMPLETED'));
        $this->payPalOrderProvider->method('getById')->willReturn($response);

        // No refusal: nothing is logged about a paid cart.
        $this->logger->expects($this->never())->method('warning');

        $this->validator->validate(42);
    }

    public function testRefusesWhenPayPalCannotBeReadAndACaptureIsCompletedLocally(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('COMPLETED'));
        $this->payPalOrderProvider->method('getById')->willThrowException(new \RuntimeException('cURL error 28'));
        $this->payPalOrderCaptureRepository->method('getByOrderId')->with('ORDER-A')->willReturn([$this->createPayPalOrderCaptureMock('COMPLETED')]);

        try {
            $this->validator->validate(42);
            $this->fail('The paid cart was not refused');
        } catch (CartAlreadyPaidException $exception) {
            $this->assertNull($exception->getPaidPayPalOrder());
        }
    }

    public function testPassesWhenPayPalCannotBeReadAndTheLocalCaptureWasDeclined(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('COMPLETED'));
        $this->payPalOrderProvider->method('getById')->willThrowException(new \RuntimeException('cURL error 28'));
        $this->payPalOrderCaptureRepository->method('getByOrderId')->with('ORDER-A')->willReturn([$this->createPayPalOrderCaptureMock('DECLINED')]);

        // Only the unreadable PayPal order is logged, the cart is not refused.
        $this->logger->expects($this->once())->method('warning')->with($this->stringContains('could not be read'));

        $this->validator->validate(42);
    }

    /**
     * @return PayPalOrder|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPayPalOrderMock(string $status)
    {
        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getId')->willReturn('ORDER-A');
        $payPalOrder->method('getStatus')->willReturn($status);

        return $payPalOrder;
    }

    /**
     * @return PayPalOrderCapture|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPayPalOrderCaptureMock(string $status)
    {
        $capture = $this->createMock(PayPalOrderCapture::class);
        $capture->method('getStatus')->willReturn($status);

        return $capture;
    }

    /**
     * @return PayPalOrderResponse|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPayPalOrderResponseMock(string $status, ?string $captureStatus = 'COMPLETED', ?string $authorizationStatus = null)
    {
        $amount = ['value' => '10.00', 'currency_code' => 'EUR'];
        $response = $this->createMock(PayPalOrderResponse::class);
        $response->method('getId')->willReturn('ORDER-A');
        $response->method('getStatus')->willReturn($status);
        $response->method('getCaptures')->willReturn(
            $status === 'COMPLETED' && $captureStatus ? [['id' => 'CAPTURE-A', 'status' => $captureStatus, 'amount' => $amount]] : []
        );
        $response->method('getAuthorizations')->willReturn(
            $authorizationStatus ? [['id' => 'AUTH-A', 'status' => $authorizationStatus, 'amount' => $amount]] : []
        );

        return $response;
    }
}
