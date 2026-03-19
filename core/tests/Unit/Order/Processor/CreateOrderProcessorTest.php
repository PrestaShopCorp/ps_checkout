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

namespace PsCheckout\Tests\Unit\Order\Processor;

use Cart;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Order\Action\CreateOrderActionInterface;
use PsCheckout\Core\Order\Processor\CreateOrderProcessor;
use PsCheckout\Core\Order\Request\ValueObject\ValidateOrderRequest;
use PsCheckout\Core\Order\Validator\CheckoutValidatorInterface;
use PsCheckout\Core\Order\Validator\OrderAuthorizationValidatorInterface;
use PsCheckout\Core\PaymentToken\Action\DeletePaymentTokenActionInterface;
use PsCheckout\Core\PaymentToken\Action\SavePaymentTokenActionInterface;
use PsCheckout\Core\PayPal\Order\Action\AuthorizePayPalOrderActionInterface;
use PsCheckout\Core\PayPal\Order\Action\CapturePayPalOrderActionInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\CartRepositoryInterface;

class CreateOrderProcessorTest extends TestCase
{
    /** @var CreateOrderProcessor */
    private $processor;

    /** @var OrderAuthorizationValidatorInterface|MockObject */
    private $orderAuthorizationValidator;

    /** @var CreateOrderActionInterface|MockObject */
    private $createOrderAction;

    /** @var CartRepositoryInterface|MockObject */
    private $cartRepository;

    /** @var ContextInterface|MockObject */
    private $context;

    /** @var CheckoutValidatorInterface|MockObject */
    private $checkoutValidator;

    /** @var CapturePayPalOrderActionInterface|MockObject */
    private $capturePayPalOrderAction;

    /** @var SavePaymentTokenActionInterface|MockObject */
    private $savePaymentTokenAction;

    /** @var PayPalOrderProviderInterface|MockObject */
    private $payPalOrderProvider;

    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $payPalOrderRepository;

    /** @var DeletePaymentTokenActionInterface|MockObject */
    private $deletePaymentTokenAction;

    /** @var AuthorizePayPalOrderActionInterface|MockObject */
    private $authorizePayPalOrderAction;

    protected function setUp(): void
    {
        $this->orderAuthorizationValidator = $this->createMock(OrderAuthorizationValidatorInterface::class);
        $this->createOrderAction = $this->createMock(CreateOrderActionInterface::class);
        $this->cartRepository = $this->getMockBuilder(CartRepositoryInterface::class)
            ->addMethods(['getOneBy'])
            ->getMock();
        $this->context = $this->createMock(ContextInterface::class);
        $this->checkoutValidator = $this->createMock(CheckoutValidatorInterface::class);
        $this->capturePayPalOrderAction = $this->createMock(CapturePayPalOrderActionInterface::class);
        $this->savePaymentTokenAction = $this->createMock(SavePaymentTokenActionInterface::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->deletePaymentTokenAction = $this->createMock(DeletePaymentTokenActionInterface::class);
        $this->authorizePayPalOrderAction = $this->createMock(AuthorizePayPalOrderActionInterface::class);

        $cart = $this->getMockBuilder(Cart::class)
            ->disableOriginalConstructor()
            ->getMock();
        $cart->id = 1;

        $this->cartRepository->method('getOneBy')->willReturn($cart);

        $this->processor = new CreateOrderProcessor(
            $this->orderAuthorizationValidator,
            $this->createOrderAction,
            $this->cartRepository,
            $this->context,
            $this->checkoutValidator,
            $this->capturePayPalOrderAction,
            $this->savePaymentTokenAction,
            $this->payPalOrderProvider,
            $this->payPalOrderRepository,
            $this->deletePaymentTokenAction,
            $this->authorizePayPalOrderAction
        );
    }

    public function testSavePaymentTokenIsCalledOnAuthorizeIntent(): void
    {
        $payPalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'COMPLETED',
            PayPalOrderIntent::AUTHORIZE,
            null,
            null,
            [],
            []
        );

        $authorizedResponse = new PayPalOrderResponse(
            'ORDER-123',
            'COMPLETED',
            PayPalOrderIntent::AUTHORIZE,
            null,
            ['card' => ['vault' => ['id' => 'VAULT-1', 'status' => 'VAULTED']]],
            [],
            []
        );

        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);

        $this->authorizePayPalOrderAction->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse)
            ->willReturn($authorizedResponse);

        $this->capturePayPalOrderAction->expects($this->never())
            ->method('execute');

        $this->savePaymentTokenAction->expects($this->once())
            ->method('execute')
            ->with($authorizedResponse);

        $request = new ValidateOrderRequest(['orderID' => 'ORDER-123'], 1);

        $this->processor->run($request);
    }

    public function testSavePaymentTokenIsCalledOnCaptureIntent(): void
    {
        $payPalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'COMPLETED',
            PayPalOrderIntent::CAPTURE,
            null,
            null,
            [],
            []
        );

        $capturedResponse = new PayPalOrderResponse(
            'ORDER-123',
            'COMPLETED',
            PayPalOrderIntent::CAPTURE,
            null,
            ['card' => ['vault' => ['id' => 'VAULT-1', 'status' => 'VAULTED']]],
            [],
            []
        );

        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);

        $this->capturePayPalOrderAction->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse)
            ->willReturn($capturedResponse);

        $this->authorizePayPalOrderAction->expects($this->never())
            ->method('execute');

        $this->savePaymentTokenAction->expects($this->once())
            ->method('execute')
            ->with($capturedResponse);

        $request = new ValidateOrderRequest(['orderID' => 'ORDER-123'], 1);

        $this->processor->run($request);
    }

    public function testSavePaymentTokenIsNotCalledOnOrderNotApprovedError(): void
    {
        $payPalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'CREATED',
            PayPalOrderIntent::CAPTURE,
            null,
            null,
            [],
            []
        );

        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);

        $this->capturePayPalOrderAction->expects($this->once())
            ->method('execute')
            ->willThrowException(new PayPalException('Order not approved', PayPalException::ORDER_NOT_APPROVED));

        $this->savePaymentTokenAction->expects($this->never())
            ->method('execute');

        $this->createOrderAction->expects($this->once())
            ->method('execute')
            ->with($payPalOrderResponse);

        $request = new ValidateOrderRequest(['orderID' => 'ORDER-123'], 1);

        $this->processor->run($request);
    }
}
