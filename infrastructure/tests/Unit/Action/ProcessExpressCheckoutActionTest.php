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

namespace Tests\Unit\PsCheckout\Infrastructure\Action;

use Cart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Core\Customer\Action\ExpressCheckoutActionInterface;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Action\ProcessExpressCheckoutAction;
use PsCheckout\Infrastructure\Repository\AddressRepositoryInterface;
use PsCheckout\Infrastructure\Action\SaveExpressCheckoutFlagsAction;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Validator\FrontControllerValidatorInterface;
use PsCheckout\Utility\Common\InputStreamUtility;

class ProcessExpressCheckoutActionTest extends TestCase
{
    /** @var FrontControllerValidatorInterface|MockObject */
    private $validator;

    /** @var InputStreamUtility|MockObject */
    private $inputStreamUtility;

    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $payPalOrderRepository;

    /** @var OrderHttpClientInterface|MockObject */
    private $orderHttpClient;

    /** @var SaveExpressCheckoutFlagsAction|MockObject */
    private $saveExpressCheckoutFlagsAction;

    /** @var ExpressCheckoutActionInterface|MockObject */
    private $expressCheckoutAction;

    /** @var ContextInterface|MockObject */
    private $context;

    /** @var AddressRepositoryInterface|MockObject */
    private $addressRepository;

    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var ProcessExpressCheckoutAction */
    private $action;

    protected function setUp(): void
    {
        $this->validator = $this->createMock(FrontControllerValidatorInterface::class);
        $this->inputStreamUtility = $this->createMock(InputStreamUtility::class);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->orderHttpClient = $this->createMock(OrderHttpClientInterface::class);
        $this->saveExpressCheckoutFlagsAction = $this->createMock(SaveExpressCheckoutFlagsAction::class);
        $this->expressCheckoutAction = $this->createMock(ExpressCheckoutActionInterface::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->addressRepository = $this->createMock(AddressRepositoryInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->action = new ProcessExpressCheckoutAction(
            $this->validator,
            $this->inputStreamUtility,
            $this->payPalOrderRepository,
            $this->orderHttpClient,
            $this->saveExpressCheckoutFlagsAction,
            $this->expressCheckoutAction,
            $this->context,
            $this->addressRepository,
            $this->logger
        );
    }

    // -------------------------------------------------------------------------
    // Guard — express checkout not enabled
    // -------------------------------------------------------------------------

    public function testThrowsNotEnabledWhenExpressCheckoutDisabled(): void
    {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(false);
        $this->inputStreamUtility->expects($this->never())->method('getBodyContent');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_NOT_ENABLED);

        $this->action->execute();
    }

    // -------------------------------------------------------------------------
    // Guard — payload validation
    // -------------------------------------------------------------------------

    public function testThrowsInvalidPayloadWhenBodyIsEmpty(): void
    {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(true);
        $this->inputStreamUtility->method('getBodyContent')->willReturn('');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD);

        $this->action->execute();
    }

    public function testThrowsInvalidPayloadWhenBodyIsInvalidJson(): void
    {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(true);
        $this->inputStreamUtility->method('getBodyContent')->willReturn('not-valid-json');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD);

