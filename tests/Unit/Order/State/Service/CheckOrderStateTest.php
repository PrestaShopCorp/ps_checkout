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

namespace Tests\Unit\Order\State\Service;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckOrderState;

class CheckOrderStateTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGetNewOrderState($currentOrderStateId, $newOrderStateId, $expectedResult)
    {
        $checkOrderState = new CheckOrderState();
        $result = $checkOrderState->isOrderStateTransitionAvailable($currentOrderStateId, $newOrderStateId);
        $this->assertEquals($expectedResult, $result);
    }

    public function dataProvider()
    {
        return [
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::CANCELED, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::PAYMENT_ERROR, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::REFUNDED, true],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, true],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::PAYMENT_ACCEPTED, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::REFUNDED, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, true],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::AUTHORIZED, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, true],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::PAYMENT_ERROR, true],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, true],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::PARTIALLY_PAID, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::REFUNDED, true],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::PARTIALLY_REFUNDED, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::CANCELED, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::PAYMENT_ERROR, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::PARTIALLY_PAID, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::WAITING_CAPTURE, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, true],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::CANCELED, true],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::PAYMENT_ERROR, true],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, false],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, false],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::PAYMENT_ACCEPTED, true],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::REFUNDED, false],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::AUTHORIZED, false],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::PARTIALLY_PAID, true],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::PARTIALLY_REFUNDED, false],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::WAITING_CAPTURE, false],
            [OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, false],
        ];
    }
}
