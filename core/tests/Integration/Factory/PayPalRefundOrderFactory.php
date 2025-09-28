<?php

namespace PsCheckout\Core\Tests\Integration\Factory;

use PsCheckout\Core\PayPal\Refund\ValueObject\PayPalRefundOrder;

class PayPalRefundOrderFactory
{
    public static function create(array $data = []): PayPalRefundOrder
    {
        $defaultData = [
            'id' => 1,
            'state' => 0,
            'hasBeenPaid' => true,
            'hasBeenPartiallyRefunded' => false,
            'hasBeenRefunded' => false,
            'totalPaid' => (float) '29.00',
            'currencyId' => 0,
        ];

        $data = array_merge($defaultData, $data);

        return new PayPalRefundOrder(
            (int) $data['id'],
            (int) $data['state'],
            (bool) $data['hasBeenPaid'],
            (bool) $data['hasBeenPartiallyRefunded'],
            (bool) $data['hasBeenRefunded'],
            (string) $data['totalPaid'],
            (int) $data['currencyId']
        );
    }
}
