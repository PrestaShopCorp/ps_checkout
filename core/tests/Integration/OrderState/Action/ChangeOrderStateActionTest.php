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

use OrderState;
use PsCheckout\Core\Order\Exception\OrderException;
use PsCheckout\Core\OrderState\Action\ChangeOrderStateAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapper;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\OrderFactory;
use PsCheckout\Infrastructure\Repository\OrderStateRepository;

class ChangeOrderStateActionTest extends BaseTestCase
{
    /** @var OrderStateMapper */
    private $orderStateMapper;

    /** @var OrderStateRepository */
    private $orderStateRepository;

    /** @var ChangeOrderStateAction */
    private $changeOrderStateAction;

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
                ),
            ]
        );

        // Expect the exception to be thrown
        $this->expectException(OrderException::class);
        $this->expectExceptionMessage(sprintf('The order #%d has already been assigned to OrderState #%d', $order->id, $newOrderState->id));

        $this->changeOrderStateAction->execute($order->id, $newOrderState->id);
    }

    public function testChangeOrderState(): void
    {
        // Order state 0 is PENDING
        $order = OrderFactory::create(['current_state' => 0]);

        /** @var OrderState $newOrderState */
        $newOrderState = $this->orderStateRepository->getOneBy(
            [
                'id_order_state' => $this->orderStateMapper->getIdByKey(
                    OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED
                ),
            ]
        );

        try {
            $this->changeOrderStateAction->execute($order->id, $newOrderState->id);
        } catch (OrderException $exception) {
            if (OrderException::FAILED_UPDATE_ORDER_STATUS !== $exception->getCode()) {
                throw $exception;
            }
            // NOTE: Email sending fails in test environment; the state transition itself still happened.
        }

        self::assertEquals($newOrderState->id, (new \Order($order->id))->getCurrentState());
    }

    public function testChangeOrderStateWithNotDefaultValue(): void
    {
        \Configuration::updateValue(OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED, 5000);

        // Order state 0 is PENDING
        $order = OrderFactory::create(['current_state' => 0]);

        /** @var OrderState $newOrderState */
        $newOrderState = $this->orderStateRepository->getOneBy(
            [
                'id_order_state' => $this->orderStateMapper->getIdByKey(
                    OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED
                ),
            ]
        );

        try {
            $this->changeOrderStateAction->execute($order->id, $newOrderState->id);
        } catch (OrderException $exception) {
            if (OrderException::FAILED_UPDATE_ORDER_STATUS !== $exception->getCode()) {
                throw $exception;
            }
            // NOTE: Email sending fails in test environment; the state transition itself still happened.
        }

        self::assertEquals($newOrderState->id, (new \Order($order->id))->getCurrentState());
    }
}
