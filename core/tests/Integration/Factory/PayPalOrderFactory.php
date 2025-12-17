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

namespace PsCheckout\Core\Tests\Integration\Factory;

use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;

class PayPalOrderFactory
{
    public static function create(array $data = []): PayPalOrder
    {
        $defaultData = [
            'id' => 'TEST-ORDER-123',
            'id_cart' => 1,
            'intent' => PayPalOrderIntent::CAPTURE,
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
