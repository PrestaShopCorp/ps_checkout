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

namespace tests\Unit\Order\Comparator;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Comparator\PayPalOrderComparator;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderPaymentSource;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderPurchaseUnit;

class OrderComparatorTest extends TestCase
{
    /**
     * @dataProvider orderProvider
     */
    public function testCheckIntent(PayPalOrder $oldOrder, PayPalOrder $newOrder, $differenceKey)
    {
        $orderComparatorService = new PayPalOrderComparator();
        $result = $orderComparatorService->getFieldsToUpdate($oldOrder, $newOrder);
        if (is_array($differenceKey)) {
            foreach ($differenceKey as $key) {
                $this->assertArrayHasKey($key, $result);
            }
        } else {
            $this->assertArrayHasKey($differenceKey, $result);
        }
    }

    public function orderProvider()
    {
        return [
            [
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CREATE'),
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE'),
                'intent',
            ],
            [
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', null, [new PayPalOrderPurchaseUnit('123', 123, 'EUR', 100)]),
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', null, [new PayPalOrderPurchaseUnit('123', 123, 'USD', 100)]),
                'purchase_units',
            ],
            [
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', null, [new PayPalOrderPurchaseUnit('123', 123, 'EUR', 100)]),
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', null, [new PayPalOrderPurchaseUnit('123', 123, 'USD', 50)]),
                'purchase_units',
            ],
            [
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', new PayPalOrderPaymentSource('paypal', 'PAYPAL')),
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', null, [new PayPalOrderPurchaseUnit('123', 123, 'USD', 50)]),
                'payment_source',
            ],
            [
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', new PayPalOrderPaymentSource('paypal', 'PAYPAL')),
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', null, [new PayPalOrderPurchaseUnit('123', 123, 'USD', 50)]),
                ['payment_source', 'purchase_units'],
            ],
            [
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CREATE', new PayPalOrderPaymentSource('paypal', 'PAYPAL')),
                new PayPalOrder(new PayPalOrderId('123'), 'CREATED', 'CAPTURE', null, [new PayPalOrderPurchaseUnit('123', 123, 'USD', 50)]),
                ['intent', 'payment_source', 'purchase_units'],
            ],
        ];
    }
}
