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

namespace Tests;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PaypalCountryCodeMatrice;

class IsoCodeDispatcherTest extends TestCase
{
    /**
     * @dataProvider isoCodeDataProviderPaypal
     */
    public function testgetPaypalIsoCode($resultExpect, $dataToValidate)
    {
        $this->assertSame(
            $resultExpect,
            (new PaypalCountryCodeMatrice())->getPaypalIsoCode($dataToValidate)
        );
    }

    public function isoCodeDataProviderPaypal()
    {
        return [
            [
                'AL',
                'AL',
            ],
            [
                'C2',
                'CN',
            ],
            [
                false,
                1,
            ],
            [
                false,
                [
                    'FR',
                ],
            ],
        ];
    }

    /**
     * @dataProvider isoCodeDataProviderPrestashop
     */
    public function testgetPrestashopIsoCode($resultExpect, $dataToValidate)
    {
        $this->assertSame(
            $resultExpect,
            (new PaypalCountryCodeMatrice())->getPrestashopIsoCode($dataToValidate)
        );
    }

    public function isoCodeDataProviderPrestashop()
    {
        return [
            [
                'AL',
                'AL',
            ],
            [
                'CN',
                'C2',
            ],
            [
                false,
                1,
            ],
            [
                false,
                [
                    'FR',
                ],
            ],
        ];
    }
}
