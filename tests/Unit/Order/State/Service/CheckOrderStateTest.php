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

namespace Tests\Unit\PayPal;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Order\Service\CheckOrderAmount;
use PrestaShop\Module\PrestashopCheckout\Order\State\Factory\OrderStateMappingFactory;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfiguration;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckOrderState;
use PrestaShop\Module\PrestashopCheckout\Order\State\Service\CheckTransitionStateService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CheckTransitionPayPalOrderStatusService;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\PayPalCaptureStatus;

class CheckOrderStateTest extends TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testGetNewOrderState($currentOrderStateId,$newOrderStateId, $expectedResult)
    {
        $checkOrderState = new CheckOrderState();
        $result = $checkOrderState->isOrderStateTransitionAvailable($currentOrderStateId,$newOrderStateId);
        $this->assertEquals($expectedResult, $result);
    }

    public function dataProvider()
    {
        return [
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::CANCELED, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::PAYMENT_ERROR, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::OUT_OF_STOCK_UNPAID, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::OUT_OF_STOCK_PAID, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::REFUNDED,true],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::PARTIALLY_REFUNDED,true],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::PAYMENT_ACCEPTED, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::REFUNDED, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::PARTIALLY_REFUNDED,true],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::AUTHORIZED, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,true],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::PAYMENT_ERROR,true],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::PAYMENT_ACCEPTED,true],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::PARTIALLY_PAID, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::REFUNDED,true],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::PARTIALLY_REFUNDED, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::CANCELED,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::PAYMENT_ERROR,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::PAYMENT_ACCEPTED,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::WAITING_CAPTURE, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,true],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::CANCELED,true],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::PAYMENT_ERROR,true],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::OUT_OF_STOCK_UNPAID,false],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::OUT_OF_STOCK_PAID,false],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::PAYMENT_ACCEPTED,true],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::REFUNDED,false],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::AUTHORIZED,false],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::PARTIALLY_PAID,false],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::PARTIALLY_REFUNDED,false],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::WAITING_CAPTURE,false],
            [OrderStateConfiguration::WAITING_PAYPAL_PAYMENT, OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,false],
        ];
    }
}
