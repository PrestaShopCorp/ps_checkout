<?php

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
use PsCheckout\Core\FundingSource\Eligibility\Checker\PaylaterEligibilityChecker;
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
     * @var ContextInterface&MockObject
     */
    private $context;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var CountryResolverInterface&MockObject
     */
    private $countryResolver;

    /**
     * @var FundingSourcePresenterInterface&MockObject
     */
    private $fundingSourcePresenter;

    /**
     * @var FundingSourceEligibilityService
     */
    private $fundingSourceEligibilityService;

    /**
     * @var array<string, FundingSourceEligibilityCheckerInterface>
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
        'paylater' => PaylaterEligibilityChecker::class,
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

    public function testEmptyEligibleFundingSources()
    {
        $shop = $this->createMock(\Shop::class);
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);
        $this->fundingSourcePresenter->method('getAllActiveForSpecificShop')->with(1)->willReturn([]);

        self::assertEmpty($this->fundingSourceEligibilityService->getEligibleFundingSources());
    }

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
     *     configurations: array<int, array{string, bool}>
     * } $context
     * @param bool $eligible
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
     *         configurations: array<int, array{string, bool}>
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => true
            ],
            'eligible_bancontact' => [
                'name' => 'bancontact',
                'context' => [
                    'country' => 'BE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_blik' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'PLN',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_eps' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'AT',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => true
            ],
            'eligible_ideal' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'NL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_mybank' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'IT',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_p24' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_paylater' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'FR',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => false
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => false
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => false
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => true
            ],
            'ineligible_bancontact_wrong_country' => [
                'name' => 'bancontact',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_bancontact_wrong_currency' => [
                'name' => 'bancontact',
                'context' => [
                    'country' => 'BE',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_bancontact_authorization_intent' => [
                'name' => 'bancontact',
                'context' => [
                    'country' => 'BE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_blik_wrong_country' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'PLN',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_blik_wrong_currency' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_blik_authorization_intent' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'PLN',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_eps_wrong_country' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_eps_wrong_currency' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'AT',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_eps_authorization_intent' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'AT',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => []
                ],
                'eligible' => false
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => false
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => false
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => false
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
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => true
            ],
            'ineligible_ideal_wrong_country' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_ideal_wrong_currency' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'NL',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_ideal_authorization_intent' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'NL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_mybank_wrong_country' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_mybank_wrong_currency' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'IT',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_mybank_authorization_intent' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'IT',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_p24_wrong_country' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'DE',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_p24_wrong_currency' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'USD',
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_p24_authorization_intent' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'EUR',
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'ineligible_paylater_wrong_country' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'ZZ',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => false
            ],
            'eligible_paylater_wrong_currency' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'XXX', // Paylater does not restrict any currencies
                    'intent' => PayPalOrderIntent::CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'ineligible_paylater_authorization_intent' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'FR',
                    'currency' => null,
                    'intent' => PayPalOrderIntent::AUTHORIZE,
                    'configurations' => []
                ],
                'eligible' => false
            ]
        ];
    }
}
