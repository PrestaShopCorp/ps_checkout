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

namespace PsCheckout\Tests\Unit\Order\Exception\Handler;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Exception\Handler\OrderCreationExceptionHandler;
use PsCheckout\Infrastructure\Action\CustomerNotifyActionInterface;
use PsCheckout\Presentation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class OrderCreationExceptionHandlerTest extends TestCase
{
    /** @var OrderCreationExceptionHandler */
    private $handler;

    /** @var TranslatorInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var LoggerInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var CustomerNotifyActionInterface&\PHPUnit\Framework\MockObject\MockObject */
    private $customerNotifyAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->translator->method('trans')->willReturnCallback(function ($key) {
            return $key;
        });

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->customerNotifyAction = $this->createMock(CustomerNotifyActionInterface::class);

        $this->handler = new OrderCreationExceptionHandler(
            $this->translator,
            $this->logger,
            $this->customerNotifyAction
        );
    }

    /**
     * @dataProvider provideNonCriticalPayPalExceptionCodes
     */
    public function testNonCriticalPayPalExceptionsReturn400WithoutNotification(int $code, string $expectedMessage): void
    {
        $exception = new PayPalException('test', $code);

        $this->customerNotifyAction->expects($this->never())->method('execute');
        $this->logger->expects($this->once())->method('notice');

        /** @var array{httpCode: int, status: bool, body: array{error: array{message: string}}} $result */
        $result = $this->handler->handle($exception, 'PAYPAL-ORDER-123');

        $this->assertSame(400, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame($expectedMessage, $result['body']['error']['message']);
    }

    /**
     * @dataProvider provideCriticalPayPalExceptionCodes
     */
    public function testCriticalPayPalExceptionsReturn500WithNotification(int $code, string $expectedMessage): void
    {
        $exception = new PayPalException('test', $code);

        $this->customerNotifyAction->expects($this->once())->method('execute');
        $this->logger->expects($this->once())->method('error');

        /** @var array{httpCode: int, status: bool, body: array{error: array{message: string}}} $result */
        $result = $this->handler->handle($exception, 'PAYPAL-ORDER-123');

        $this->assertSame(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame($expectedMessage, $result['body']['error']['message']);
    }

    /**
     * @return array<string, array{int, string}>
     */
    public function provideNonCriticalPayPalExceptionCodes(): array
    {
        return [
            'CARD_EXPIRED' => [
                PayPalException::CARD_EXPIRED,
                'Your card has expired. Please use another card.',
            ],
            'CARD_CLOSED' => [
                PayPalException::CARD_CLOSED,
                'This card has been closed by the issuer. Please use another card.',
            ],
            'INVALID_SECURITY_CODE' => [
                PayPalException::INVALID_SECURITY_CODE,
                'The security code is incorrect. Please check and try again.',
            ],
            'INVALID_EXPIRY_DATE' => [
                PayPalException::INVALID_EXPIRY_DATE,
                'The expiry date is invalid. Please check and try again.',
            ],
            'CREDIT_CARD_NUMBER_IS_INVALID' => [
                PayPalException::CREDIT_CARD_NUMBER_IS_INVALID,
                'The card number is invalid. Please check and try again.',
            ],
            'CARD_EXPIRATION_YEAR_IS_INVALID' => [
                PayPalException::CARD_EXPIRATION_YEAR_IS_INVALID,
                'The card expiration year is invalid. Please check and try again.',
            ],
            'BILLING_ADDRESS_INVALID' => [
                PayPalException::BILLING_ADDRESS_INVALID,
                'The billing address is invalid. Please check and try again.',
            ],
            'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR' => [
                PayPalException::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR,
                'Your payment was declined. Please try a different payment method.',
            ],
            'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED' => [
                PayPalException::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED,
                'Your payment information could not be verified. Please try a different payment method.',
            ],
            'DUPLICATE_INVOICE_ID' => [
                PayPalException::DUPLICATE_INVOICE_ID,
                'This order has already been processed. Please check your order history.',
            ],
            'ORDER_NOT_APPROVED' => [
                PayPalException::ORDER_NOT_APPROVED,
                'The payment was not approved. Please try again.',
            ],
            'ORDER_EXPIRED' => [
                PayPalException::ORDER_EXPIRED,
                'Your payment session has expired. Please try again.',
            ],
            'ORDER_ALREADY_AUTHORIZED' => [
                PayPalException::ORDER_ALREADY_AUTHORIZED,
                'This order has already been authorized.',
            ],
            'DOMESTIC_TRANSACTION_REQUIRED' => [
                PayPalException::DOMESTIC_TRANSACTION_REQUIRED,
                'This payment method is not available for international transactions. Please try another payment method.',
            ],
            'TRANSACTION_LIMIT_EXCEEDED' => [
                PayPalException::TRANSACTION_LIMIT_EXCEEDED,
                'The transaction limit has been exceeded. Please try a smaller amount or another payment method.',
            ],
        ];
    }

    /**
     * @return array<string, array{int, string}>
     */
    public function provideCriticalPayPalExceptionCodes(): array
    {
        return [
            'PAYEE_ACCOUNT_LOCKED_OR_CLOSED' => [
                PayPalException::PAYEE_ACCOUNT_LOCKED_OR_CLOSED,
                'Payment cannot be processed at the moment. Please contact our customer service.',
            ],
            'PAYEE_ACCOUNT_RESTRICTED' => [
                PayPalException::PAYEE_ACCOUNT_RESTRICTED,
                'Payment cannot be processed at the moment. Please contact our customer service.',
            ],
            'TRANSACTION_RECEIVING_LIMIT_EXCEEDED' => [
                PayPalException::TRANSACTION_RECEIVING_LIMIT_EXCEEDED,
                'Payment cannot be processed at the moment. Please contact our customer service.',
            ],
            'ORDER_ALREADY_CAPTURED' => [
                PayPalException::ORDER_ALREADY_CAPTURED,
                'Order is already captured.',
            ],
        ];
    }

    public function testUnrecognizedPayPalExceptionFallsBackToGenericCriticalPath(): void
    {
        $exception = new PayPalException('Some unknown error', 99999);

        $this->customerNotifyAction->expects($this->once())->method('execute');
        $this->logger->expects($this->once())->method('error');

        /** @var array{httpCode: int, status: bool, body: array{error: array{message: string}}} $result */
        $result = $this->handler->handle($exception, 'PAYPAL-ORDER-123');

        $this->assertSame(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame(
            'Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.',
            $result['body']['error']['message']
        );
    }

    public function testUnknownExceptionTypeFallsBackToGenericCriticalPath(): void
    {
        $exception = new \RuntimeException('Unexpected error', 500);

        $this->customerNotifyAction->expects($this->once())->method('execute');
        $this->logger->expects($this->once())->method('error');

        /** @var array{httpCode: int, status: bool, body: array{error: array{message: string}}} $result */
        $result = $this->handler->handle($exception, 'PAYPAL-ORDER-123');

        $this->assertSame(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame(
            'Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.',
            $result['body']['error']['message']
        );
    }

    /**
     * @dataProvider provideOrderCreateClientErrorCases
     */
    public function testHandleOrderCreateExceptionReturns400ForClientErrors(
        \Exception $exception,
        ?string $fundingSource,
        string $expectedMessage
    ): void {
        $this->logger->expects($this->once())->method('notice');
        $this->logger->expects($this->never())->method('error');

        /** @var array{httpCode: int, status: bool, body: array{error: array{message: string}}} $result */
        $result = $this->handler->handleOrderCreateException($exception, $fundingSource);

        $this->assertSame(400, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame($expectedMessage, $result['body']['error']['message']);
    }

    /**
     * @return array<string, array{\Exception, string|null, string}>
     */
    public function provideOrderCreateClientErrorCases(): array
    {
        return [
            'CART_SHIPPING_ADDRESS_INVALID' => [
                new PsCheckoutException('internal', PsCheckoutException::CART_SHIPPING_ADDRESS_INVALID),
                null,
                'There is an error in your shipping address. Please check it and try again.',
            ],
            'SHIPPING_ADDRESS_INVALID' => [
                new PayPalException('original', PayPalException::SHIPPING_ADDRESS_INVALID),
                null,
                'There is an error in your shipping address. Please check it and try again.',
            ],
            'BILLING_ADDRESS_INVALID' => [
                new PayPalException('original', PayPalException::BILLING_ADDRESS_INVALID),
                null,
                'There is an error in your billing address. Please check it and try again.',
            ],
            'PAYMENT_SOURCE_CANNOT_BE_USED' => [
                new PayPalException('original', PayPalException::PAYMENT_SOURCE_CANNOT_BE_USED),
                null,
                'The selected payment method does not support this type of transaction. Please choose another payment method or contact support for assistance.',
            ],
            'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED with PUI' => [
                new PayPalException('original', PayPalException::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED),
                'pay_upon_invoice',
                'The combination of your name and address could not be validated. Please correct your data and try again. You can find further information in the Ratepay Data Privacy Statement or you can contact Ratepay.',
            ],
            'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED without PUI' => [
                new PayPalException('original', PayPalException::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED),
                'card',
                'original',
            ],
            'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR with PUI' => [
                new PayPalException('original', PayPalException::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR),
                'pay_upon_invoice',
                'It is not possible to use the selected payment method. This decision is based on automated data processing. You can find further information in the Ratepay Data Privacy Statement or you can contact Ratepay.',
            ],
            'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR without PUI' => [
                new PayPalException('original', PayPalException::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR),
                'card',
                'original',
            ],
            'CART_CUSTOMER_PHONE_INVALID' => [
                new PsCheckoutException('internal', PsCheckoutException::CART_CUSTOMER_PHONE_INVALID),
                'card',
                'Your phone number is invalid or missing. Please update your contact details and try again.',
            ],
            'CART_CUSTOMER_EMAIL_INVALID' => [
                new PsCheckoutException('internal', PsCheckoutException::CART_CUSTOMER_EMAIL_INVALID),
                'card',
                'Your email address is invalid or missing. Please update your contact details and try again.',
            ],
            'CART_CUSTOMER_BIRTH_DATE_INVALID' => [
                new PsCheckoutException('internal', PsCheckoutException::CART_CUSTOMER_BIRTH_DATE_INVALID),
                'pay_upon_invoice',
                'Your date of birth is invalid or missing. Please check and try again.',
            ],
        ];
    }

    public function testCartAddressInvoiceInvalidWithoutPuiFallsBackToServerError(): void
    {
        $exception = new PsCheckoutException('internal', PsCheckoutException::CART_ADDRESS_INVOICE_INVALID);

        $this->logger->expects($this->never())->method('notice');
        $this->logger->expects($this->once())->method('error');

        /** @var array{httpCode: int, status: bool, body: array{error: array{message: string}}} $result */
        $result = $this->handler->handleOrderCreateException($exception, 'card');

        $this->assertSame(500, $result['httpCode']);
        $this->assertFalse($result['status']);
    }

    public function testHandleOrderCreateExceptionReturns500ForUnknownException(): void
    {
        $exception = new \RuntimeException('Something went wrong', 999);

        $this->logger->expects($this->never())->method('notice');
        $this->logger->expects($this->once())->method('error');

        /** @var array{httpCode: int, status: bool, body: array{error: array{message: string}}} $result */
        $result = $this->handler->handleOrderCreateException($exception, null);

        $this->assertSame(500, $result['httpCode']);
        $this->assertFalse($result['status']);
        $this->assertSame('Something went wrong', $result['body']['error']['message']);
    }
}
