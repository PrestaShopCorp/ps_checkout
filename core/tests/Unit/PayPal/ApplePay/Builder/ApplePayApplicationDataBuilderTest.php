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

namespace Tests\Unit\PsCheckout\Core\PayPal\ApplePay\Builder;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\CheckoutContext;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayApplicationDataBuilder;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class ApplePayApplicationDataBuilderTest extends TestCase
{
    private function makeBuilder(string $environment = 'SANDBOX'): ApplePayApplicationDataBuilder
    {
        $configuration = $this->createMock(ConfigurationInterface::class);
        $configuration->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE)
            ->willReturn($environment);

        return new ApplePayApplicationDataBuilder($configuration);
    }

    private function makeContext(int $cartId = 42): CheckoutContext
    {
        return new CheckoutContext(
            ['cart' => ['id' => $cartId, 'is_virtual' => false]],
            'applepay',
            false,
            null,
            null,
            false,
            false
        );
    }

    public function testApplicationDataIsBase64Encoded(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext());

        $this->assertArrayHasKey('application_data', $result);
        /** @var string $appData */
        $appData = $result['application_data'];
        $decoded = base64_decode($appData, true);
        $this->assertNotFalse($decoded, 'application_data must be valid Base64');
    }

    public function testApplicationDataContainsCartId(): void
    {
        $result = $this->makeBuilder()->build($this->makeContext(99));

        /** @var string $appData */
        $appData = $result['application_data'];
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) base64_decode($appData, true), true);
        $this->assertSame(99, $decoded['cart_id']);
    }

    public function testApplicationDataContainsEnvironment(): void
    {
        $result = $this->makeBuilder('LIVE')->build($this->makeContext());

        /** @var string $appData */
        $appData = $result['application_data'];
        /** @var array<string, mixed> $decoded */
        $decoded = json_decode((string) base64_decode($appData, true), true);
        $this->assertSame('LIVE', $decoded['environment']);
    }
}