        $this->action->execute();
    }

    public function testThrowsInvalidPayloadWhenOrderIdMissing(): void
    {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(true);
        $this->inputStreamUtility->method('getBodyContent')->willReturn(
            json_encode(['fundingSource' => 'paypal'])
        );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD);

        $this->action->execute();
    }

    // -------------------------------------------------------------------------
    // Guard — PayPal order / cart validation
    // -------------------------------------------------------------------------

    public function testThrowsInvalidPayloadWhenPayPalOrderNotFound(): void
    {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(true);
        $this->inputStreamUtility->method('getBodyContent')->willReturn(
            json_encode(['orderID' => 'ORDER-1', 'fundingSource' => 'paypal'])
        );
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD);

        $this->action->execute();
    }

    public function testThrowsInvalidPayloadWhenCartIsNull(): void
    {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(true);
        $this->inputStreamUtility->method('getBodyContent')->willReturn(
            json_encode(['orderID' => 'ORDER-1', 'fundingSource' => 'paypal'])
        );

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getIdCart')->willReturn(1);
        $this->payPalOrderRepository->method('getOneBy')->willReturn($payPalOrder);
        $this->context->method('getCart')->willReturn(null);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD);

        $this->action->execute();
    }

    public function testThrowsInvalidPayloadWhenCartIdDoesNotMatchPayPalOrder(): void
    {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(true);
        $this->inputStreamUtility->method('getBodyContent')->willReturn(
            json_encode(['orderID' => 'ORDER-1', 'fundingSource' => 'paypal'])
        );

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getIdCart')->willReturn(5);
        $this->payPalOrderRepository->method('getOneBy')->willReturn($payPalOrder);

        $cart = $this->getMockBuilder(Cart::class)->disableOriginalConstructor()->getMock();
        $cart->id = 10;
        $this->context->method('getCart')->willReturn($cart);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_INVALID_PAYLOAD);

        $this->action->execute();
    }

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function testReturnsOrderIdAndFundingSourceOnSuccess(): void
    {
        $this->setUpValidGuards('ORDER-1', 'paypal', 1, 1);

        $result = $this->action->execute();

        $this->assertSame(['orderID' => 'ORDER-1', 'fundingSource' => 'paypal'], $result);
    }

    public function testDelegatesWithCorrectFlagsAndFundingSource(): void
    {
        $this->setUpValidGuards('ORDER-2', 'venmo', 7, 7);

        $this->saveExpressCheckoutFlagsAction->expects($this->once())
            ->method('execute')
            ->with('ORDER-2', 'venmo');

        $this->expressCheckoutAction->expects($this->once())
            ->method('execute');

        $this->action->execute();
    }

    public function testNullFundingSourceIsPassedThrough(): void
    {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(true);
        $this->inputStreamUtility->method('getBodyContent')->willReturn(
            json_encode(['orderID' => 'ORDER-3'])
        );
        $this->setUpPayPalOrderAndCart('ORDER-3', 2, 2);
        $this->setUpHttpResponse('ORDER-3');

        $this->saveExpressCheckoutFlagsAction->expects($this->once())
            ->method('execute')
            ->with('ORDER-3', null);

        $result = $this->action->execute();

        $this->assertNull($result['fundingSource']);
    }

    // -------------------------------------------------------------------------
    // Exception logging — fetchOrder failure is logged with paypal_order context
    // -------------------------------------------------------------------------

    public function testLogsOrderContextAndRethrowsWhenFetchOrderFails(): void
    {
        $this->setUpValidGuards('ORDER-1', 'paypal', 1, 1, false);

        $fetchException = new \Exception('HTTP timeout');
        $this->orderHttpClient->method('fetchOrder')->willThrowException($fetchException);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                $this->stringContains('ProcessExpressCheckoutAction'),
                $this->callback(function (array $context) {
                    return isset($context['paypal_order']) && $context['paypal_order'] === 'ORDER-1'
                        && isset($context['exception']);
                })
            );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('HTTP timeout');

        $this->action->execute();
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Configures all guards to pass and optionally sets up the HTTP mock.
     *
     * @param bool $withHttpResponse Whether to also set up a valid fetchOrder response
     */
    private function setUpValidGuards(
        string $orderID,
        ?string $fundingSource,
        int $payPalOrderCartId,
        int $currentCartId,
        bool $withHttpResponse = true
    ): void {
        $this->validator->method('isExpressCheckoutEnabled')->willReturn(true);
        $this->inputStreamUtility->method('getBodyContent')->willReturn(
            json_encode(['orderID' => $orderID, 'fundingSource' => $fundingSource])
        );
        $this->setUpPayPalOrderAndCart($orderID, $payPalOrderCartId, $currentCartId);

        if ($withHttpResponse) {
            $this->setUpHttpResponse($orderID);
        }
    }

    private function setUpPayPalOrderAndCart(string $orderID, int $payPalOrderCartId, int $currentCartId): void
    {
        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getIdCart')->willReturn($payPalOrderCartId);
        $this->payPalOrderRepository->method('getOneBy')
            ->with(['id' => $orderID])
            ->willReturn($payPalOrder);

        $cart = $this->getMockBuilder(Cart::class)->disableOriginalConstructor()->getMock();
        $cart->id = $currentCartId;
        $this->context->method('getCart')->willReturn($cart);
    }

    private function setUpHttpResponse(string $orderID): void
    {
        $body = json_encode([
            'id' => $orderID,
            'status' => 'APPROVED',
            'intent' => 'CAPTURE',
            'purchase_units' => [[]],
            'links' => [],
        ]);

        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        $this->orderHttpClient->method('fetchOrder')->willReturn($response);
    }
}
