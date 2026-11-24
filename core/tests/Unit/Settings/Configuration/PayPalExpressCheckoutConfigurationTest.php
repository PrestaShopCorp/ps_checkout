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
use PsCheckout\Core\Settings\Configuration\PayPalExpressCheckoutConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalPayLaterConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class PayPalExpressCheckoutConfigurationTest extends TestCase
{
    /**
     * @param array<string, bool> $values
     */
    private function makeConfiguration(array $values): PayPalExpressCheckoutConfiguration
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('getBoolean')->willReturnCallback(static function (string $key) use ($values): bool {
            return $values[$key] ?? false;
        });

        return new PayPalExpressCheckoutConfiguration($configuration);
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
    public function testButtonIsEnabledWhenGuestCheckoutAndItsKeyAreOn(string $buttonKey): void
    {
        $configuration = $this->makeConfiguration([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
            $buttonKey => true,
        ]);

        $this->assertTrue($configuration->isButtonEnabled($buttonKey));
        $this->assertTrue($configuration->isAnyButtonEnabled());
    }

    /**
     * @dataProvider buttonKeyProvider
     */
    public function testButtonIsDisabledWhenGuestCheckoutIsOff(string $buttonKey): void
    {
        $configuration = $this->makeConfiguration([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
            $buttonKey => true,
        ]);

        $this->assertFalse($configuration->isButtonEnabled($buttonKey));
        $this->assertFalse($configuration->isAnyButtonEnabled());
    }

    /**
     * @dataProvider buttonKeyProvider
     */
    public function testButtonFollowsItsOwnKeyWhenGuestCheckoutIsOn(string $buttonKey): void
    {
        $otherButtons = [];

        foreach (PayPalExpressCheckoutConfiguration::BUTTON_KEYS as $key) {
            $otherButtons[$key] = $key !== $buttonKey;
        }

        $configuration = $this->makeConfiguration(array_merge($otherButtons, [
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
        ]));

        $this->assertFalse($configuration->isButtonEnabled($buttonKey));
        $this->assertTrue($configuration->isAnyButtonEnabled());
    }

    public function testNoButtonIsEnabledWhenEveryKeyIsOff(): void
    {
        $configuration = $this->makeConfiguration([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
        ]);

        $this->assertFalse($configuration->isAnyButtonEnabled());
    }

    public function testAKeyThatIsNotAButtonIsNeverEnabled(): void
    {
        $configuration = $this->makeConfiguration([
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
            'PS_CHECKOUT_DISPLAY_LOGO_CART' => true,
        ]);

        $this->assertFalse($configuration->isButtonEnabled('PS_CHECKOUT_DISPLAY_LOGO_CART'));
    }
}
