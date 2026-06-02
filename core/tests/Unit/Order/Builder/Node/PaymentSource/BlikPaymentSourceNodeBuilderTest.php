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

namespace Tests\Unit\PsCheckout\Core\Order\Builder\Node\PaymentSource;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\BlikPaymentSourceNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\Validate;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;

class BlikPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(): BlikPaymentSourceNodeBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn('My Shop');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('PL');

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturnCallback(
            static function (string $email): bool {
                return (bool) preg_match(Validate::PAYPAL_EMAIL_PATTERN, $email);
            }
        );

        return new BlikPaymentSourceNodeBuilder($configuration, $link, $countryRepository, $validate);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $email = 'anna@example.com'): array
    {
        $address = new \stdClass();
        $address->firstname = 'Anna';
        $address->lastname = 'Nowak';
        $address->id_country = 1;

        $customer = new \stdClass();
        $customer->email = $email;

        return [
            'addresses' => ['invoice' => $address],
            'customer' => $customer,
        ];
    }

    /**
     * @param array<string, mixed> $cart
     */
    private function makeContext(array $cart): CheckoutContext
    {
        return new CheckoutContext($cart, 'blik', false, null, null, false, false);
    }

    public function testSupportsBlik(): void
    {
        $builder = $this->makeBuilder();

        $this->assertTrue($builder->supports('blik'));
        $this->assertFalse($builder->supports('ideal'));
    }

    public function testBuildIncludesEmailWhenPresent(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart()));

        $this->assertSame([
            'payment_source' => [
                'blik' => [
                    'name' => 'Anna Nowak',
                    'country_code' => 'PL',
                    'experience_context' => [
                        'brand_name' => 'My Shop',
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                    'email' => 'anna@example.com',
                ],
            ],
        ], $result);
    }

    public function testBuildOmitsEmailWhenCustomerEmailIsEmpty(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart('')));

        $this->assertArrayNotHasKey('email', $result['payment_source']['blik']);
    }

    public function testBuildOmitsEmailWhenCustomerIsAbsent(): void
    {
        $address = new \stdClass();
        $address->firstname = 'Anna';
        $address->lastname = 'Nowak';
        $address->id_country = 1;

        $result = $this->makeBuilder()->build($this->makeContext(['addresses' => ['invoice' => $address]]));

        $this->assertArrayNotHasKey('email', $result['payment_source']['blik']);
    }

    public function testBuildOmitsEmailWhenEmailHasNoTld(): void
    {
        // Regression: emails without a TLD were silently forwarded to PayPal, causing INVALID_PARAMETER_SYNTAX.
        $result = $this->makeBuilder()->setCart($this->makeCart('einkauf@my-shop'))->build();

        $this->assertArrayNotHasKey('email', $result['payment_source']['blik']);
    }

    public function testBuildReturnsCorrectNameAndCountryCode(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart()));

        $this->assertSame('Anna Nowak', $result['payment_source']['blik']['name']);
        $this->assertSame('PL', $result['payment_source']['blik']['country_code']);
    }

    public function testBrandNameIsTruncatedTo127Characters(): void
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->willReturn(str_repeat('F', 200));

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturn('https://example.com/x');

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn('PL');

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturn(true);

        $builder = new BlikPaymentSourceNodeBuilder($configuration, $link, $countryRepository, $validate);
        $result = $builder->build($this->makeContext($this->makeCart()));

        $this->assertSame(127, mb_strlen($result['payment_source']['blik']['experience_context']['brand_name']));
    }
}
