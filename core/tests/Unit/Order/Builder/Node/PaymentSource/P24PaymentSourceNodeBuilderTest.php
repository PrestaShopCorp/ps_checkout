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
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\Order\Builder\Node\PaymentSource\P24PaymentSourceNodeBuilder;
use PsCheckout\Core\Util\ExperienceContextHelper;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Adapter\Validate;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\StateRepositoryInterface;
use Psr\Log\LoggerInterface;

class P24PaymentSourceNodeBuilderTest extends TestCase
{
    private function makeExperienceContextHelper(string $shopName = 'My Shop', string $countryCode = 'PL'): ExperienceContextHelper
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn($shopName);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action) {
            return 'https://example.com/' . $action;
        });

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->method('getCountryIsoCodeById')->willReturn($countryCode);

        return new ExperienceContextHelper($configuration, $link, $countryRepository, $this->createMock(StateRepositoryInterface::class));
    }

    private function makeBuilder(): P24PaymentSourceNodeBuilder
    {
        $logger = $this->createMock(LoggerInterface::class);

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturnCallback(
            static function (string $email): bool {
                return (bool) preg_match(Validate::PAYPAL_EMAIL_PATTERN, $email);
            }
        );

        return new P24PaymentSourceNodeBuilder($this->makeExperienceContextHelper(), $logger, $validate);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $email = 'jan@example.com', string $locale = ''): array
    {
        $address = new \stdClass();
        $address->firstname = 'Jan';
        $address->lastname = 'Kowalski';
        $address->id_country = 1;

        $customer = new \stdClass();
        $customer->email = $email;

        $cart = [
            'addresses' => ['invoice' => $address],
            'customer' => $customer,
        ];

        if ($locale !== '') {
            $language = new \stdClass();
            $language->locale = $locale;
            $cart['language'] = $language;
        }

        return $cart;
    }

    /**
     * @param array<string, mixed> $cart
     */
    private function makeContext(array $cart): CheckoutContext
    {
        return new CheckoutContext($cart, 'p24', false, null, null, false, false);
    }

    public function testSupportsP24(): void
    {
        $builder = $this->makeBuilder();

        $this->assertTrue($builder->supports('p24'));
        $this->assertFalse($builder->supports('ideal'));
    }

    public function testBuildReturnsCorrectStructure(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart()));

        $this->assertSame([
            'payment_source' => [
                'p24' => [
                    'name' => 'Jan Kowalski',
                    'email' => 'jan@example.com',
                    'country_code' => 'PL',
                    'experience_context' => [
                        'brand_name' => 'My Shop',
                        'shipping_preference' => 'GET_FROM_FILE',
                        'return_url' => 'https://example.com/validate',
                        'cancel_url' => 'https://example.com/cancel',
                    ],
                ],
            ],
        ], $result);
    }

    public function testEmailIsIncludedWhenValid(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart('customer@shop.pl')));

        $this->assertArrayHasKey('email', $result['payment_source']['p24']);
        $this->assertSame('customer@shop.pl', $result['payment_source']['p24']['email']);
    }

    public function testLocaleIsIncludedWhenSupported(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart('jan@example.com', 'pl-PL')));

        $this->assertSame('pl-PL', $result['payment_source']['p24']['experience_context']['locale']);
    }

    public function testLocaleIsOmittedWhenNotSupported(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart('jan@example.com', 'pl_PL')));

        $this->assertArrayNotHasKey('locale', $result['payment_source']['p24']['experience_context']);
    }

    public function testLocaleIsOmittedWhenMissing(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext($this->makeCart()));

        $this->assertArrayNotHasKey('locale', $result['payment_source']['p24']['experience_context']);
    }

    public function testBuildThrowsExceptionWhenEmailHasNoTld(): void
    {
        // Regression: emails without a TLD were silently forwarded to PayPal, causing INVALID_PARAMETER_SYNTAX.
        $this->expectException(PsCheckoutException::class);

        $this->makeBuilder()->build($this->makeContext($this->makeCart('einkauf@my-shop')));
    }

    public function testBuildThrowsExceptionWhenEmailIsEmpty(): void
    {
        $this->expectException(PsCheckoutException::class);

        $this->makeBuilder()->build($this->makeContext($this->makeCart('')));
    }

    public function testBrandNameIsTruncatedTo127Characters(): void
    {
        $logger = $this->createMock(LoggerInterface::class);

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturn(true);

        $builder = new P24PaymentSourceNodeBuilder(
            $this->makeExperienceContextHelper(str_repeat('E', 200)),
            $logger,
            $validate
        );
        $result = $builder->build($this->makeContext($this->makeCart()));

        $this->assertSame(127, mb_strlen($result['payment_source']['p24']['experience_context']['brand_name']));
    }

    public function testCountryCodeComesFromRepository(): void
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->willReturn('Shop');

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturn('https://example.com/x');

        $countryRepository = $this->createMock(CountryRepositoryInterface::class);
        $countryRepository->expects($this->once())
            ->method('getCountryIsoCodeById')
            ->with(55)
            ->willReturn('PL');

        $logger = $this->createMock(LoggerInterface::class);

        $validate = $this->createMock(ValidateInterface::class);
        $validate->method('isPayPalEmail')->willReturn(true);

        $address = new \stdClass();
        $address->firstname = 'Jan';
        $address->lastname = 'Kowalski';
        $address->id_country = 55;

        $customer = new \stdClass();
        $customer->email = 'jan@example.com';

        $builder = new P24PaymentSourceNodeBuilder(
            new ExperienceContextHelper($configuration, $link, $countryRepository, $this->createMock(StateRepositoryInterface::class)),
            $logger,
            $validate
        );
        $result = $builder->build($this->makeContext([
            'addresses' => ['invoice' => $address],
            'customer' => $customer,
        ]));

        $this->assertSame('PL', $result['payment_source']['p24']['country_code']);
    }
}
