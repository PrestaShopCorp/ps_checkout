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
use PsCheckout\Core\FundingSource\Eligibility\Checker\ApplePayEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\BancontactEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\BlikEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\EpsEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\FundingSourceEligibilityCheckerInterface;
use PsCheckout\Core\FundingSource\Eligibility\Checker\GooglePayEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\IdealEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\MybankEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\P24EligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\PayUponInvoiceEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\PaylaterEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\Checker\VenmoEligibilityChecker;
use PsCheckout\Core\FundingSource\Eligibility\FundingSourceEligibilityService;
use PsCheckout\Core\FundingSource\ValueObject\FundingSource;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\CountryResolverInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;

/**
 * @coversDefaultClass \PsCheckout\Core\FundingSource\Eligibility\FundingSourceEligibilityService
 */
class FundingSourceEligibilityServiceTest extends TestCase
{
    /**
     * @var ContextInterface|MockObject
     */
    private $context;

    /**
     * @var ConfigurationInterface|MockObject
     */
    private $configuration;

    /**
     * @var CountryResolverInterface|MockObject
     */
    private $countryResolver;

    /**
     * @var FundingSourcePresenterInterface|MockObject
     */
    private $fundingSourcePresenter;

    /**
     * @var FundingSourceEligibilityService
     */
    private $fundingSourceEligibilityService;

    /**
     * @var array<string, class-string<FundingSourceEligibilityCheckerInterface>>
     */
    private $checkers = [
        'apple_pay' => ApplePayEligibilityChecker::class,
        'bancontact' => BancontactEligibilityChecker::class,
        'blik' => BlikEligibilityChecker::class,
        'eps' => EpsEligibilityChecker::class,
        'google_pay' => GooglePayEligibilityChecker::class,
        'ideal' => IdealEligibilityChecker::class,
        'mybank' => MybankEligibilityChecker::class,
        'p24' => P24EligibilityChecker::class,
        'pay_upon_invoice' => PayUponInvoiceEligibilityChecker::class,
        'paylater' => PaylaterEligibilityChecker::class,
        'venmo' => VenmoEligibilityChecker::class,
    ];

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->fundingSourcePresenter = $this->createMock(FundingSourcePresenterInterface::class);

        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->countryResolver = $this->createMock(CountryResolverInterface::class);

