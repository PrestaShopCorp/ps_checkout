<?php

namespace PsCheckout\Core\Tests\Integration\Factory;

use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderPurchaseUnit;

class PayPalOrderPurchaseUnitFactory
{
    public static function create(array $data = []): PayPalOrderPurchaseUnit
    {
        $defaultData = [
            'id_order' => 'PAY-123',
            'id' => 123456,
            'reference_id' => 'default',
            'items' => [
                [
                    'name' => 'Test Product',
                    'quantity' => '1',
                    'unit_amount' => [
                        'currency_code' => 'EUR',
                        'value' => '10.00'
                    ]
                ]
            ]
        ];

        $data = array_merge($defaultData, $data);

        return new PayPalOrderPurchaseUnit(
            $data['id_order'],
            $data['id'],
            $data['reference_id'],
            $data['items']
        );
    }
}
