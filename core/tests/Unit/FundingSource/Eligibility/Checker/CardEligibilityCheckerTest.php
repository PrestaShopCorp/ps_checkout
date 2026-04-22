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

namespace FundingSource\Eligibility\Checker;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\FundingSource\Eligibility\Checker\CardEligibilityChecker;
use PsCheckout\Core\FundingSource\ValueObject\FundingSource;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\CountryResolverInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \PsCheckout\Core\FundingSource\Eligibility\Checker\CardEligibilityChecker
 */
class CardEligibilityCheckerTest extends TestCase
{
    /** @var ContextInterface|MockObject */
    private $context;

    /** @var ConfigurationInterface|MockObject */
    private $configuration;

    /** @var CountryResolverInterface|MockObject */
    private $countryResolver;

    /** @var CardEligibilityChecker */
    private $checker;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->countryResolver = $this->createMock(CountryResolverInterface::class);

        $this->checker = new CardEligibilityChecker(
            $this->context,
            $this->configuration,
            $this->countryResolver,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function testSupportsCard(): void
    {
        self::assertTrue($this->checker->supports(new FundingSource('card', 'Card', 0, true, null)));
    }

    /**
     * @dataProvider otherFundingSourcesProvider
     */
    public function testDoesNotSupportOtherFundingSources(string $name): void
    {
        self::assertFalse($this->checker->supports(new FundingSource($name, $name, 0, true, null)));
    }

    /**
     * @return array<string, array{string}>
     */
    public function otherFundingSourcesProvider(): array
    {
        return [
            'paypal' => ['paypal'],
            'apple_pay' => ['apple_pay'],
            'google_pay' => ['google_pay'],
            'paylater' => ['paylater'],
            'venmo' => ['venmo'],
            'bancontact' => ['bancontact'],
        ];
    }

    /**
     * @dataProvider supportedCurrenciesProvider
     */
    public function testIsEligibleWithSupportedCurrencyAndCaptureIntent(string $currency): void
    {
        $this->configuration->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_INTENT)
            ->willReturn(PayPalOrderIntent::CAPTURE);
        $this->context->method('getCurrencyIsoCode')->willReturn($currency);
        $this->context->method('getCartOrderTotal')->willReturn(null);

        self::assertTrue($this->checker->isEligible(new FundingSource('card', 'Card', 0, true, null)));
    }

    /**
     * @dataProvider supportedCurrenciesProvider
     */
    public function testIsEligibleWithSupportedCurrencyAndAuthorizeIntent(string $currency): void
    {
        $this->configuration->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_INTENT)
            ->willReturn(PayPalOrderIntent::AUTHORIZE);
        $this->context->method('getCurrencyIsoCode')->willReturn($currency);
        $this->context->method('getCartOrderTotal')->willReturn(null);

        self::assertTrue($this->checker->isEligible(new FundingSource('card', 'Card', 0, true, null)));
    }

    /**
     * @return array<string, array{string}>
     */
    public function supportedCurrenciesProvider(): array
    {
        return [
            'AUD' => ['AUD'],
            'BRL' => ['BRL'],
            'CAD' => ['CAD'],
            'CNY' => ['CNY'],
            'CZK' => ['CZK'],
            'DKK' => ['DKK'],
            'EUR' => ['EUR'],
            'GBP' => ['GBP'],
            'HKD' => ['HKD'],
            'HUF' => ['HUF'],
            'ILS' => ['ILS'],
            'JPY' => ['JPY'],
            'MXN' => ['MXN'],
            'MYR' => ['MYR'],
            'NOK' => ['NOK'],
            'NZD' => ['NZD'],
            'PHP' => ['PHP'],
            'PLN' => ['PLN'],
            'RUB' => ['RUB'],
            'SEK' => ['SEK'],
            'SGD' => ['SGD'],
            'THB' => ['THB'],
            'TWD' => ['TWD'],
            'USD' => ['USD'],
            'CHF' => ['CHF'],
        ];
    }

    public function testIsNotEligibleWithUnsupportedCurrency(): void
    {
        $this->configuration->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_INTENT)
            ->willReturn(PayPalOrderIntent::CAPTURE);
        $this->context->method('getCurrencyIsoCode')->willReturn('XXX');
        $this->context->method('getCartOrderTotal')->willReturn(null);

        self::assertFalse($this->checker->isEligible(new FundingSource('card', 'Card', 0, true, null)));
    }

    public function testGetMinAmountReturnsNull(): void
    {
        self::assertNull($this->checker->getMinAmount('EUR'));
        self::assertNull($this->checker->getMinAmount('USD'));
        self::assertNull($this->checker->getMinAmount('CAD'));
    }

    public function testGetMaxAmountReturnsNull(): void
    {
        self::assertNull($this->checker->getMaxAmount('EUR'));
        self::assertNull($this->checker->getMaxAmount('USD'));
        self::assertNull($this->checker->getMaxAmount('CAD'));
    }
}
