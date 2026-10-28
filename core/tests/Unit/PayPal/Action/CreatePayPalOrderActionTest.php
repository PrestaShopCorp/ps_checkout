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

namespace PsCheckout\Core\Tests\Unit\PayPal\Order\Action;

use Exception;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PaymentToken\Action\DeletePaymentTokenActionInterface;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PaymentToken\ValueObject\PaymentToken;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Action\CreatePayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Processor\CreatePayPalOrderProcessorInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CreatePayPalOrderRequest;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class CreatePayPalOrderActionTest extends TestCase
{
    /** @var OrderHttpClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $orderHttpClient;

    /** @var CreatePayPalOrderAction */
    private $action;

    /** @var ContextInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $context;

    /** @var PayPalCustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $payPalCustomerRepository;

    /** @var PaymentTokenRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentTokenRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderHttpClient = $this->createMock(OrderHttpClientInterface::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->payPalCustomerRepository = $this->createMock(PayPalCustomerRepositoryInterface::class);
        $this->paymentTokenRepository = $this->createMock(PaymentTokenRepositoryInterface::class);
        $this->action = new CreatePayPalOrderAction(
            $this->context,
            $this->payPalCustomerRepository,
            $this->createMock(PayPalOrderRepositoryInterface::class),
            $this->orderHttpClient,
            $this->createMock(OrderPayloadBuilderInterface::class),
            $this->createMock(PresenterInterface::class),
            $this->createMock(CreatePayPalOrderProcessorInterface::class),
            $this->createMock(PayPalOrderCacheInterface::class),
            $this->createMock(DeletePaymentTokenActionInterface::class),
            $this->paymentTokenRepository
        );
    }

    private function createPayPalOrder(): void
    {
        $method = new \ReflectionMethod(CreatePayPalOrderAction::class, 'createPayPalOrder');
        $method->setAccessible(true);

        $method->invoke($this->action, ['intent' => 'CAPTURE']);
    }

    public function testShopNotRegisteredInMduIsNotWrappedSoThatItKeepsItsDedicatedHandling(): void
    {
        $mduException = new PsCheckoutException(
            'Shop is not registered in the PrestaShop Checkout services.',
            PsCheckoutException::SHOP_NOT_REGISTERED_IN_MDU
        );
        $this->orderHttpClient->method('createOrder')->willThrowException($mduException);

        try {
            $this->createPayPalOrder();
            $this->fail('An exception was expected');
        } catch (PsCheckoutException $exception) {
            $this->assertSame($mduException, $exception);
            $this->assertSame(PsCheckoutException::SHOP_NOT_REGISTERED_IN_MDU, $exception->getCode());
        }
    }

    public function testOtherErrorsAreStillWrappedAsUnknown(): void
    {
        $cause = new Exception('Connection reset');
        $this->orderHttpClient->method('createOrder')->willThrowException($cause);

        try {
            $this->createPayPalOrder();
            $this->fail('An exception was expected');
        } catch (PsCheckoutException $exception) {
            $this->assertSame(PsCheckoutException::UNKNOWN, $exception->getCode());
            $this->assertSame('Failed to create order', $exception->getMessage());
            $this->assertSame($cause, $exception->getPrevious());
        }
    }

    public function testOtherPsCheckoutExceptionsAreStillWrappedAsUnknown(): void
    {
        $cause = new PsCheckoutException('Something else', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
        $this->orderHttpClient->method('createOrder')->willThrowException($cause);

        try {
            $this->createPayPalOrder();
            $this->fail('An exception was expected');
        } catch (PsCheckoutException $exception) {
            $this->assertSame(PsCheckoutException::UNKNOWN, $exception->getCode());
            $this->assertSame($cause, $exception->getPrevious());
        }
    }

    /**
     * @return array<string, array{int, string|null, PaymentToken|null}>
     */
    public function invalidVaultOwnershipProvider(): array
    {
        $tokenOfAnotherCustomer = $this->createMock(PaymentToken::class);
        $tokenOfAnotherCustomer->method('getPayPalCustomerId')->willReturn('OTHER_PAYPAL_CUSTOMER');

        return [
            'unknown token' => [1, 'PAYPAL_CUSTOMER', null],
            'token of another customer' => [1, 'PAYPAL_CUSTOMER', $tokenOfAnotherCustomer],
            'guest without PayPal customer' => [0, null, $tokenOfAnotherCustomer],
            'customer without PayPal customer' => [1, null, $tokenOfAnotherCustomer],
        ];
    }

    /**
     * @dataProvider invalidVaultOwnershipProvider
     */
    public function testVaultIdOwnershipIsCheckedBeforeAnyPayPalCall(int $customerId, ?string $payPalCustomerId, ?PaymentToken $paymentToken): void
    {
        $this->context->method('getCustomer')->willReturn((object) ['id' => $customerId]);
        $this->payPalCustomerRepository->method('getPayPalCustomerIdByCustomerId')->willReturn($payPalCustomerId);
        $this->paymentTokenRepository->method('getOneById')->with('VAULT_ID')->willReturn($paymentToken);

        $this->orderHttpClient->expects($this->never())->method('createOrder');

        try {
            $this->action->execute(1, new CreatePayPalOrderRequest([
                'vaultId' => 'VAULT_ID',
                'fundingSource' => 'card',
                'isExpressCheckout' => false,
            ]));
            $this->fail('An exception was expected');
        } catch (PsCheckoutException $exception) {
            $this->assertSame('Payment token does not belong to the customer', $exception->getMessage());
        }
    }
}
