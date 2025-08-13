<?php

namespace PsCheckout\Core\Tests\Integration\OrderState\Action;

use OrderHistory;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\OrderState\Action\SetPendingOrderStateAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapper;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\CartFactory;
use PsCheckout\Core\Tests\Integration\Factory\OrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class SetPendingOrderStateActionTest extends BaseTestCase
{
    private ?SetPendingOrderStateAction $setPendingOrderStateAction;
    private ?OrderStateMapper $orderStateMapper;
    private ?PayPalOrderRepository $payPalOrderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setPendingOrderStateAction = $this->getService(SetPendingOrderStateAction::class);
        $this->orderStateMapper = $this->getService(OrderStateMapper::class);
        $this->payPalOrderRepository = $this->getService(PayPalOrderRepository::class);
    }

    public function testItShouldSetOrderStateToPending(): void
    {
        $cart = CartFactory::create();
        //NOTE: Any state id except pending
        $order = OrderFactory::create([
            'current_state' => $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR),
            'id_cart' => $cart->id
        ]);
        $payPalOrder = PayPalOrderFactory::create(['id_cart' => $order->id_cart]);
        $this->payPalOrderRepository->save($payPalOrder);

        $this->setPendingOrderStateAction->execute($payPalOrder->getId());

        self::assertEquals(
            $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING),
            (new \Order($order->id))->current_state
        );
    }

    public function testItShouldNotChangeStateIfAlreadyPending(): void
    {
        $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING);

        $cart = CartFactory::create();
        $order = OrderFactory::create([
            'current_state' => $orderStateId,
            'id_cart' => $cart->id
        ]);

        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->changeIdOrderState($orderStateId, $order->id);
        $history->add();

        $payPalOrder = PayPalOrderFactory::create(['id_cart' => $order->id_cart]);
        $this->payPalOrderRepository->save($payPalOrder);

        $this->setPendingOrderStateAction->execute($payPalOrder->getId());

        self::assertEquals(
            $orderStateId,
            (new \Order($order->id))->current_state
        );
    }

    public function testItShouldThrowPayPalOrderDoesNotExistException(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal order not found.');

        $this->setPendingOrderStateAction->execute('not-existing-id');
    }
}
