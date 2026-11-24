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
use PsCheckout\Core\Settings\Configuration\PayPalExpressCheckoutConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalPayLaterConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Validator\FrontControllerValidator;

class FrontControllerValidatorTest extends TestCase
{
    /**
     * @param array<string, bool> $values
     */
    private function makeValidator(array $values): FrontControllerValidator
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('getBoolean')->willReturnCallback(static function (string $key) use ($values): bool {
            return $values[$key] ?? false;
        });

        return new FrontControllerValidator(
            $configuration,
            new PayPalPayLaterConfiguration($configuration),
            new PayPalExpressCheckoutConfiguration($configuration)
        );
    }

    /**
     * @return array<string, array{string}>
     */
    public function buttonKeyProvider(): array
    {
        return [
            'Express Checkout, cart page' => [PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE],
            'Express Checkout, checkout page' => [PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE],
            'Express Checkout, product page' => [PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE],
            'Pay Later, cart page' => [PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON],
            'Pay Later, checkout page' => [PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON],
            'Pay Later, product page' => [PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON],
        ];
    }

    /**
     * @dataProvider buttonKeyProvider
     */
    public function testExpressCheckoutIsEnabledWhenGuestCheckoutAndAButtonAreOn(string $buttonKey): void
    {
        $validator = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
            $buttonKey => true,
        ]);

        $this->assertTrue($validator->isExpressCheckoutEnabled());
    }

    /**
     * @dataProvider buttonKeyProvider
     */
    public function testExpressCheckoutIsDisabledWhenGuestCheckoutIsOff(string $buttonKey): void
    {
        $validator = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
            $buttonKey => true,
        ]);

        $this->assertFalse($validator->isExpressCheckoutEnabled());
    }

    public function testExpressCheckoutIsDisabledWhenNoButtonIsOn(): void
    {
        $validator = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
        ]);

        $this->assertFalse($validator->isExpressCheckoutEnabled());
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function buttonAssetsProvider(): array
    {
        return [
            'product page, Express Checkout button' => ['product', PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE],
            'product page, Pay Later button' => ['product', PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON],
            'cart page, Express Checkout button' => ['cart', PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE],
            'cart page, Pay Later button' => ['cart', PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON],
            'authentication page, Express Checkout button' => ['authentication', PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE],
            'authentication page, Pay Later button' => ['authentication', PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON],
        ];
    }

    /**
     * @dataProvider buttonAssetsProvider
     */
    public function testFrontJsIsLoadedForAButtonWhenGuestCheckoutIsOn(string $controller, string $buttonKey): void
    {
        $validator = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
            $buttonKey => true,
        ]);

        $this->assertTrue($validator->shouldLoadFrontJS($controller));
    }

    /**
     * @dataProvider buttonAssetsProvider
     */
    public function testFrontJsIsNotLoadedForAButtonWhenGuestCheckoutIsOff(string $controller, string $buttonKey): void
    {
        $validator = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
            $buttonKey => true,
        ]);

        $this->assertFalse($validator->shouldLoadFrontJS($controller));
    }

    /**
     * @return array<string, array{string}>
     */
    public function authenticationButtonProvider(): array
    {
        return [
            'Express Checkout button' => [PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE],
            'Pay Later button' => [PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON],
        ];
    }

    /**
     * @dataProvider authenticationButtonProvider
     */
    public function testFrontCssOnAuthenticationPageNeedsGuestCheckout(string $buttonKey): void
    {
        $withGuestCheckout = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
            $buttonKey => true,
        ]);
        $withoutGuestCheckout = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
            $buttonKey => true,
        ]);

        $this->assertTrue($withGuestCheckout->shouldLoadFrontCss('authentication'));
        $this->assertFalse($withoutGuestCheckout->shouldLoadFrontCss('authentication'));
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function fundingLogoProvider(): array
    {
        return [
            'product page' => ['product', PayPalConfiguration::PS_CHECKOUT_DISPLAY_LOGO_PRODUCT],
            'cart page' => ['cart', PayPalConfiguration::PS_CHECKOUT_DISPLAY_LOGO_CART],
        ];
    }

    /**
     * @dataProvider fundingLogoProvider
     */
    public function testFrontJsForFundingLogosDoesNotNeedGuestCheckout(string $controller, string $logoKey): void
    {
        $validator = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
            $logoKey => true,
        ]);

        $this->assertTrue($validator->shouldLoadFrontJS($controller));
    }

    public function testFrontJsIsAlwaysLoadedOnTheCheckoutPage(): void
    {
        $validator = $this->makeValidator([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
        ]);

        $this->assertTrue($validator->shouldLoadFrontJS('order'));
    }
}
