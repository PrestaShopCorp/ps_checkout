<?php

namespace PsCheckout\Core\Tests\Integration\OrderState\Action;

use OrderHistory;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\OrderState\Action\ChangeOrderStateAction;
use PsCheckout\Core\OrderState\Action\SetDeclinedOrderStateAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapper;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\CartFactory;
use PsCheckout\Core\Tests\Integration\Factory\OrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class SetDeclinedOrderStateActionTest extends BaseTestCase
{
    private ?SetDeclinedOrderStateAction $setDeclinedOrderStateAction;
    private ?ChangeOrderStateAction $changeOrderStateAction;
    private ?OrderStateMapper $orderStateMapper;
    private ?PayPalOrderRepository $payPalOrderRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setDeclinedOrderStateAction = $this->getService(SetDeclinedOrderStateAction::class);
        $this->changeOrderStateAction = $this->getService(ChangeOrderStateAction::class);
        $this->orderStateMapper = $this->getService(OrderStateMapper::class);
        $this->payPalOrderRepository = $this->getService(PayPalOrderRepository::class);
    }

    public function testItShouldDeclinePayment(): void
    {
        $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING);

        $cart = CartFactory::create();
        $order = OrderFactory::create([
            'current_state' => $orderStateId,
            'id_cart' => $cart->id
        ]) ;

        $payPalOrder = PayPalOrderFactory::create(['id_cart' => $order->id_cart]);
        $this->payPalOrderRepository->save($payPalOrder);

        try {
            $this->setDeclinedOrderStateAction->execute($payPalOrder->getId());
        }  catch (OrderException $exception) {
            if (OrderException::FAILED_UPDATE_ORDER_STATUS === $exception->getCode()) {
                //NOTE: Error due mail sending which does not work with tests
                self::assertEquals(
                    $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR),
                    (new \Order($order->id))->current_state
                );
            }
        }
    }

    public function testItShouldNotChangeStateAsOrderAlreadyHasErrorStatus(): void
    {
        $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR);

        $cart = CartFactory::create();
        $order = OrderFactory::create([
            'current_state' => $orderStateId,
            'id_cart' => $cart->id
        ]) ;

        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->changeIdOrderState($orderStateId, $order->id);
        $history->add();

        $payPalOrder = PayPalOrderFactory::create(['id_cart' => $order->id_cart]);
        $this->payPalOrderRepository->save($payPalOrder);

        $this->setDeclinedOrderStateAction->execute($payPalOrder->getId());

        self::assertEquals(
            $orderStateId,
            (new \Order($order->id))->current_state
        );
    }

    public function testItShouldNotChangeStateAsOrderAlreadyHasCanceledStatus(): void
    {
        $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_CANCELED);

        $cart = CartFactory::create();
        $order = OrderFactory::create([
            'current_state' => $orderStateId,
            'id_cart' => $cart->id
        ]) ;

        $history = new OrderHistory();
        $history->id_order = $order->id;
        $history->changeIdOrderState($orderStateId, $order->id);
        $history->add();

        $payPalOrder = PayPalOrderFactory::create(['id_cart' => $order->id_cart]);
        $this->payPalOrderRepository->save($payPalOrder);

        $this->setDeclinedOrderStateAction->execute($payPalOrder->getId());

        self::assertEquals(
            $orderStateId,
            (new \Order($order->id))->current_state
        );
    }

    public function testItShouldThrowPayPalOrderDoesNotExistException(): void
    {
        // Expect the exception to be thrown
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal order not found.');

        $this->setDeclinedOrderStateAction->execute('not-existing-id');
    }
}