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
use PsCheckout\Core\Order\Action\CreateOrderActionInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
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

    /** @var PayPalOrderProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderProvider;

    /** @var CreateOrderActionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $createOrderAction;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var PaidCartValidator */
    private $validator;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(ReadOnlyCollectionRepositoryInterface::class);
        $this->orderRepository->method('getAllBy')->willReturn([]);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->createOrderAction = $this->createMock(CreateOrderActionInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->validator = new PaidCartValidator(
            $this->orderRepository,
            $this->payPalOrderRepository,
            $this->payPalOrderProvider,
            $this->createOrderAction,
            $this->logger
        );
    }

    public function testRefusesWhenCartAlreadyHasAnOrder(): void
    {
        $orderRepository = $this->createMock(ReadOnlyCollectionRepositoryInterface::class);
        $orderRepository->method('getAllBy')->with(['id_cart' => 42])->willReturn([new \stdClass()]);
        $validator = new PaidCartValidator(
            $orderRepository,
            $this->payPalOrderRepository,
            $this->payPalOrderProvider,
            $this->createOrderAction,
            $this->logger
        );

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->createOrderAction->expects($this->never())->method('execute');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ALREADY_PAID);

        $validator->validate(42);
    }

    public function testPassesWhenCartHasNoPreviousPayPalOrder(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->with(42)->willReturn(null);

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->createOrderAction->expects($this->never())->method('execute');

        $this->validator->validate(42);
    }

    /**
     * @dataProvider provideStatusesThatCannotBePaid
     */
    public function testPassesWithoutCallingPayPalWhenPreviousPayPalOrderCannotBePaid(string $status): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock($status));

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->createOrderAction->expects($this->never())->method('execute');

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

        $this->createOrderAction->expects($this->never())->method('execute');

        $this->validator->validate(42);
    }

    public function testRefusesAndCreatesTheOrderWhenPreviousPayPalOrderIsCompletedAtPayPal(): void
    {
        $response = $this->createPayPalOrderResponseMock('COMPLETED');
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('APPROVED'));
        $this->payPalOrderProvider->method('getById')->with('ORDER-A')->willReturn($response);

        $this->createOrderAction->expects($this->once())->method('execute')->with($response);
        $this->logger->expects($this->once())
            ->method('warning')
            ->with(
                $this->stringContains('not created'),
                $this->callback(function (array $context): bool {
                    return $context['cart_id'] === 42 && $context['paypal_order_id'] === 'ORDER-A';
                })
            );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ALREADY_PAID);

        $this->validator->validate(42);
    }

    public function testStillRefusesWhenTheOrderCannotBeCreated(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('APPROVED'));
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock('COMPLETED'));
        $this->createOrderAction->method('execute')->willThrowException(
            new PsCheckoutException('Order cannot be saved', PsCheckoutException::PRESTASHOP_VALIDATE_ORDER)
        );

        $this->logger->expects($this->once())->method('error');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ALREADY_PAID);

        $this->validator->validate(42);
    }

    public function testDoesNotLogAnErrorWhenTheOrderWasCreatedInTheMeantime(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('APPROVED'));
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock('COMPLETED'));
        $this->createOrderAction->method('execute')->willThrowException(
            new PsCheckoutException('Order already exist', PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS)
        );

        $this->logger->expects($this->never())->method('error');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ALREADY_PAID);

        $this->validator->validate(42);
    }

    public function testPassesAndLogsWhenPayPalCannotBeRead(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('CREATED'));
        $this->payPalOrderProvider->method('getById')->willThrowException(new \RuntimeException('cURL error 28'));

        $this->createOrderAction->expects($this->never())->method('execute');
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

    public function testRefusesWhenPreviousPayPalOrderIsCompletedLocallyAndPayPalCannotBeRead(): void
    {
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($this->createPayPalOrderMock('COMPLETED'));
        $this->payPalOrderProvider->method('getById')->willThrowException(new \RuntimeException('cURL error 28'));

        $this->createOrderAction->expects($this->never())->method('execute');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::CART_ALREADY_PAID);

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
     * @return PayPalOrderResponse|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPayPalOrderResponseMock(string $status)
    {
        $response = $this->createMock(PayPalOrderResponse::class);
        $response->method('getId')->willReturn('ORDER-A');
        $response->method('getStatus')->willReturn($status);

        return $response;
    }
}
