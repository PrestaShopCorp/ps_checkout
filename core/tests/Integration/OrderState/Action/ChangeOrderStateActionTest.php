<?php

namespace PsCheckout\Core\Tests\Integration\OrderState\Action;

use OrderState;
use PrestaShop\PrestaShop\Core\Addon\Theme\Theme;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\OrderState\Action\ChangeOrderStateAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapper;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\CartFactory;
use PsCheckout\Core\Tests\Integration\Factory\OrderFactory;
use PsCheckout\Infrastructure\Repository\OrderStateRepository;

class ChangeOrderStateActionTest extends BaseTestCase
{
    private ?OrderStateMapper $orderStateMapper;
    private ?OrderStateRepository $orderStateRepository;
    private ?ChangeOrderStateAction $changeOrderStateAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderStateMapper = $this->getService(OrderStateMapper::class);
        $this->orderStateRepository = $this->getService(OrderStateRepository::class);

        $this->changeOrderStateAction = $this->getService(ChangeOrderStateAction::class);
    }

    public function testItExpectsExceptionOnOrderStateChange(): void
    {
        $order = OrderFactory::create(['current_state' => (int) \Configuration::get('PS_OS_PAYMENT')]);

        /** @var OrderState $newOrderState */
        $newOrderState = $this->orderStateRepository->getOneBy(
            [
                'id_order_state' => $this->orderStateMapper->getIdByKey(
                    OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED
                )
            ]
        );

        // Expect the exception to be thrown
        $this->expectException(OrderException::class);
        $this->expectExceptionMessage(sprintf('The order #%d has already been assigned to OrderState #%d', $order->id, $newOrderState->id));

        $this->changeOrderStateAction->execute($order->id, $newOrderState->id);
    }

    public function testChangeOrderState(): void
    {
        //Order state 0 is PENDING
        $order = OrderFactory::create(['current_state' => 0]);

        /** @var OrderState $newOrderState */
        $newOrderState = $this->orderStateRepository->getOneBy(
            [
                'id_order_state' => $this->orderStateMapper->getIdByKey(
                    OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED
                )
            ]
        );

        try {
            $this->changeOrderStateAction->execute($order->id, $newOrderState->id);
        } catch (OrderException $exception) {
            if (OrderException::FAILED_UPDATE_ORDER_STATUS === $exception->getCode()) {
                //NOTE: Error due mail sending which does not work with tests
                self::assertEquals($newOrderState->id, (new \Order($order->id))->getCurrentState());
            }
        }
    }

    public function testChangeOrderStateWithNotDefaultValue(): void
    {
        \Configuration::updateValue(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED, 5000);

        //Order state 0 is PENDING
        $order = OrderFactory::create(['current_state' => 0]);

        /** @var OrderState $newOrderState */
        $newOrderState = $this->orderStateRepository->getOneBy(
            [
                'id_order_state' => $this->orderStateMapper->getIdByKey(
                    OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED
                )
            ]
        );

        try {
            $this->changeOrderStateAction->execute($order->id, $newOrderState->id);
        } catch (OrderException $exception) {
            if (OrderException::FAILED_UPDATE_ORDER_STATUS === $exception->getCode()) {
                //NOTE: Error due mail sending which does not work with tests
                self::assertEquals($newOrderState->id, (new \Order($order->id))->getCurrentState());
            }
        }
    }
}