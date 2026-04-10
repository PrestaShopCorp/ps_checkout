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

use Order;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\OrderState\Action\SetReversedOrderStateAction;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapper;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\OrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class SetReversedOrderStateActionTest extends BaseTestCase
{
    private $payPalOrderRepository;

    private $orderStateMapper;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setReversedOrderStateAction = $this->getService(SetReversedOrderStateAction::class);
        $this->payPalOrderRepository = $this->getService(PayPalOrderRepository::class);
        $this->orderStateMapper = $this->getService(OrderStateMapper::class);
    }

    /**
     * @dataProvider provideOrderStateData
     */
    public function testSetReversedState(array $data): void
    {
        if (isset($data['expectException'])) {
            $this->expectException($data['expectException']);
            $this->expectExceptionCode($data['expectExceptionCode']);
        }

        // Create PayPal order if needed
        if (isset($data['payPalOrder'])) {
            $payPalOrder = PayPalOrderFactory::create($data['payPalOrder']);
            $this->payPalOrderRepository->savePayPalOrder($payPalOrder);
        }

        // Create PrestaShop order if needed
        if (isset($data['order'])) {
            // Convert state keys to actual IDs
            if (isset($data['order']['current_state'])) {
                $data['order']['current_state'] = $this->orderStateMapper->getIdByKey($data['order']['current_state']);
            }
            $order = OrderFactory::create($data['order']);
        }

        // Execute the action
        $this->setReversedOrderStateAction->execute($data['payPalOrderId'] ?? 'NON-EXISTENT-ORDER');

        // Verify results if no exception was expected
        if (!isset($data['expectException'])) {
            $updatedOrder = new Order($order->id);
            $expectedStateId = $this->orderStateMapper->getIdByKey($data['expectedState']);
            $this->assertEquals($expectedStateId, $updatedOrder->current_state);
        }
    }

    public function provideOrderStateData(): array
    {
        return [
            'new order should not change state when not paid' => [
                [
                    'payPalOrder' => [
                        'status' => PayPalOrderStatus::APPROVED,
                        'id_cart' => 123,
                    ],
                    'order' => [
                        'id_cart' => 123,
                        'current_state' => OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING,
                        'valid' => 1,
                    ],
                    'payPalOrderId' => 'TEST-ORDER-123',
                    'expectedState' => OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING,
                ],
            ],
            'non-existent order should throw exception' => [
                [
                    'expectException' => PsCheckoutException::class,
                    'expectExceptionCode' => PsCheckoutException::ORDER_NOT_FOUND,
                    'payPalOrderId' => 'NON-EXISTENT-ORDER',
                ],
            ],
            'already paid order should not change state' => [
                [
                    'payPalOrder' => [
                        'status' => PayPalOrderStatus::APPROVED,
                        'id_cart' => 123,
                    ],
                    'order' => [
                        'id_cart' => 123,
                        'current_state' => OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED,
                        'valid' => 1,
                    ],
                    'payPalOrderId' => 'TEST-ORDER-123',
                    'expectedState' => OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED,
                ],
            ],
            'refunded order should not change state' => [
                [
                    'payPalOrder' => [
                        'status' => PayPalOrderStatus::APPROVED,
                        'id_cart' => 123,
                    ],
                    'order' => [
                        'id_cart' => 123,
                        'current_state' => OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED,
                        'valid' => 1,
                    ],
                    'payPalOrderId' => 'TEST-ORDER-123',
                    'expectedState' => OrderStateConfiguration::PS_CHECKOUT_STATE_REFUNDED,
                ],
            ],
        ];
    }
}
