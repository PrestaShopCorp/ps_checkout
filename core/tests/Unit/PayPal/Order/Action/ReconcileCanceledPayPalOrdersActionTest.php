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

namespace PsCheckout\Core\Tests\Unit\PayPal\Order\Action;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Action\ReconcileCanceledPayPalOrdersAction;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Handler\EventHandlerInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\CartRepositoryInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class ReconcileCanceledPayPalOrdersActionTest extends TestCase
{
    /** @var PayPalOrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderRepository;

    /** @var OrderRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $orderRepository;

    /** @var CartRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cartRepository;

    /** @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /** @var PayPalOrderProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderProvider;

    /** @var PayPalOrderCacheInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalOrderCache;

    /** @var EventHandlerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $orderApprovedEventHandler;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var ReconcileCanceledPayPalOrdersAction */
    private $action;

    protected function setUp(): void
    {
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->orderRepository = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->addMethods(['getAllBy'])
            ->getMock();
        $this->cartRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->addMethods(['getOneBy'])
            ->getMock();
        $this->context = $this->createMock(ContextInterface::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->payPalOrderCache = $this->createMock(PayPalOrderCacheInterface::class);
        $this->orderApprovedEventHandler = $this->createMock(EventHandlerInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->action = new ReconcileCanceledPayPalOrdersAction(
            $this->payPalOrderRepository,
            $this->orderRepository,
            $this->cartRepository,
            $this->context,
            $this->payPalOrderProvider,
            $this->payPalOrderCache,
            $this->orderApprovedEventHandler,
            $this->logger
        );
    }

    public function testExecuteDoesNothingWithoutCandidates(): void
    {
        $this->payPalOrderRepository->method('getCanceledSince')->willReturn([]);

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->orderApprovedEventHandler->expects($this->never())->method('handle');

        $this->assertSame(0, $this->action->execute());
    }

    public function testExecuteResumesOrderApprovedOnPayPalSide(): void
    {
        $payPalOrder = $this->createPayPalOrderMock();
        $this->payPalOrderRepository->method('getCanceledSince')->willReturn([$payPalOrder]);
        $this->orderRepository->method('getAllBy')->with(['id_cart' => 42])->willReturn([]);
        $cart = $this->createCartMock();
        $this->cartRepository->method('getOneBy')->with(['id_cart' => 42])->willReturn($cart);
        $this->payPalOrderCache->method('has')->with('ORDER-123')->willReturn(true);
        $response = $this->createPayPalOrderResponseMock('APPROVED');
        $this->payPalOrderProvider->method('getById')->with('ORDER-123')->willReturn($response);

        $this->context->expects($this->once())->method('loadCartForWebhook')->with($cart);
        $this->payPalOrderCache->expects($this->once())->method('delete')->with('ORDER-123');
        $this->orderApprovedEventHandler->expects($this->once())->method('handle')->with($response);
        $payPalOrder->expects($this->never())->method('addTag');

        $this->assertSame(1, $this->action->execute());
    }

    /**
     * @dataProvider pendingPayPalStatusProvider
     */
    public function testExecuteKeepsCheckingWhileBuyerMayStillPay(string $payPalStatus): void
    {
        $payPalOrder = $this->createPayPalOrderMock();
        $this->payPalOrderRepository->method('getCanceledSince')->willReturn([$payPalOrder]);
        $this->orderRepository->method('getAllBy')->willReturn([]);
        $this->cartRepository->method('getOneBy')->willReturn($this->createCartMock());
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock($payPalStatus));

        $this->orderApprovedEventHandler->expects($this->never())->method('handle');
        $payPalOrder->expects($this->never())->method('addTag');
        $this->payPalOrderRepository->expects($this->never())->method('save');

        $this->assertSame(0, $this->action->execute());
    }

    public function pendingPayPalStatusProvider(): array
    {
        return [
            'created' => ['CREATED'],
            'payer action required' => ['PAYER_ACTION_REQUIRED'],
            'pending approval' => ['PENDING_APPROVAL'],
        ];
    }

    /**
     * @dataProvider terminalPayPalStatusProvider
     */
    public function testExecuteMarksOrderReconciledOnTerminalPayPalStatus(string $payPalStatus): void
    {
        $payPalOrder = $this->createPayPalOrderMock();
        $this->payPalOrderRepository->method('getCanceledSince')->willReturn([$payPalOrder]);
        $this->orderRepository->method('getAllBy')->willReturn([]);
        $this->cartRepository->method('getOneBy')->willReturn($this->createCartMock());
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock($payPalStatus));

        $this->orderApprovedEventHandler->expects($this->never())->method('handle');
        $payPalOrder->expects($this->once())->method('addTag')->with(PayPalOrder::RECONCILED);
        $this->payPalOrderRepository->expects($this->once())->method('save')->with($payPalOrder);

        $this->assertSame(0, $this->action->execute());
    }

