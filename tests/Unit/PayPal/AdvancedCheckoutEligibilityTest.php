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

namespace Tests\Unit\PayPal;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\PayPal\AdvancedCheckoutEligibility;

class AdvancedCheckoutEligibilityTest extends TestCase
{
    private $advancedCheckoutEligibility;

    public function setUp()
    {
        $this->advancedCheckoutEligibility = new AdvancedCheckoutEligibility();
    }

    public function testGetSupportedCountries()
    {
        $this->assertEquals(
            ['AU', 'AT', 'BE', 'BG', 'CA', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'JP', 'LV', 'LI', 'LT', 'LU', 'MT', 'MX', 'NL', 'NO', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB', 'US'],
            $this->advancedCheckoutEligibility->getSupportedCountries()
        );
    }

    public function testGetSupportedCurrenciesByCountry()
    {
        $this->assertEquals(
            ['AUD', 'CAD', 'EUR', 'GBP', 'JPY', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('US')
        );
        $this->assertEquals(
            ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('AU')
        );
        $this->assertEquals(
            ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('CA')
        );
        $this->assertEquals(
            ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('FR')
        );
        $this->assertEquals(
            ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('DE')
        );
        $this->assertEquals(
            ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('IT')
        );
        $this->assertEquals(
            ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('JP')
        );
        $this->assertEquals(
            ['MXN'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('MX')
        );
        $this->assertEquals(
            ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('ES')
        );
        $this->assertEquals(
            ['AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'],
            $this->advancedCheckoutEligibility->getSupportedCurrenciesByCountry('UK')
        );
    }

    public function testGetSupportedCardBrandsByCountry()
    {
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'DISCOVER'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('US')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('AU')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('CA')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'CB_NATIONALE'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('FR')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('DE')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('IT')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('JP')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('MX')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('ES')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCountry('UK')
        );
    }

    public function testGetSupportedCardBrandsByCurrency()
    {
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('AUD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('BRL')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('CAD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('CHF')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('CZK')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('DKK')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'CB_NATIONALE'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('EUR')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('GBP')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('HKD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('HUF')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('ILS')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('JPY')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('MXN')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('NOK')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('NZD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('PHP')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('PLN')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('SEK')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('SGD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('THB')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('TWD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'DISCOVER'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByCurrency('USD')
        );
    }

    public function testGetSupportedCardBrands()
    {
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'JCB', 'CB_NATIONALE', 'DISCOVER'],
            $this->advancedCheckoutEligibility->getSupportedCardBrands()
        );
        $this->assertEquals(
            '["MASTERCARD","VISA","AMEX","JCB","CB_NATIONALE","DISCOVER"]',
            json_encode($this->advancedCheckoutEligibility->getSupportedCardBrands())
        );
    }

    public function testIsEligible()
    {
        $this->assertTrue($this->advancedCheckoutEligibility->isEligible('CA', 'JPY'));
        $this->assertFalse($this->advancedCheckoutEligibility->isEligible('MX', 'JPY'));
        $this->assertTrue($this->advancedCheckoutEligibility->isEligible('FR', 'EUR'));
    }

    public function testGetSupportedCardBrandsByContext()
    {
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('US', 'AUD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('US', 'CAD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('US', 'EUR')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('US', 'GBP')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('US', 'JPY')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'DISCOVER'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('US', 'USD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('MX', 'MXN')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'CB_NATIONALE'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('FR', 'EUR')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('FR', 'USD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('AU', 'AUD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('AU', 'EUR')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('CA', 'EUR')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('CA', 'USD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('CA', 'CAD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('JP', 'USD')
        );
        $this->assertEquals(
            ['MASTERCARD', 'VISA', 'AMEX', 'JCB'],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('JP', 'JPY')
        );
        $this->assertEquals(
            [],
            $this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('BO', 'JPY')
        );
        $this->assertEquals(
            '["MASTERCARD","VISA","AMEX","JCB"]',
            json_encode($this->advancedCheckoutEligibility->getSupportedCardBrandsByContext('JP', 'JPY'))
        );
    }
}
