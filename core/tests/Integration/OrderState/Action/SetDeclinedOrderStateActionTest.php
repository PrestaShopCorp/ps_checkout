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
    /** @var SetDeclinedOrderStateAction */
    private $setDeclinedOrderStateAction;

    /** @var ChangeOrderStateAction */
    private $changeOrderStateAction;

    /** @var OrderStateMapper */
    private $orderStateMapper;

    /** @var PayPalOrderRepository */
    private $payPalOrderRepository;

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
            'id_cart' => $cart->id,
        ]);

        $payPalOrder = PayPalOrderFactory::create(['id_cart' => $order->id_cart]);
        $this->payPalOrderRepository->save($payPalOrder);

        try {
            $this->setDeclinedOrderStateAction->execute($payPalOrder->getId());
        } catch (OrderException $exception) {
            if (OrderException::FAILED_UPDATE_ORDER_STATUS !== $exception->getCode()) {
                throw $exception;
            }
            // NOTE: Email sending fails in test environment; the state transition itself still happened.
        }

        self::assertEquals(
            $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR),
            (new \Order($order->id))->current_state
        );
    }

    public function testItShouldNotChangeStateAsOrderAlreadyHasErrorStatus(): void
    {
        $orderStateId = $this->orderStateMapper->getIdByKey(OrderStateConfiguration::PS_CHECKOUT_STATE_ERROR);

        $cart = CartFactory::create();
        $order = OrderFactory::create([
            'current_state' => $orderStateId,
            'id_cart' => $cart->id,
        ]);

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
            'id_cart' => $cart->id,
        ]);

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