    public function terminalPayPalStatusProvider(): array
    {
        return [
            'voided' => ['VOIDED'],
            'reversed' => ['REVERSED'],
            'completed' => ['COMPLETED'],
        ];
    }

    public function testExecuteWarnsWhenPayPalOrderIsCapturedWithoutPrestaShopOrder(): void
    {
        $this->payPalOrderRepository->method('getCanceledSince')->willReturn([$this->createPayPalOrderMock()]);
        $this->orderRepository->method('getAllBy')->willReturn([]);
        $this->cartRepository->method('getOneBy')->willReturn($this->createCartMock());
        $this->payPalOrderProvider->method('getById')->willReturn($this->createPayPalOrderResponseMock('COMPLETED'));

        $this->logger->expects($this->once())->method('warning');

        $this->action->execute();
    }

    public function testExecuteMarksOrderReconciledWithoutCallingPayPalWhenPrestaShopOrderExists(): void
    {
        $payPalOrder = $this->createPayPalOrderMock();
        $this->payPalOrderRepository->method('getCanceledSince')->willReturn([$payPalOrder]);
        $this->orderRepository->method('getAllBy')->willReturn([new \stdClass()]);

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->orderApprovedEventHandler->expects($this->never())->method('handle');
        $payPalOrder->expects($this->once())->method('addTag')->with(PayPalOrder::RECONCILED);
        $this->payPalOrderRepository->expects($this->once())->method('save');

        $this->assertSame(0, $this->action->execute());
    }

    public function testExecuteMarksOrderReconciledWhenCartIsGone(): void
    {
        $payPalOrder = $this->createPayPalOrderMock();
        $this->payPalOrderRepository->method('getCanceledSince')->willReturn([$payPalOrder]);
        $this->orderRepository->method('getAllBy')->willReturn([]);
        $this->cartRepository->method('getOneBy')->willReturn(null);

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $payPalOrder->expects($this->once())->method('addTag')->with(PayPalOrder::RECONCILED);

        $this->assertSame(0, $this->action->execute());
    }

    public function testExecuteLogsAndContinuesWhenOneOrderFails(): void
    {
        $failing = $this->createPayPalOrderMock('ORDER-FAIL', 7);
        $resumable = $this->createPayPalOrderMock();
        $this->payPalOrderRepository->method('getCanceledSince')->willReturn([$failing, $resumable]);
        $this->orderRepository->method('getAllBy')->willReturn([]);
        $this->cartRepository->method('getOneBy')->willReturn($this->createCartMock());
        $this->payPalOrderProvider->method('getById')->willReturnCallback(function (string $id) {
            if ($id === 'ORDER-FAIL') {
                throw new PsCheckoutException('PayPal Order not found', PsCheckoutException::PAYPAL_ORDER_NOT_FOUND);
            }

            return $this->createPayPalOrderResponseMock('APPROVED');
        });

        $this->logger->expects($this->once())->method('error');
        $this->orderApprovedEventHandler->expects($this->once())->method('handle');

        $this->assertSame(1, $this->action->execute());
    }

    /**
     * @return PayPalOrder|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPayPalOrderMock(string $id = 'ORDER-123', int $cartId = 42)
    {
        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getId')->willReturn($id);
        $payPalOrder->method('getIdCart')->willReturn($cartId);
        $payPalOrder->method('getStatus')->willReturn('CANCELED');

        return $payPalOrder;
    }

    /**
     * @return \Cart|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createCartMock()
    {
        $cart = $this->createMock(\Cart::class);
        $cart->id = 42;

        return $cart;
    }

    /**
     * @return PayPalOrderResponse|\PHPUnit\Framework\MockObject\MockObject
     */
    private function createPayPalOrderResponseMock(string $status)
    {
        $response = $this->createMock(PayPalOrderResponse::class);
        $response->method('getId')->willReturn('ORDER-123');
        $response->method('getStatus')->willReturn($status);

        return $response;
    }
}
