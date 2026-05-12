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
use PsCheckout\Core\Order\Builder\Node\PaymentSource\VenmoPaymentSourceNodeBuilder;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;

class VenmoPaymentSourceNodeBuilderTest extends TestCase
{
    private function makeBuilder(string $shopName = 'Test Shop'): VenmoPaymentSourceNodeBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')->with('PS_SHOP_NAME')->willReturn($shopName);

        $link = $this->createMock(LinkInterface::class);
        $link->method('getModuleLink')->willReturnCallback(static function (string $action, array $params = []) {
            $query = !empty($params) ? '?' . http_build_query($params) : '';

            return 'https://example.com/' . $action . $query;
        });

        return new VenmoPaymentSourceNodeBuilder($configuration, $link);
    }

    /**
     * @param string $email
     * @param bool $isVirtual
     * @param bool $hasShipping
     * @param bool $savePaymentMethod
     * @param bool $isExpressCheckout
     * @param bool $isUpdate
     * @param string|null $vaultId
     * @param string|null $customerId
     * @param int $cartId
     */
    private function makeContext(
        $email = 'customer@example.com',
        $isVirtual = false,
        $hasShipping = false,
        $savePaymentMethod = false,
        $isExpressCheckout = false,
        $isUpdate = false,
        $vaultId = null,
        $customerId = null,
        $cartId = 0
    ): CheckoutContext {
        $customer = new \stdClass();
        $customer->email = $email;

        $cart = ['customer' => $customer, 'cart' => ['id' => $cartId, 'is_virtual' => $isVirtual]];

        if ($hasShipping) {
            $address = new \stdClass();
            $address->id = 1;
            $cart['addresses']['shipping'] = $address;
        }

        return new CheckoutContext(
            $cart,
            'venmo',
            $savePaymentMethod,
            $customerId,
            $vaultId,
            $isExpressCheckout,
            $isUpdate
        );
    }

    /**
     * @dataProvider buildDataProvider
     * @param array<string, mixed> $expected
     */
    public function testBuild(
        $vaultId,
        $customerId,
        bool $savePaymentMethod,
        array $expected
    ): void {
        $context = $this->makeContext('customer@example.com', false, false, $savePaymentMethod, false, false, $vaultId, $customerId);

        $this->assertSame($expected, $this->makeBuilder()->build($context));
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
            'return_url' => 'https://example.com/validate',
            'cancel_url' => 'https://example.com/cancel',
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
        $result = $this->makeBuilder()->build($this->makeContext('customer@example.com', true));

        $this->assertSame('NO_SHIPPING', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testShippingPreferenceIsSetProvidedAddressWhenShippingExists(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext('customer@example.com', false, true));

        $this->assertSame('SET_PROVIDED_ADDRESS', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testShippingPreferenceIsGetFromFileByDefault(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertSame('GET_FROM_FILE', $result['payment_source']['venmo']['experience_context']['shipping_preference']);
    }

    public function testUserActionIsPayNowForStandardCheckout(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertSame('PAY_NOW', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testUserActionIsContinueForExpressCheckout(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext('customer@example.com', false, false, false, true));

        $this->assertSame('CONTINUE', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testUserActionIsContinueForUpdate(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext('customer@example.com', false, false, false, false, true));

        $this->assertSame('CONTINUE', $result['payment_source']['venmo']['experience_context']['user_action']);
    }

    public function testBrandNameIsNormalizedFromShopName(): void
    {
        $result = $this->makeBuilder("My\nShop")->build($this->makeContext());

        $this->assertSame('MyShop', $result['payment_source']['venmo']['experience_context']['brand_name']);
    }

    public function testEmailCastToString(): void
    {
        $customer = new \stdClass();
        $customer->email = 42;
        $cart = ['customer' => $customer, 'cart' => ['id' => 0, 'is_virtual' => false]];
        $context = new CheckoutContext($cart, 'venmo', false, null, null, false, false);

        $result = $this->makeBuilder()->build($context);

        $this->assertSame('42', $result['payment_source']['venmo']['email_address']);
    }

    public function testEmailAddressOmittedWhenExpressCheckout(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext('customer@example.com', false, false, false, true));

        $this->assertArrayNotHasKey('email_address', $result['payment_source']['venmo']);
    }

    public function testEmailAddressOmittedWhenUpdate(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext('customer@example.com', false, false, false, false, true));

        $this->assertArrayNotHasKey('email_address', $result['payment_source']['venmo']);
    }

    public function testCustomerIdIsIgnoredWhenSavePaymentMethodIsFalse(): void
    {
        $result = $this->makeBuilder()->build(
            $this->makeContext('customer@example.com', false, false, false, false, false, null, 'cust_abc')
        );

        $this->assertArrayNotHasKey('attributes', $result['payment_source']['venmo']);
    }

    public function testOrderUpdateCallbackConfigPresentWhenGetFromFile(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext(
            'customer@example.com',
            false,
            false,
            false,
            false,
            false,
            null,
            null,
            7
        ));

        $callbackConfig = $result['payment_source']['venmo']['experience_context']['order_update_callback_config'];
        $this->assertSame(['SHIPPING_ADDRESS', 'SHIPPING_OPTIONS'], $callbackConfig['callback_events']);
        $this->assertStringContainsString('id_cart=7', $callbackConfig['callback_url']);
        $this->assertStringContainsString('shipping', $callbackConfig['callback_url']);
    }

    /**
     * @dataProvider noCallbackConfigProvider
     */
    public function testOrderUpdateCallbackConfigAbsentWhenNotGetFromFile(bool $isVirtual, bool $hasShipping): void
    {
        $result = $this->makeBuilder()->build(
            $this->makeContext('customer@example.com', $isVirtual, $hasShipping, false, false, false, null, null, 7)
        );

        $this->assertArrayNotHasKey(
            'order_update_callback_config',
            $result['payment_source']['venmo']['experience_context']
        );
    }

    /**
     * @return array<string, array{bool, bool}>
     */
    public static function noCallbackConfigProvider(): array
    {
        return [
            'virtual cart → NO_SHIPPING' => [true, false],
            'shipping address provided → SET_PROVIDED_ADDRESS' => [false, true],
        ];
    }

    public function testOrderUpdateCallbackConfigAbsentWhenCartIdIsZero(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertArrayNotHasKey(
            'order_update_callback_config',
            $result['payment_source']['venmo']['experience_context']
        );
    }

    public function testSupportsVenmo(): void
    {
        $builder = $this->makeBuilder();
        $this->assertTrue($builder->supports('venmo'));
        $this->assertFalse($builder->supports('paypal'));
        $this->assertFalse($builder->supports('card'));
    }
}
