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
                'transaction_id' => 'TEST-CAPTURE-123',
            ],
            'currencyId' => 1,
            'secureKey' => 'test-secure-key',
            'fundingSource' => 'paypal',
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
