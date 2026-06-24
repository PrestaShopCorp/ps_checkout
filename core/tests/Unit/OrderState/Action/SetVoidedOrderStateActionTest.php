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

namespace PsCheckout\Core\Tests\Unit\OrderState\Action;

use Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\OrderState\Action\ChangeOrderStateActionInterface;
use PsCheckout\Core\OrderState\Action\SetVoidedOrderStateAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapperInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Repository\OrderRepository;

class SetVoidedOrderStateActionTest extends TestCase
{
    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $payPalOrderRepository;

    /** @var OrderRepository|MockObject */
    private $orderRepository;

    /** @var ConfigurationInterface|MockObject */
    private $configuration;

    /** @var OrderStateMapperInterface|MockObject */
    private $orderStateMapper;

    /** @var ChangeOrderStateActionInterface|MockObject */
    private $changeOrderStateAction;

    /** @var SetVoidedOrderStateAction */
    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->orderStateMapper = $this->createMock(OrderStateMapperInterface::class);
        $this->changeOrderStateAction = $this->createMock(ChangeOrderStateActionInterface::class);

        $this->action = new SetVoidedOrderStateAction(
            $this->payPalOrderRepository,
            $this->orderRepository,
            $this->configuration,
            $this->orderStateMapper,
            $this->changeOrderStateAction
        );
    }

    public function testItShouldChangeStateToVoided(): void
    {
        $payPalOrderId = 'PAYPAL-ORDER-123';
        $idCart = 456;
        $orderId = 789;
        $voidedStateId = 18;

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getIdCart')->willReturn($idCart);

        $order = $this->createMock(Order::class);
        $order->id = $orderId;
        $order->id_lang = 1;

        $order->expects($this->once())
            ->method('getHistory')
            ->with(1, $voidedStateId)
            ->willReturn([]);

        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => $payPalOrderId])
            ->willReturn($payPalOrder);

        $this->orderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id_cart' => $idCart])
            ->willReturn($order);

        $this->configuration->expects($this->once())
            ->method('getInteger')
            ->with(OrderStateConfiguration::PS_CHECKOUT_STATE_VOIDED)
            ->willReturn($voidedStateId);

        $this->orderStateMapper->expects($this->once())
            ->method('getIdByKey')
            ->with(OrderStateConfiguration::PS_CHECKOUT_STATE_VOIDED)
            ->willReturn($voidedStateId);

        $this->changeOrderStateAction->expects($this->once())
            ->method('execute')
            ->with($orderId, (string) $voidedStateId);

        $this->action->execute($payPalOrderId);
    }

    public function testItShouldThrowPayPalOrderDoesNotExistException(): void
    {
        $payPalOrderId = 'NON-EXISTING-ORDER';

        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => $payPalOrderId])
            ->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal order not found.');
        $this->expectExceptionCode(PsCheckoutException::ORDER_NOT_FOUND);

        $this->action->execute($payPalOrderId);
    }

    public function testItShouldNotChangeStateWhenOrderDoesNotExist(): void
    {
        $payPalOrderId = 'PAYPAL-ORDER-456';
        $idCart = 999;

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getIdCart')->willReturn($idCart);

        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => $payPalOrderId])
            ->willReturn($payPalOrder);

        $this->orderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id_cart' => $idCart])
            ->willReturn(null);

        $this->changeOrderStateAction->expects($this->never())
            ->method('execute');

        $this->action->execute($payPalOrderId);
    }

    public function testItShouldNotChangeStateWhenOrderHasNoId(): void
    {
        $payPalOrderId = 'PAYPAL-ORDER-789';
        $idCart = 123;

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getIdCart')->willReturn($idCart);

        $order = $this->createMock(Order::class);
        $order->id = null;

        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => $payPalOrderId])
            ->willReturn($payPalOrder);

        $this->orderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id_cart' => $idCart])
            ->willReturn($order);

        $this->changeOrderStateAction->expects($this->never())
            ->method('execute');

        $this->action->execute($payPalOrderId);
    }

    public function testItShouldNotChangeStateWhenOrderAlreadyVoided(): void
    {
        $payPalOrderId = 'PAYPAL-ORDER-ABC';
        $idCart = 111;
        $orderId = 222;
        $voidedStateId = 18;

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getIdCart')->willReturn($idCart);

        $order = $this->createMock(Order::class);
        $order->id = $orderId;
        $order->id_lang = 1;

        $order->expects($this->once())
            ->method('getHistory')
            ->with(1, $voidedStateId)
            ->willReturn([['id_order_state' => $voidedStateId]]);

        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => $payPalOrderId])
            ->willReturn($payPalOrder);

        $this->orderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id_cart' => $idCart])
            ->willReturn($order);

        $this->configuration->expects($this->once())
            ->method('getInteger')
            ->with(OrderStateConfiguration::PS_CHECKOUT_STATE_VOIDED)
            ->willReturn($voidedStateId);

        $this->changeOrderStateAction->expects($this->never())
            ->method('execute');

        $this->action->execute($payPalOrderId);
    }
}
