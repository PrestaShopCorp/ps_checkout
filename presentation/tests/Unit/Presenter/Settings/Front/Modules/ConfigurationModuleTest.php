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

namespace Tests\Unit\PsCheckout\Modules;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalExpressCheckoutConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalPayLaterConfiguration;
use PsCheckout\Core\Settings\Configuration\PayPalSdkConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Validator\PayLaterValidatorInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;
use PsCheckout\Presentation\Presenter\Settings\Front\Modules\ConfigurationModule;

class ConfigurationModuleTest extends TestCase
{
    const BUTTON_FLAGS = [
        'ps_checkoutExpressCheckoutCartEnabled' => PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE,
        'ps_checkoutExpressCheckoutOrderEnabled' => PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE,
        'ps_checkoutExpressCheckoutProductEnabled' => PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE,
        'ps_checkoutPayLaterOrderPageButtonEnabled' => PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BUTTON,
        'ps_checkoutPayLaterCartPageButtonEnabled' => PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON,
        'ps_checkoutPayLaterProductPageButtonEnabled' => PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON,
    ];

    /**
     * @param array<string, bool> $values
     *
     * @return array<mixed>
     */
    private function present(array $values): array
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('getBoolean')->willReturnCallback(static function (string $key) use ($values): bool {
            return $values[$key] ?? false;
        });

        $shop = new \stdClass();
        $shop->id = 1;

        $context = $this->createMock(ContextInterface::class);
        $context->method('getShop')->willReturn($shop);

        $fundingSourcePresenter = $this->createMock(FundingSourcePresenterInterface::class);
        $fundingSourcePresenter->method('getAllActiveForSpecificShop')->willReturn([]);

        $payPalSdkConfiguration = $this->createMock(PayPalSdkConfiguration::class);
        $payPalSdkConfiguration->method('buildConfiguration')->willReturn([]);

        $module = new ConfigurationModule(
            'ps_checkout',
            $context,
            $configuration,
            $this->createMock(PayPalConfiguration::class),
            $fundingSourcePresenter,
            $payPalSdkConfiguration,
            new PayPalPayLaterConfiguration($configuration),
            $this->createMock(PayLaterValidatorInterface::class),
            new PayPalExpressCheckoutConfiguration($configuration)
        );

        return $module->present();
    }

    /**
     * @return array<string, bool>
     */
    private function everyButtonOn(): array
    {
        return array_fill_keys(array_values(self::BUTTON_FLAGS), true);
    }

    public function testEveryButtonFlagIsFalseWhenGuestCheckoutIsOff(): void
    {
        $result = $this->present(array_merge($this->everyButtonOn(), [
            PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS => true,
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => false,
        ]));

        foreach (array_keys(self::BUTTON_FLAGS) as $flag) {
            $this->assertFalse($result[$flag], $flag);
        }
    }

    public function testEveryButtonFlagIsTrueWhenGuestCheckoutIsOn(): void
    {
        $result = $this->present(array_merge($this->everyButtonOn(), [
            PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS => true,
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
        ]));

        foreach (array_keys(self::BUTTON_FLAGS) as $flag) {
            $this->assertTrue($result[$flag], $flag);
        }
    }

    /**
     * @return array<string, array{string, string}>
     */
    public function buttonFlagProvider(): array
    {
        $cases = [];

        foreach (self::BUTTON_FLAGS as $flag => $buttonKey) {
            $cases[$flag] = [$flag, $buttonKey];
        }

        return $cases;
    }

    /**
     * @dataProvider buttonFlagProvider
     */
    public function testEachButtonFlagFollowsItsOwnKeyWhenGuestCheckoutIsOn(string $flag, string $buttonKey): void
    {
        $result = $this->present([
            PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS => true,
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
            $buttonKey => true,
        ]);

        foreach (array_keys(self::BUTTON_FLAGS) as $otherFlag) {
            $this->assertSame($otherFlag === $flag, $result[$otherFlag], $otherFlag);
        }
    }

    public function testEveryButtonFlagIsFalseWhenPayPalPaymentsAreNotReceivable(): void
    {
        $result = $this->present(array_merge($this->everyButtonOn(), [
            PayPalConfiguration::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS => false,
            PayPalExpressCheckoutConfiguration::PS_GUEST_CHECKOUT_ENABLED => true,
        ]));

        foreach (array_keys(self::BUTTON_FLAGS) as $flag) {
            $this->assertFalse($result[$flag], $flag);
        }
    }
}
