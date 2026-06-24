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

namespace PsCheckout\Tests\Unit\Presenter\OrderSummary;

use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;

class OrderSummaryPresenterTest extends TestCase
{
    /**
     * @dataProvider provideIsTokenSavedScenarios
     *
     * @return void
     */
    public function testIsTokenSavedChecksPaymentTokenId(?string $paymentTokenId, bool $expected): void
    {
        $payPalOrder = new PayPalOrder(
            'ORDER-123',
            1,
            'CAPTURE',
            'card',
            'COMPLETED',
            [],
            'live',
            true,
            false,
            [],
            $paymentTokenId
        );

        $result = !empty($payPalOrder->getPaymentTokenId());

        $this->assertSame($expected, $result);
    }

    /**
     * @return array<string, array{string|null, bool}>
     */
    public function provideIsTokenSavedScenarios(): array
    {
        return [
            'token_id_set' => ['vault-token-123', true],
            'token_id_null' => [null, false],
        ];
    }
}
