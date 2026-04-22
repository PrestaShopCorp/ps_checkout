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

namespace Tests\Unit\PsCheckout\Infrastructure\Validator;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\Context;
use PsCheckout\Infrastructure\Validator\PayLaterValidator;

class PayLaterValidatorTest extends TestCase
{
    private $contextMock;

    private $payPalConfigurationMock;

    private $payLaterValidator;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(Context::class);
        $this->payPalConfigurationMock = $this->createMock(PayPalConfiguration::class);
        $this->payLaterValidator = new PayLaterValidator($this->contextMock, $this->payPalConfigurationMock);
    }

    /**
     * @dataProvider validPayLaterScenariosProvider
     */
    public function testIsPayLaterAvailableReturnsTrueForValidScenarios(
        string $merchantCountry,
        string $customerCountry,
        string $currency,
        string $locale
    ): void {
        $this->payPalConfigurationMock
            ->method('getMerchantCountry')
            ->willReturn($merchantCountry);

        $currencyMock = $this->createMock(\stdClass::class);
        $currencyMock->iso_code = $currency;

        $languageMock = $this->createMock(\stdClass::class);
        $languageMock->locale = $locale;

        $countryMock = $this->createMock(\stdClass::class);
        $countryMock->iso_code = $customerCountry;

        $this->contextMock
            ->method('getCurrency')
            ->willReturn($currencyMock);

        $this->contextMock
            ->method('getLanguage')
            ->willReturn($languageMock);

        $this->contextMock
            ->method('getCountry')
            ->willReturn($countryMock);

        $this->assertTrue($this->payLaterValidator->isPayLaterAvailable());
    }

    /**
     * @dataProvider invalidPayLaterScenariosProvider
     */
    public function testIsPayLaterAvailableReturnsFalseForInvalidScenarios(
        string $merchantCountry,
        string $customerCountry,
        string $currency,
        string $locale
    ): void {
        $this->payPalConfigurationMock
            ->method('getMerchantCountry')
            ->willReturn($merchantCountry);

        $currencyMock = $this->createMock(\stdClass::class);
        $currencyMock->iso_code = $currency;

        $languageMock = $this->createMock(\stdClass::class);
        $languageMock->locale = $locale;

        $countryMock = $this->createMock(\stdClass::class);
        $countryMock->iso_code = $customerCountry;

        $this->contextMock
            ->method('getCurrency')
            ->willReturn($currencyMock);

        $this->contextMock
            ->method('getLanguage')
            ->willReturn($languageMock);

        $this->contextMock
            ->method('getCountry')
            ->willReturn($countryMock);

        $this->assertFalse($this->payLaterValidator->isPayLaterAvailable());
    }

    public function testIsPayLaterAvailableReturnsFalseForUnsupportedMerchantCountry(): void
    {
        $this->payPalConfigurationMock
            ->method('getMerchantCountry')
            ->willReturn('JP'); // Japan is not supported

        $currencyMock = $this->createMock(\stdClass::class);
        $currencyMock->iso_code = 'JPY';

        $languageMock = $this->createMock(\stdClass::class);
        $languageMock->locale = 'ja-JP';

        $countryMock = $this->createMock(\stdClass::class);
        $countryMock->iso_code = 'JP';

        $this->contextMock
            ->method('getCurrency')
            ->willReturn($currencyMock);

        $this->contextMock
            ->method('getLanguage')
            ->willReturn($languageMock);

        $this->contextMock
            ->method('getCountry')
            ->willReturn($countryMock);

        $this->assertFalse($this->payLaterValidator->isPayLaterAvailable());
    }

    public function testIsPayLaterAvailableReturnsTrueForCanadaFrenchLocale(): void
    {
        $this->payPalConfigurationMock
            ->method('getMerchantCountry')
            ->willReturn('CA');

        $currencyMock = $this->createMock(\stdClass::class);
        $currencyMock->iso_code = 'CAD';

        $languageMock = $this->createMock(\stdClass::class);
        $languageMock->locale = 'fr-CA';

        $countryMock = $this->createMock(\stdClass::class);
        $countryMock->iso_code = 'CA';

        $this->contextMock->method('getCurrency')->willReturn($currencyMock);
        $this->contextMock->method('getLanguage')->willReturn($languageMock);
        $this->contextMock->method('getCountry')->willReturn($countryMock);

        $this->assertTrue($this->payLaterValidator->isPayLaterAvailable());
    }

    public function testIsPayLaterAvailableReturnsTrueForCanadaEnglishLocale(): void
    {
        $this->payPalConfigurationMock
            ->method('getMerchantCountry')
            ->willReturn('CA');

        $currencyMock = $this->createMock(\stdClass::class);
        $currencyMock->iso_code = 'CAD';

        $languageMock = $this->createMock(\stdClass::class);
        $languageMock->locale = 'en-CA';

        $countryMock = $this->createMock(\stdClass::class);
        $countryMock->iso_code = 'CA';

        $this->contextMock->method('getCurrency')->willReturn($currencyMock);
        $this->contextMock->method('getLanguage')->willReturn($languageMock);
        $this->contextMock->method('getCountry')->willReturn($countryMock);

        $this->assertTrue($this->payLaterValidator->isPayLaterAvailable());
    }

    public function testIsPayLaterAvailableReturnsFalseForMismatchedMerchantAndCustomerCountry(): void
    {
        $this->payPalConfigurationMock
            ->method('getMerchantCountry')
            ->willReturn('US'); // US merchant

        $currencyMock = $this->createMock(\stdClass::class);
        $currencyMock->iso_code = 'USD';

        $languageMock = $this->createMock(\stdClass::class);
        $languageMock->locale = 'en-US';

        $countryMock = $this->createMock(\stdClass::class);
        $countryMock->iso_code = 'CA'; // Canadian customer

        $this->contextMock
            ->method('getCurrency')
            ->willReturn($currencyMock);

        $this->contextMock
            ->method('getLanguage')
            ->willReturn($languageMock);

        $this->contextMock
            ->method('getCountry')
            ->willReturn($countryMock);

        $this->assertFalse($this->payLaterValidator->isPayLaterAvailable());
    }

    public function testIsPayLaterAvailableReturnsFalseForWrongCurrency(): void
    {
        $this->payPalConfigurationMock
            ->method('getMerchantCountry')
            ->willReturn('US');

        $currencyMock = $this->createMock(\stdClass::class);
        $currencyMock->iso_code = 'EUR'; // Wrong currency for US

        $languageMock = $this->createMock(\stdClass::class);
        $languageMock->locale = 'en-US';

        $countryMock = $this->createMock(\stdClass::class);
        $countryMock->iso_code = 'US';

        $this->contextMock
            ->method('getCurrency')
            ->willReturn($currencyMock);

        $this->contextMock
            ->method('getLanguage')
            ->willReturn($languageMock);

        $this->contextMock
            ->method('getCountry')
            ->willReturn($countryMock);

        $this->assertFalse($this->payLaterValidator->isPayLaterAvailable());
    }

    public function testIsPayLaterAvailableReturnsFalseForUnsupportedLocale(): void
    {
        $this->payPalConfigurationMock
            ->method('getMerchantCountry')
            ->willReturn('US');

        $currencyMock = $this->createMock(\stdClass::class);
        $currencyMock->iso_code = 'USD';

        $languageMock = $this->createMock(\stdClass::class);
        $languageMock->locale = 'fr-FR'; // French locale for US merchant

        $countryMock = $this->createMock(\stdClass::class);
        $countryMock->iso_code = 'US';

        $this->contextMock
            ->method('getCurrency')
            ->willReturn($currencyMock);

        $this->contextMock
            ->method('getLanguage')
            ->willReturn($languageMock);

        $this->contextMock
            ->method('getCountry')
            ->willReturn($countryMock);

        $this->assertFalse($this->payLaterValidator->isPayLaterAvailable());
    }

    public function validPayLaterScenariosProvider(): array
    {
        return [
            'Australia scenario' => ['AU', 'AU', 'AUD', 'en-AU'],
            'France scenario' => ['FR', 'FR', 'EUR', 'fr-FR'],
            'Germany scenario' => ['DE', 'DE', 'EUR', 'de-DE'],
            'Italy scenario' => ['IT', 'IT', 'EUR', 'it-IT'],
            'Spain scenario' => ['ES', 'ES', 'EUR', 'es-ES'],
            'United Kingdom scenario' => ['GB', 'GB', 'GBP', 'en-GB'],
            'United States scenario' => ['US', 'US', 'USD', 'en-US'],
            'Canada scenario FR' => ['CA', 'CA', 'CAD', 'fr-CA'],
            'Canada scenario EN' => ['CA', 'CA', 'CAD', 'en-CA'],
        ];
    }

    public function invalidPayLaterScenariosProvider(): array
    {
        return [
            'Wrong currency for Australia' => ['AU', 'AU', 'USD', 'en-AU'],
            'Wrong currency for France' => ['FR', 'FR', 'USD', 'fr-FR'],
            'Wrong currency for Germany' => ['DE', 'DE', 'GBP', 'de-DE'],
            'Wrong currency for Italy' => ['IT', 'IT', 'AUD', 'it-IT'],
            'Wrong currency for Spain' => ['ES', 'ES', 'GBP', 'es-ES'],
            'Wrong currency for UK' => ['GB', 'GB', 'EUR', 'en-GB'],
            'Wrong currency for US' => ['US', 'US', 'EUR', 'en-US'],
            'Wrong locale for Australia' => ['AU', 'AU', 'AUD', 'fr-FR'],
            'Wrong locale for France' => ['FR', 'FR', 'EUR', 'en-US'],
            'Wrong locale for Germany' => ['DE', 'DE', 'EUR', 'it-IT'],
            'Wrong locale for Italy' => ['IT', 'IT', 'EUR', 'es-ES'],
            'Wrong locale for Spain' => ['ES', 'ES', 'EUR', 'de-DE'],
            'Wrong locale for UK' => ['GB', 'GB', 'GBP', 'fr-FR'],
            'Wrong locale for US' => ['US', 'US', 'USD', 'de-DE'],
            'Unsupported customer country' => ['US', 'CA', 'USD', 'en-US'],
            'Wrong currency for Canada (USD)' => ['CA', 'CA', 'USD', 'fr-CA'],
            'Wrong currency for Canada (EUR)' => ['CA', 'CA', 'EUR', 'en-CA'],
            'Wrong locale for Canada (de-DE)' => ['CA', 'CA', 'CAD', 'de-DE'],
            'Wrong locale for Canada (fr-FR)' => ['CA', 'CA', 'CAD', 'fr-FR'],
            'Wrong locale for Canada (en-US)' => ['CA', 'CA', 'CAD', 'en-US'],
            'Canada merchant with non-Canada customer' => ['CA', 'FR', 'CAD', 'fr-CA'],
        ];
    }
}
