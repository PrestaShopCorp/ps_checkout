<?php

namespace PsCheckout\Core\Tests\Integration\Factory;

use PsCheckout\Core\Order\ValueObject\ValidateOrderData;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;

class ValidateOrderDataFactory
{
    public static function create(array $data = []): ValidateOrderData
    {
        $defaultData = [
            'cartId' => 1,
            'orderStateId' => OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED,
            'paidAmount' => 29.00,
            'extraVars' => [
                'transaction_id' => 'TEST-CAPTURE-123'
            ],
            'currencyId' => 1,
            'secureKey' => 'test-secure-key',
            'fundingSource' => 'paypal'
        ];

        $data = array_merge($defaultData, $data);

        return new ValidateOrderData(
            $data['cartId'],
            $data['orderStateId'],
            $data['paidAmount'],
            $data['extraVars'],
            $data['currencyId'],
            $data['secureKey'],
            $data['fundingSource']
        );
    }
}
