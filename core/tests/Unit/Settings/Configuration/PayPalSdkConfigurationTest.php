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

namespace Tests\Unit\PsCheckout\Core\Settings\Configuration;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\FundingSource\Eligibility\FundingSourceEligibilityServiceInterface;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\OAuth\OAuthServiceInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalExpressCheckoutConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalPayLaterConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalSdkConfiguration;
use PsCheckout\Core\Util\CountryResolverInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Environment\EnvInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;
use Psr\Log\LoggerInterface;

class PayPalSdkConfigurationTest extends TestCase
{
    /**
     * @param array<string, bool> $values
     *
     * @return string[]
     */
    private function buildComponents(string $phpSelf, array $values): array
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('getBoolean')->willReturnCallback(static function (string $key) use ($values): bool {
            return $values[$key] ?? false;
        });

        $controller = new \stdClass();
        $controller->php_self = $phpSelf;

        $shop = new \stdClass();
        $shop->id = 1;

        $context = $this->createMock(ContextInterface::class);
        $context->method('getController')->willReturn($controller);
        $context->method('getShop')->willReturn($shop);
        $context->method('getCurrencyIsoCode')->willReturn('EUR');

        $fundingSourcePresenter = $this->createMock(FundingSourcePresenterInterface::class);
        $fundingSourcePresenter->method('getAllForSpecificShop')->willReturn([]);

        $eligibilityService = $this->createMock(FundingSourceEligibilityServiceInterface::class);
        $eligibilityService->method('getEligibleFundingSources')->willReturn([]);

        $sdkConfiguration = new PayPalSdkConfiguration(
            $context,
            $configuration,
            $this->createMock(PayPalConfiguration::class),
            $this->createMock(EnvInterface::class),
            $fundingSourcePresenter,
            $eligibilityService,
            $this->createMock(CountryResolverInterface::class),
            $this->createMock(PayPalCustomerRepositoryInterface::class),
            $this->createMock(OAuthServiceInterface::class),
            $this->createMock(LoggerInterface::class),
            new PayPalPayLaterConfiguration($configuration),
            new PayPalExpressCheckoutConfiguration($configuration)
        );

        $components = $sdkConfiguration->buildConfiguration()['components'];
        $this->assertIsString($components);

        return explode(',', $components);
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function buttonOnPageProvider(): array
    {
        return [
            'cart page, Express Checkout cart button' => ['cart', PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE],
            'cart page, Express Checkout checkout button' => ['cart', PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE],
            'cart page, Pay Later button' => ['cart', PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON],
            'product page, Express Checkout button' => ['product', PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE],
            'product page, Pay Later button' => ['product', PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON],
        ];
    }

    /**
     * @dataProvider buttonOnPageProvider
     */
    public function testButtonsComponentIsLoadedWhenGuestCheckoutAndTheButtonAreOn(string $phpSelf, string $buttonKey): void
    {
        $components = $this->buildComponents($phpSelf, [
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
            $buttonKey => true,
        ]);

        $this->assertContains('buttons', $components);
    }

    /**
     * @dataProvider buttonOnPageProvider
     */
    public function testButtonsComponentIsNotLoadedWhenGuestCheckoutIsOff(string $phpSelf, string $buttonKey): void
    {
        $components = $this->buildComponents($phpSelf, [
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
            $buttonKey => true,
        ]);

        $this->assertNotContains('buttons', $components);
    }

    public function testButtonsComponentIsAlwaysLoadedOnTheCheckoutPage(): void
    {
        $components = $this->buildComponents('order', [
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
        ]);

        $this->assertContains('buttons', $components);
    }
}
