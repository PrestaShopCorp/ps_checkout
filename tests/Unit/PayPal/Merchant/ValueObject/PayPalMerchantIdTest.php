<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace Tests\Unit\PayPal\Merchant\ValueObject;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\Merchant\Exception\PayPalMerchantException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Merchant\ValueObject\PayPalMerchantId;
use stdClass;

class PayPalMerchantIdTest extends TestCase
{
    public function testValidValueDoesNotThrowException()
    {
        $payPalMerchantId = new PayPalMerchantId('T3STM3RCH4NT');
        $this->assertEquals('T3STM3RCH4NT', $payPalMerchantId->getValue());
    }

    /**
     * @dataProvider invalidValueProvider
     */
    public function testInvalidValueThrowsException($value)
    {
        $this->expectException(PayPalMerchantException::class);
        $this->expectExceptionCode(PayPalMerchantException::INVALID_ID);
        $this->expectExceptionMessage(sprintf('PayPal merchant id %s is invalid. PayPal merchant id must be an alphanumeric string.', var_export($value, true)));
        new PayPalMerchantId($value);
    }

    public function invalidValueProvider()
    {
        return [
            ['invalid_string'],
            [3.14],
            [[]],
            [false],
            [new stdClass()],
        ];
    }
}
