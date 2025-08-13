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

namespace Tests\Unit\PsCheckout;

use PHPUnit\Framework\TestCase;
use PsCheckout\Infrastructure\Adapter\Context;
use PsCheckout\Presentation\Presenter\Settings\Front\SupportedCardBrandsPresenter;

class SupportedCardBrandsPresenterTest extends TestCase
{
    private $supportedCardBrandsPresenter;

    private $context;

    public function setUp(): void
    {
        $this->context = $this->createMock(Context::class);
        $this->supportedCardBrandsPresenter = new SupportedCardBrandsPresenter($this->context);
    }

    /**
     * @dataProvider countryAndCurrencyProvider
     */
    public function testPresent($countryIso, $currencyIso, $expectedResult)
    {
        // Mock the context to return the provided country and currency ISO codes
        $countryMock = $this->createMock(\Country::class);
        $countryMock->iso_code = $countryIso;
        $countryMock->id = 1; // Mocking that the country has a valid ID

        $currencyMock = $this->createMock(\Currency::class);
        $currencyMock->iso_code = $currencyIso;
        $currencyMock->id = 1; // Mocking that the currency has a valid ID

        $this->context->method('getCountry')->willReturn($countryMock);
        $this->context->method('getCurrency')->willReturn($currencyMock);

        $result = $this->supportedCardBrandsPresenter->present();

        $this->assertEquals($expectedResult, $result);
    }

    public function testPresentWithoutCountry()
    {
        $this->context->method('getCountry')->willReturn(null);
        $this->context->method('getCurrency')->willReturn(null);

        $result = $this->supportedCardBrandsPresenter->present();

        $this->assertEquals([], $result);
    }

    /**
     * Data provider for country and currency combinations
     */
    public function countryAndCurrencyProvider(): array
    {
        return [
            'Test 1' => ['US', 'USD', ['MASTERCARD', 'VISA', 'AMEX', 'DISCOVER']],
            'Test 2' => ['AU', 'AUD', ['MASTERCARD', 'VISA', 'AMEX']],
            'Test 3' => ['CA', 'CAD', ['MASTERCARD', 'VISA', 'AMEX', 'JCB']],
            'Test 4' => ['FR', 'EUR', ['MASTERCARD', 'VISA', 'AMEX', 'CB_NATIONALE']],
            'Test 5' => ['DE', 'EUR', ['MASTERCARD', 'VISA', 'AMEX']],
            'Test 6' => ['IT', 'EUR', ['MASTERCARD', 'VISA', 'AMEX']],
            'Test 7' => ['JP', 'JPY', ['MASTERCARD', 'VISA', 'AMEX', 'JCB']],
            'Test 8' => ['MX', 'MXN', ['MASTERCARD', 'VISA', 'AMEX']],
            'Test 9' => ['ES', 'EUR', ['MASTERCARD', 'VISA', 'AMEX']],
            'Test 10' => ['GB', 'GBP', ['MASTERCARD', 'VISA', 'AMEX']],
        ];
    }
}
