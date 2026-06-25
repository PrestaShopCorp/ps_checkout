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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Order\Builder\CheckoutContextBuilderInterface;
use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayNodeBuilderInterface;
use PsCheckout\Core\PayPal\ApplePay\Builder\ApplePayPaymentRequestDataBuilder;
use PsCheckout\Core\PayPal\ApplePay\ValueObject\ApplePayPaymentRequestData;

class ApplePayPaymentRequestDataBuilderTest extends TestCase
{
    /** @var CheckoutContextBuilderInterface|MockObject */
    private $contextBuilder;

    /** @var CheckoutContextInterface|MockObject */
    private $context;

    protected function setUp(): void
    {
        $this->context = $this->createMock(CheckoutContextInterface::class);
        $this->contextBuilder = $this->createMock(CheckoutContextBuilderInterface::class);
        $this->contextBuilder->method('setFundingSource')->willReturnSelf();
        $this->contextBuilder->method('build')->willReturn($this->context);
    }

    private function makeOrchestrator(
        ApplePayNodeBuilderInterface $amount,
        ApplePayNodeBuilderInterface $contact,
        ApplePayNodeBuilderInterface $shipping,
        ApplePayNodeBuilderInterface $coupon,
        ApplePayNodeBuilderInterface $applicationData
    ): ApplePayPaymentRequestDataBuilder {
        return new ApplePayPaymentRequestDataBuilder(
            $this->contextBuilder,
            $amount,
            $contact,
            $shipping,
            $coupon,
            $applicationData
        );
    }

    public function testBuildMergesAllNodeBuildersIntoPaymentRequestData(): void
    {
        $amountBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $amountBuilder->method('build')->willReturn([
            'currency_code' => 'EUR',
            'total' => ['type' => 'final', 'label' => 'Total', 'amount' => '99.99'],
        ]);

        $contactBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $contactBuilder->method('build')->willReturn(['billing_contact' => ['given_name' => 'John']]);

        $shippingBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $shippingBuilder->method('build')->willReturn(['shipping_methods' => []]);

        $couponBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $couponBuilder->method('build')->willReturn(['supports_coupon_code' => false]);

        $appDataBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $appDataBuilder->method('build')->willReturn(['application_data' => 'base64data==']);

        $result = $this->makeOrchestrator(
            $amountBuilder,
            $contactBuilder,
            $shippingBuilder,
            $couponBuilder,
            $appDataBuilder
        )->build();

        $this->assertInstanceOf(ApplePayPaymentRequestData::class, $result);
        $this->assertSame('EUR', $result->getCurrencyCode());
        $this->assertSame('99.99', $result->getTotal()->getAmount());

        $data = $result->toArray();
        $this->assertArrayHasKey('total', $data);
        $this->assertArrayHasKey('billing_contact', $data);
        $this->assertArrayHasKey('shipping_methods', $data);
        $this->assertArrayHasKey('supports_coupon_code', $data);
        $this->assertArrayHasKey('application_data', $data);
    }

    public function testBuildSetsApplepayFundingSourceOnContextBuilder(): void
    {
        $this->contextBuilder
            ->expects($this->once())
            ->method('setFundingSource')
            ->with('applepay')
            ->willReturnSelf();

        $nodeBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $nodeBuilder->method('build')->willReturn([]);

        $this->makeOrchestrator($nodeBuilder, $nodeBuilder, $nodeBuilder, $nodeBuilder, $nodeBuilder)->build();
    }

    public function testEachNodeBuilderReceivesTheSameContext(): void
    {
        $amountBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $amountBuilder->expects($this->once())->method('build')->with($this->context)->willReturn([]);

        $contactBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $contactBuilder->expects($this->once())->method('build')->with($this->context)->willReturn([]);

        $shippingBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $shippingBuilder->expects($this->once())->method('build')->with($this->context)->willReturn([]);

        $couponBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $couponBuilder->expects($this->once())->method('build')->with($this->context)->willReturn([]);

        $appDataBuilder = $this->createMock(ApplePayNodeBuilderInterface::class);
        $appDataBuilder->expects($this->once())->method('build')->with($this->context)->willReturn([]);

        $this->makeOrchestrator($amountBuilder, $contactBuilder, $shippingBuilder, $couponBuilder, $appDataBuilder)->build();
    }
}
