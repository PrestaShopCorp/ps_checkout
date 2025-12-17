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
