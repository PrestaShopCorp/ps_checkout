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
use PsCheckout\Core\Order\Builder\Node\PaymentSource\VenmoPaymentSourceNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class VenmoPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(string $shopName = 'Test Shop'): VenmoPaymentSourceNodeBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn($shopName);

        return new VenmoPaymentSourceNodeBuilder($configuration);
    }

    /**
     * @return array<string, mixed>
     */
    private function makeCart(string $email = 'customer@example.com', bool $isVirtual = false, bool $hasShipping = false): array
    {
        $customer = new \stdClass();
        $customer->email = $email;

        $cart = ['customer' => $customer, 'cart' => ['is_virtual' => $isVirtual]];

        if ($hasShipping) {
            $address = new \stdClass();
            $address->id = 1;
            $cart['addresses']['shipping'] = $address;
        }

        return $cart;
    }

    /**
     * @dataProvider buildDataProvider
     * @param array<string, mixed> $expected
     */
    public function testBuild(
        ?string $vaultId,
        ?string $customerId,
        bool $savePaymentMethod,
        array $expected
    ): void {
        $builder = $this->makeBuilder();
        $builder->setCart($this->makeCart());

        if ($vaultId !== null) {
            $builder->setPaypalVaultId($vaultId);
        }
        if ($customerId !== null) {
            $builder->setPaypalCustomerId($customerId);
        }
        $builder->setSavePaymentMethod($savePaymentMethod);

        $this->assertSame($expected, $builder->build());
    }

    /**
     * @return array<string, array{?string, ?string, bool, array<string, mixed>}>
     */
    public function buildDataProvider(): array
    {
        $vaultAttributes = [
            'store_in_vault' => 'ON_SUCCESS',
            'usage_pattern' => 'IMMEDIATE',
            'usage_type' => 'MERCHANT',
            'customer_type' => 'CONSUMER',
            'permit_multiple_payment_tokens' => false,
        ];

        $experienceContext = [
            'brand_name' => 'Test Shop',
            'shipping_preference' => 'GET_FROM_FILE',
            'user_action' => 'PAY_NOW',
        ];

        return [
            'no save, no vault id, no customer id' => [
                null, null, false,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'save payment method without customer id' => [
                null, null, true,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'attributes' => [
                                'vault' => $vaultAttributes,
                            ],
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'save payment method with customer id' => [
                null, 'cust_abc', true,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'attributes' => [
                                'vault' => $vaultAttributes,
                                'customer' => ['id' => 'cust_abc'],
                            ],
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'vault id without save payment method' => [
                'vault_xyz', null, false,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'vault_id' => 'vault_xyz',
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'save payment method with vault id' => [
                'vault_xyz', null, true,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'attributes' => [
                                'vault' => $vaultAttributes,
                            ],
                            'vault_id' => 'vault_xyz',
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
            'all options set' => [
                'vault_xyz', 'cust_abc', true,
                [
                    'payment_source' => [
                        'venmo' => [
                            'email_address' => 'customer@example.com',
                            'attributes' => [
                                'vault' => $vaultAttributes,
                                'customer' => ['id' => 'cust_abc'],
                            ],
                            'vault_id' => 'vault_xyz',
                            'experience_context' => $experienceContext,
                        ],
                    ],
                ],
            ],
        ];
    }

    public function testShippingPreferenceIsNoShippingForVirtualCart(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart('customer@example.com', true))
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('NO_SHIPPING', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testShippingPreferenceIsSetProvidedAddressWhenShippingExists(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart('customer@example.com', false, true))
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('SET_PROVIDED_ADDRESS', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testShippingPreferenceIsGetFromFileByDefault(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('GET_FROM_FILE', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testUserActionIsPayNowWhenCartIsSet(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('PAY_NOW', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testUserActionIsContinueForExpressCheckout(): void
    {
        $result = $this->makeBuilder()
            ->setIsExpressCheckout(true)
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('CONTINUE', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testUserActionIsContinueWhenNoCart(): void
    {
        $result = $this->makeBuilder()
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('CONTINUE', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testBrandNameIsNormalizedFromShopName(): void
    {
        $result = $this->makeBuilder("My\nShop")
            ->setCart($this->makeCart())
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('MyShop', $result['payment_source']['venmo']['experience_context']['brand_name']);
    }

    public function testEmailCastToString(): void
    {
        $customer = new \stdClass();
        $customer->email = 42;

        $result = $this->makeBuilder()
            ->setCart(['customer' => $customer, 'cart' => ['is_virtual' => false]])
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertSame('42', $result['payment_source']['venmo']['email_address']);
    }

    public function testEmailAddressOmittedWhenNoCart(): void
    {
        $result = $this->makeBuilder()
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('email_address', $result['payment_source']['venmo']);
    }

    public function testCustomerIdIsIgnoredWhenSavePaymentMethodIsFalse(): void
    {
        $result = $this->makeBuilder()
            ->setCart($this->makeCart())
            ->setPaypalCustomerId('cust_abc')
            ->setSavePaymentMethod(false)
            ->build();

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['venmo']);
    }
}
