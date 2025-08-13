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

use PHPUnit\Framework\TestCase;
use PsCheckout\Infrastructure\Adapter\AddressInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CurrencyInterface;
use PsCheckout\Infrastructure\Repository\CustomerRepositoryInterface;
use PsCheckout\Infrastructure\Repository\LanguageRepositoryInterface;
use PsCheckout\Presentation\Presenter\Cart\CartPresenter;

class CartPresenterTest extends TestCase
{
    private $context;

    private $address;

    private $currency;

    private $languageRepository;

    private $customerRepository;

    private $cartPresenter;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->address = $this->createMock(AddressInterface::class);
        $this->currency = $this->createMock(CurrencyInterface::class);
        $this->languageRepository = $this->createMock(LanguageRepositoryInterface::class);
        $this->customerRepository = $this->createMock(CustomerRepositoryInterface::class);

        $this->cartPresenter = new CartPresenter(
            $this->context,
            $this->address,
            $this->currency,
            $this->languageRepository,
            $this->customerRepository
        );
    }

    /**
     * @dataProvider cartDataProvider
     */
    public function testPresentReturnsExpectedData($cartData, $expectedResult)
    {
        $cartMock = $this->createMock(Cart::class);
        $cartMock->id = $cartData['id'];
        $cartMock->id_address_delivery = $cartData['id_address_delivery'];
        $cartMock->id_address_invoice = $cartData['id_address_invoice'];
        $cartMock->id_currency = $cartData['id_currency'];
        $cartMock->id_customer = $cartData['id_customer'];
        $cartMock->id_lang = $cartData['id_lang'];

        $cartMock->method('getProducts')->willReturn($cartData['products']);
        $cartMock->method('getTotalShippingCost')->willReturn($cartData['shipping_cost']);
        $cartMock->method('getOrderTotal')->willReturn($cartData['total_including_tax']);
        $cartMock->method('getGiftWrappingPrice')->willReturn($cartData['gift_wrapping']);

        $this->context->method('getCart')->willReturn($cartMock);

        $this->address->method('initialize')->willReturnMap([
            [$cartData['id_address_delivery'], $cartData['shipping_address']],
            [$cartData['id_address_invoice'], $cartData['invoice_address']],
        ]);

        $this->currency->method('getCurrencyInstance')->willReturn((object) ['iso_code' => $cartData['currency_iso_code']]);

        $this->customerRepository->method('getOneBy')->willReturn($cartData['customer']);

        $this->languageRepository->method('getOneBy')->willReturn($cartData['language']);

        $result = $this->cartPresenter->present();

        $this->assertEquals($expectedResult, $result);
    }

    public function cartDataProvider(): array
    {
        return [
            'full cart' => [
                'cartData' => [
                    'id' => 1,
                    'id_address_delivery' => 2,
                    'id_address_invoice' => 3,
                    'id_currency' => 4,
                    'id_customer' => 5,
                    'id_lang' => 6,
                    'products' => [['id_product' => 1, 'name' => 'Product 1']],
                    'shipping_cost' => 10.00,
                    'total_including_tax' => 100.00,
                    'gift_wrapping' => 5.00,
                    'shipping_address' => ['id' => 2, 'address' => 'Shipping Address'],
                    'invoice_address' => ['id' => 3, 'address' => 'Invoice Address'],
                    'currency_iso_code' => 'USD',
                    'customer' => ['id_customer' => 5, 'name' => 'John Doe'],
                    'language' => ['id_lang' => 6, 'iso_code' => 'en'],
                ],
                'expectedResult' => [
                    'cart' => [
                        'id' => 1,
                        'shipping_cost' => 10.00,
                        'totals' => [
                            'total_including_tax' => ['amount' => 100.00],
                        ],
                        'subtotals' => [
                            'gift_wrapping' => ['amount' => 5.00],
                        ],
                    ],
                    'customer' => ['id_customer' => 5, 'name' => 'John Doe'],
                    'language' => ['id_lang' => 6, 'iso_code' => 'en'],
                    'products' => [['id_product' => 1, 'name' => 'Product 1']],
                    'addresses' => [
                        'shipping' => ['id' => 2, 'address' => 'Shipping Address'],
                        'invoice' => ['id' => 3, 'address' => 'Invoice Address'],
                    ],
                    'currency' => ['iso_code' => 'USD'],
                ],
            ],
            'empty cart' => [
                'cartData' => [
                    'id' => null,
                    'id_address_delivery' => null,
                    'id_address_invoice' => null,
                    'id_currency' => null,
                    'id_customer' => null,
                    'id_lang' => null,
                    'products' => [],
                    'shipping_cost' => 0.00,
                    'total_including_tax' => 0.00,
                    'gift_wrapping' => 0.00,
                    'shipping_address' => null,
                    'invoice_address' => null,
                    'currency_iso_code' => null,
                    'customer' => null,
                    'language' => null,
                ],
                'expectedResult' => [
                    'cart' => [
                        'id' => null,
                        'shipping_cost' => 0.00,
                        'totals' => [
                            'total_including_tax' => ['amount' => 0.00],
                        ],
                        'subtotals' => [
                            'gift_wrapping' => ['amount' => 0.00],
                        ],
                    ],
                    'customer' => null,
                    'language' => null,
                    'products' => [],
                    'addresses' => [
                        'shipping' => null,
                        'invoice' => null,
                    ],
                    'currency' => ['iso_code' => null],
                ],
            ],
        ];
    }
}
