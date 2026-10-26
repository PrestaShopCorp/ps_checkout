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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\FundingSource\ValueObject\FundingSource;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Environment\EnvInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTokenPresenterInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourceTranslationProviderInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;
use PsCheckout\Presentation\Presenter\Settings\Front\Modules\PayPalModule;

class PayPalModuleTest extends TestCase
{
    /** @var ContextInterface|MockObject */
    private $context;

    /** @var FundingSourceTokenPresenterInterface|MockObject */
    private $fundingSourceTokenPresenter;

    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $payPalOrderRepository;

    /** @var PayPalModule */
    private $module;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->fundingSourceTokenPresenter = $this->createMock(FundingSourceTokenPresenterInterface::class);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);

        $fundingSourcePresenter = $this->createMock(FundingSourcePresenterInterface::class);
        $fundingSourcePresenter->method('getAllActiveForSpecificShop')->willReturn([
            new FundingSource('paypal', 'PayPal', 1, true, null),
        ]);

        $translationProvider = $this->createMock(FundingSourceTranslationProviderInterface::class);
        $translationProvider->method('getPaymentMethodName')->willReturn('PayPal');

        $supportedCardBrandsPresenter = $this->createMock(PresenterInterface::class);
        $supportedCardBrandsPresenter->method('present')->willReturn(['VISA']);

        $env = $this->createMock(EnvInterface::class);
        $env->method('getBnCode')->willReturn('BN_CODE');

        $this->module = new PayPalModule(
            'ps_checkout',
            '1.0.0',
            $this->context,
            $env,
            $fundingSourcePresenter,
            $this->fundingSourceTokenPresenter,
            $supportedCardBrandsPresenter,
            $this->payPalOrderRepository,
            $translationProvider
        );

        $shop = new \stdClass();
        $shop->id = 1;
        $this->context->method('getShop')->willReturn($shop);

        $customer = new \stdClass();
        $customer->id = 42;
        $this->context->method('getCustomer')->willReturn($customer);

        $cart = $this->createMock(\Cart::class);
        $cart->id = 7;
        $cart->method('nbProducts')->willReturn(3);
        $this->context->method('getCart')->willReturn($cart);

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getId')->willReturn('PAYPAL_ORDER_ID');
        $payPalOrder->method('getFundingSource')->willReturn('card');
        $payPalOrder->method('isExpressCheckout')->willReturn(true);
        $this->payPalOrderRepository->method('getOneByCartId')->willReturn($payPalOrder);

        $this->fundingSourceTokenPresenter->method('getFundingSourceTokens')->willReturn([
            new FundingSource('token-VAULT_ID', 'Visa ****1234', 0, true, null),
        ]);
    }

    /**
     * @return array<mixed>
     */
    private function presentOn(string $phpSelf): array
    {
        $controller = new \stdClass();
        $controller->php_self = $phpSelf;
        $this->context->method('getController')->willReturn($controller);

        return $this->module->present();
    }

    /**
     * @return array<string, array{string}>
     */
    public function publicPagesProvider(): array
    {
        return [
            'product' => ['product'],
            'cart' => ['cart'],
            'index' => ['index'],
            'category' => ['category'],
            'authentication' => ['authentication'],
        ];
    }

    /**
     * @dataProvider publicPagesProvider
     */
    public function testNoVisitorSpecificDataOutsideTheCheckout(string $phpSelf): void
    {
        $settings = $this->presentOn($phpSelf);
        /** @var array<string, string> $customMarks */
        $customMarks = $settings['ps_checkoutCustomMarks'];
        $json = (string) json_encode($settings);

        $this->assertSame('', $settings['ps_checkoutPayPalOrderId']);
        $this->assertSame('paypal', $settings['ps_checkoutFundingSource']);
        $this->assertFalse($settings['ps_checkoutExpressCheckoutSelected']);
        $this->assertSame(0, $settings['ps_checkoutCartProductCount']);
        $this->assertSame(['paypal'], $settings['ps_checkoutFundingSourcesSorted']);
        $this->assertSame(['paypal' => 'PayPal'], $settings['ps_checkoutPayWithTranslations']);
        $this->assertArrayNotHasKey('token-VAULT_ID', $customMarks);
        $this->assertStringNotContainsString('VAULT_ID', $json);
        $this->assertStringNotContainsString('1234', $json);
    }

    /**
     * @return array<string, array{string}>
     */
    public function checkoutPagesProvider(): array
    {
        return [
            'order' => ['order'],
            'order-opc' => ['order-opc'],
            'orderopc' => ['orderopc'],
        ];
    }

    /**
     * @dataProvider checkoutPagesProvider
     */
    public function testVisitorDataIsPresentedOnTheCheckout(string $phpSelf): void
    {
        $settings = $this->presentOn($phpSelf);
        /** @var array<string, string> $payWithTranslations */
        $payWithTranslations = $settings['ps_checkoutPayWithTranslations'];

        $this->assertSame('PAYPAL_ORDER_ID', $settings['ps_checkoutPayPalOrderId']);
        $this->assertSame('card', $settings['ps_checkoutFundingSource']);
        $this->assertTrue($settings['ps_checkoutExpressCheckoutSelected']);
        $this->assertSame(3, $settings['ps_checkoutCartProductCount']);
        $this->assertSame(['token-VAULT_ID', 'paypal'], $settings['ps_checkoutFundingSourcesSorted']);
        $this->assertSame('Visa ****1234', $payWithTranslations['token-VAULT_ID']);
    }

    public function testNothingVisitorSpecificWithoutController(): void
    {
        $this->context->method('getController')->willReturn(null);

        $settings = $this->module->present();

        $this->assertSame('', $settings['ps_checkoutPayPalOrderId']);
        $this->assertSame(['paypal'], $settings['ps_checkoutFundingSourcesSorted']);
    }
}