        $this->fundingSourceEligibilityService = new FundingSourceEligibilityService(
            $this->context,
            $this->fundingSourcePresenter,
            array_map(function (string $className) {
                return new $className($this->context, $this->configuration, $this->countryResolver);
            }, array_values($this->checkers))
        );
    }

    /**
     * @return void
     */
    public function testEmptyEligibleFundingSources()
    {
        $shop = $this->createMock(\Shop::class);
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);
        $this->fundingSourcePresenter->method('getAllActiveForSpecificShop')->with(1)->willReturn([]);

        self::assertEmpty($this->fundingSourceEligibilityService->getEligibleFundingSources());
    }

    /**
     * @return void
     */
    public function testDisabledEligibleFundingSources()
    {
        $shop = $this->createMock(\Shop::class);
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);
        $this->fundingSourcePresenter->method('getAllActiveForSpecificShop')->with(1)->willReturn([
            new FundingSource('paypal', 'PayPal', 0, false, null),
            new FundingSource('apple_pay', 'Apple Pay', 0, false, null),
        ]);

        self::assertEmpty($this->fundingSourceEligibilityService->getEligibleFundingSources());
    }

    /**
     * @return void
     */
    public function testUnknownEligibilityChecker()
    {
        $shop = $this->createMock(\Shop::class);
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);
        $this->fundingSourcePresenter->method('getAllActiveForSpecificShop')->with(1)->willReturn([
            new FundingSource('paypal', 'PayPal', 0, true, null),
        ]);

        self::assertArrayHasKey('paypal', $this->fundingSourceEligibilityService->getEligibleFundingSources());
    }

    /**
     * @dataProvider fundingSourcesDataProvider
     *
     * @param string $name
     * @param array{
     *     country: string,
     *     currency: ?string,
     *     intent: string,
     *     configurations: array<int, array{string, bool}>,
     *     amount?: float
     * } $context
     * @param bool $eligible
     *
     * @return void
     */
    public function testEligibleFundingSources($name, $context, $eligible)
    {
        $fundingSources = array_map(function (string $name) {
            return new FundingSource($name, ucfirst($name), 0, true, null);
        }, array_keys($this->checkers));

        $shop = $this->createMock(\Shop::class);
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);
        $this->fundingSourcePresenter->method('getAllActiveForSpecificShop')->with(1)->willReturn($fundingSources);

        $this->configuration->method('get')
            ->willReturnMap([
                [PayPalConfiguration::PS_CHECKOUT_INTENT, $context['intent']],
                [PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE, PayPalConfiguration::MODE_SANDBOX],
            ]);

        if (!empty($context['configurations'])) {
            $this->configuration
                ->method('getBoolean')
                ->willReturnMap($context['configurations']);
        }

        $this->countryResolver->method('getBuyerCountryIsoCode')->willReturn($context['country']);

        $this->context->method('getCurrencyIsoCode')->willReturn($context['currency'] ?: 'EUR');

        $this->context->method('getCartOrderTotal')->willReturn($context['amount'] ?? 100.0);

        $eligibleFundingSources = $this->fundingSourceEligibilityService->getEligibleFundingSources();
        $isEligible = array_key_exists($name, $eligibleFundingSources);

        self::assertSame($eligible, $isEligible);
    }

    /**
     * @return array<string, array{
     *     name: string,
     *     context: array{
     *         country: string,
     *         currency: ?string,
     *         intent: string,
     *         configurations: array<int, array{string, bool}>,
     *         amount?: float
     *     },
     *     eligible: bool
     * }>
     */
    public function fundingSourcesDataProvider()
    {
        return [
            'eligible_apple_pay' => [
                'name' => 'apple_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => true,
            ],
            'eligible_bancontact' => [
                'name' => 'bancontact',
                'context' => [
                    'country' => 'BE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_blik' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'PLN',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_eps' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'AT',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_google_pay' => [
                'name' => 'google_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => true,
            ],
            'eligible_ideal' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'NL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_mybank' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'IT',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_p24' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_paylater' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'FR',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'ineligible_apple_pay_wrong_country' => [
                'name' => 'apple_pay',
                'context' => [
                    'country' => 'ZZ',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => false,
            ],
            'ineligible_apple_pay_wrong_currency' => [
                'name' => 'apple_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'XXX',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => false,
            ],
            'ineligible_apple_pay_wrong_configuration' => [
                'name' => 'apple_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, false],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => false,
            ],
            'eligible_apple_pay_authorization_intent' => [
                'name' => 'apple_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => true,
            ],
            'ineligible_bancontact_wrong_country' => [
                'name' => 'bancontact',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_bancontact_wrong_currency' => [
                'name' => 'bancontact',
                'context' => [
                    'country' => 'BE',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_bancontact_authorization_intent' => [
                'name' => 'bancontact',
                'context' => [
                    'country' => 'BE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_blik_wrong_country' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'PLN',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_blik_wrong_currency' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_blik_authorization_intent' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'PLN',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_eps_wrong_country' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_eps_wrong_currency' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'AT',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_eps_authorization_intent' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'AT',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_google_pay_wrong_country' => [
                'name' => 'google_pay',
                'context' => [
                    'country' => 'ZZ',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => false,
            ],
            'ineligible_google_pay_wrong_currency' => [
                'name' => 'google_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'XXX',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => false,
            ],
            'ineligible_google_pay_wrong_configuration' => [
                'name' => 'google_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, false],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => false,
            ],
            'eligible_google_pay_authorization_intent' => [
                'name' => 'google_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true],
                    ],
                ],
                'eligible' => true,
            ],
            'ineligible_ideal_wrong_country' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_ideal_wrong_currency' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'NL',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_ideal_authorization_intent' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'NL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_mybank_wrong_country' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_mybank_wrong_currency' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'IT',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_mybank_authorization_intent' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'IT',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_p24_wrong_country' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_p24_wrong_currency' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_p24_authorization_intent' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_paylater_wrong_country' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'ZZ',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'eligible_paylater_wrong_currency' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'XXX', // Paylater does not restrict any currencies
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'ineligible_paylater_authorization_intent' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'FR',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'eligible_paylater_de' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'DE',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_paylater_au' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'AU',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_paylater_ca' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'CA',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'eligible_venmo' => [
                'name' => 'venmo',
                'context' => [
                    'country' => 'US',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => true,
            ],
            'ineligible_venmo_wrong_country' => [
                'name' => 'venmo',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_venmo_wrong_currency' => [
                'name' => 'venmo',
                'context' => [
                    'country' => 'US',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'ineligible_venmo_authorize_intent' => [
                'name' => 'venmo',
                'context' => [
                    'country' => 'US',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                ],
                'eligible' => false,
            ],
            'eligible_pay_upon_invoice' => [
                'name' => 'pay_upon_invoice',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                    'amount' => 100.0,
                ],
                'eligible' => true,
            ],
            'ineligible_pay_upon_invoice_wrong_country' => [
                'name' => 'pay_upon_invoice',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                    'amount' => 100.0,
                ],
                'eligible' => false,
            ],
            'ineligible_pay_upon_invoice_wrong_currency' => [
                'name' => 'pay_upon_invoice',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                    'amount' => 100.0,
                ],
                'eligible' => false,
            ],
            'ineligible_pay_upon_invoice_authorize_intent' => [
                'name' => 'pay_upon_invoice',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => [],
                    'amount' => 100.0,
                ],
                'eligible' => false,
            ],
            'ineligible_pay_upon_invoice_below_min' => [
                'name' => 'pay_upon_invoice',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                    'amount' => 3.0,
                ],
                'eligible' => false,
            ],
            'ineligible_pay_upon_invoice_above_max' => [
                'name' => 'pay_upon_invoice',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                    'amount' => 3000.0,
                ],
                'eligible' => false,
            ],
            'ineligible_p24_pln_above_max' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'PLN',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => [],
                    'amount' => 60000.0,
                ],
                'eligible' => false,
            ],
        ];
    }
}
