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
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalIntentConfiguration;
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

        self::assertEmpty($this->fundingSourceEligibilityService->getEligibleFundingSource());
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

        $eligibleFundingSources = $this->fundingSourceEligibilityService->getEligibleFundingSource();
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
                    'intent' => PayPalIntentConfiguration::PS_CHECKOUT_CAPTURE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => true
            ],
            'eligible_google_pay' => [
                'name' => 'google_pay',
                'context' => [
                    'country' => 'FR',
                    'currency' => 'EUR',
                    'intent' => PayPalIntentConfiguration::PS_CHECKOUT_CAPTURE,
                    'configurations' => [
                        [PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_APPLE_PAY, true],
                        [PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX, true]
                    ]
                ],
                'eligible' => true
            ],
            'eligible_blik' => [
                'name' => 'blik',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'PLN',
                    'intent' => PayPalIntentConfiguration::PS_CHECKOUT_CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_eps' => [
                'name' => 'eps',
                'context' => [
                    'country' => 'AT',
                    'currency' => 'EUR',
                    'intent' => PayPalIntentConfiguration::PS_CHECKOUT_CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_ideal' => [
                'name' => 'ideal',
                'context' => [
                    'country' => 'NL',
                    'currency' => 'EUR',
                    'intent' => PayPalIntentConfiguration::PS_CHECKOUT_CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_mybank' => [
                'name' => 'mybank',
                'context' => [
                    'country' => 'IT',
                    'currency' => 'EUR',
                    'intent' => PayPalIntentConfiguration::PS_CHECKOUT_CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_p24' => [
                'name' => 'p24',
                'context' => [
                    'country' => 'PL',
                    'currency' => 'EUR',
                    'intent' => PayPalIntentConfiguration::PS_CHECKOUT_CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ],
            'eligible_paylater' => [
                'name' => 'paylater',
                'context' => [
                    'country' => 'FR',
                    'currency' => null,
                    'intent' => PayPalIntentConfiguration::PS_CHECKOUT_CAPTURE,
                    'configurations' => []
                ],
                'eligible' => true
            ]
        ];
    }
}