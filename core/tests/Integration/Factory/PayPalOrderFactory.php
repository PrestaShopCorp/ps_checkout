<?php

namespace PsCheckout\Core\Tests\Integration\Factory;

use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;

class PayPalOrderFactory
{
    public static function create(array $data = []): PayPalOrder
    {
        $defaultData = [
            'id' => 'TEST-ORDER-123',
            'id_cart' => 1,
            'intent' => 'CAPTURE',
            'funding_source' => 'paypal',
            'status' => 'PENDING',
            'payment_source' => [],
            'environment' => 'SANDBOX',
            'is_card_fields' => false,
            'is_express_checkout' => false,
            'customer_intent' => [],
            'payment_token_id' => null,
        ];

        $data = array_merge($defaultData, $data);

        return new PayPalOrder(
            $data['id'],
            $data['id_cart'],
            $data['intent'],
            $data['funding_source'],
            $data['status'],
            $data['payment_source'],
            $data['environment'],
            $data['is_card_fields'],
            $data['is_express_checkout'],
            $data['customer_intent'],
            $data['payment_token_id']
        );
    }
}
