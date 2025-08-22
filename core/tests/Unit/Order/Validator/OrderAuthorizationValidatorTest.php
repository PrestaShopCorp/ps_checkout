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

namespace PsCheckout\Tests\Unit\Order\Validator;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Validator\OrderAuthorizationValidator;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureConfiguration;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureValidatorInterface;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\CustomerInterface;
use Psr\Log\LoggerInterface;

class OrderAuthorizationValidatorTest extends TestCase
{
    /** @var OrderAuthorizationValidator */
    private $validator;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var CustomerInterface|MockObject */
    private $customer;

    /** @var CartInterface|MockObject */
    private $cartAdapter;

    /** @var ConfigurationInterface|MockObject */
    private $configuration;

    /** @var Card3DSecureValidatorInterface|MockObject */
    private $card3DSecureValidator;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->customer = $this->createMock(CustomerInterface::class);
        $this->cartAdapter = $this->createMock(CartInterface::class);
        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->card3DSecureValidator = $this->createMock(Card3DSecureValidatorInterface::class);

        $this->validator = new OrderAuthorizationValidator(
            $this->logger,
            $this->customer,
            $this->cartAdapter,
            $this->configuration,
            $this->card3DSecureValidator
        );
    }

    public function testItThrowsExceptionWhenOrderIsAlreadyCompleted(): void
    {
        $payPalOrder = $this->createMock(PayPalOrderResponse::class);
        $payPalOrder->method('getStatus')->willReturn('COMPLETED');
        $payPalOrder->method('getId')->willReturn('ORDER-123');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order ORDER-123 is already captured');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_ORDER_ALREADY_CAPTURED);

        $this->validator->validate(1, $payPalOrder);
    }

    /**
     * @dataProvider provide3DSecureScenarios
     */
    public function testIt3DSecureValidation(
        string $paymentSource,
        int $secureDecision,
        string $contingencies,
        bool $productsInStock,
        $expectedExceptionMessage,
        $expectedExceptionCode
    ) {
        $payPalOrder = $this->createMock(PayPalOrderResponse::class);
        $payPalOrder->method('getStatus')->willReturn('PENDING');
        $payPalOrder->method('getPaymentSource')->willReturn([$paymentSource => []]);
        $payPalOrder->method('getAuthenticationResult')->willReturn(['status' => 'test']);
        $payPalOrder->method('getOrderAmountValue')->willReturn(100.00);

        $cart = $this->createCartMock(true);
        $this->cartAdapter->method('getCart')->willReturn($cart);

        $this->customer->method('customerHasAddress')
            ->willReturn(true);

        $this->configuration->method('get')
            ->withConsecutive(
                ['PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES'],
                ['PS_STOCK_MANAGEMENT']
            )
            ->willReturnOnConsecutiveCalls($contingencies, $productsInStock);

        $this->card3DSecureValidator->method('getAuthorizationDecision')
            ->willReturn($secureDecision);

        // Expect logging for 3D Secure results
        $this->logger->expects($this->once())
            ->method('info')
            ->with(
                '3D Secure authentication result',
                $this->arrayHasKey('authentication_result')
            );

        if ($expectedExceptionMessage) {
            $this->expectException(PsCheckoutException::class);
            $this->expectExceptionMessage($expectedExceptionMessage);
            $this->expectExceptionCode($expectedExceptionCode);
        }

        $this->validator->validate(1, $payPalOrder);
    }

    private function createCartMock(bool $valid = true): MockObject
    {
        $cart = $this->getMockBuilder(\Cart::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProducts', 'isAllProductsInStock', 'checkAllProductsAreStillAvailableInThisState',
                         'checkAllProductsHaveMinimalQuantities', 'isVirtualCart', 'getDeliveryOptionList', 'getOrderTotal'])
            ->getMock();

        $cart->id = 1;
        $cart->id_customer = 1;
        $cart->id_address_invoice = 1;
        $cart->id_address_delivery = 1;

        $cart->method('getProducts')
            ->willReturn($valid ? [['id_product' => 1]] : []);

        $cart->method('isAllProductsInStock')
            ->willReturn($valid);

        $cart->method('checkAllProductsAreStillAvailableInThisState')
            ->willReturn($valid);

        $cart->method('checkAllProductsHaveMinimalQuantities')
            ->willReturn($valid);

        $cart->method('isVirtualCart')
            ->willReturn(false);

        $cart->method('getDeliveryOptionList')
            ->willReturn($valid ? [1 => ['carrier_list' => []]] : []);

        $cart->method('getOrderTotal')
            ->with(true, \Cart::BOTH)
            ->willReturn(100.00);

        return $cart;
    }

    public function provide3DSecureScenarios(): array
    {
        return [
            'card_payment_success' => [
                'paymentSource' => 'card',
                'secureDecision' => Card3DSecureConfiguration::DECISION_PROCEED,
                'contingencies' => 'SCA_ALWAYS',
                'productsInStock' => false,
                'expectedExceptionMessage' => null,
                'expectedExceptionCode' => null,
            ],
            'card_payment_reject' => [
                'paymentSource' => 'card',
                'secureDecision' => Card3DSecureConfiguration::DECISION_REJECT,
                'contingencies' => 'SCA_ALWAYS',
                'productsInStock' => false,
                'expectedExceptionMessage' => 'Card Strong Customer Authentication failure',
                'expectedExceptionCode' => PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_FAILURE,
            ],
            'card_payment_retry' => [
                'paymentSource' => 'card',
                'secureDecision' => Card3DSecureConfiguration::DECISION_RETRY,
                'contingencies' => 'SCA_ALWAYS',
                'productsInStock' => false,
                'expectedExceptionMessage' => 'Card Strong Customer Authentication must be retried.',
                'expectedExceptionCode' => PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN,
            ],
            'card_payment_no_decision_with_sca_always' => [
                'paymentSource' => 'card',
                'secureDecision' => Card3DSecureConfiguration::DECISION_NO_DECISION,
                'contingencies' => 'SCA_ALWAYS',
                'productsInStock' => false,
                'expectedExceptionMessage' => 'No liability shift to card issuer',
                'expectedExceptionCode' => PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN,
            ],
            'card_payment_no_decision_with_sca_when_required' => [
                'paymentSource' => 'card',
                'secureDecision' => Card3DSecureConfiguration::DECISION_NO_DECISION,
                'contingencies' => 'SCA_ALWAYS',
                'productsInStock' => false,
                'expectedExceptionMessage' => 'No liability shift to card issuer',
                'expectedExceptionCode' => PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN,
            ],
        ];
    }

    public function testItThrowsExceptionWhenCartNotFound(): void
    {
        $payPalOrder = $this->createMock(PayPalOrderResponse::class);
        $payPalOrder->method('getStatus')->willReturn('PENDING');
        $payPalOrder->method('getPaymentSource')->willReturn(['paypal' => []]);

        $this->cartAdapter->method('getCart')->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Cart with id 1 not found.');
        $this->expectExceptionCode(PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);

        $this->validator->validate(1, $payPalOrder);
    }

    public function testItThrowsExceptionWhenCartHasNoProducts(): void
    {
        $payPalOrder = $this->createMock(PayPalOrderResponse::class);
        $payPalOrder->method('getStatus')->willReturn('PENDING');
        $payPalOrder->method('getPaymentSource')->willReturn(['paypal' => []]);

        $cart = $this->createCartMock(false);
        $this->cartAdapter->method('getCart')->willReturn($cart);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Cart with id 1 has no product. Cannot capture the order.');
        $this->expectExceptionCode(PsCheckoutException::CART_PRODUCT_MISSING);

        $this->validator->validate(1, $payPalOrder);
    }
}
