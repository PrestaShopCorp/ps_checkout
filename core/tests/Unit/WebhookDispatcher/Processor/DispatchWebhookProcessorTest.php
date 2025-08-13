<?php

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PaymentToken\Action\SavePaymentTokenActionInterface;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Handler\PayPalEventDispatcherInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\WebhookDispatcher\Processor\DispatchWebhookProcessor;
use PsCheckout\Core\WebhookDispatcher\ValueObject\DispatchWebhookRequest;
use Psr\Log\LoggerInterface;

class DispatchWebhookProcessorTest extends TestCase
{
    private $logger;

    private $payPalOrderProvider;

    private $eventDispatcher;

    private $payPalOrderCache;

    private $savePaymentTokenAction;

    private $paymentTokenRepository;

    private $processor;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->eventDispatcher = $this->createMock(PayPalEventDispatcherInterface::class);
        $this->payPalOrderCache = $this->createMock(PayPalOrderCacheInterface::class);
        $this->savePaymentTokenAction = $this->createMock(SavePaymentTokenActionInterface::class);
        $this->paymentTokenRepository = $this->createMock(PaymentTokenRepositoryInterface::class);

        $this->processor = new DispatchWebhookProcessor(
            $this->logger,
            $this->payPalOrderProvider,
            $this->eventDispatcher,
            $this->payPalOrderCache,
            $this->savePaymentTokenAction,
            $this->paymentTokenRepository
        );
    }

    public function testProcessWithInvalidCategory(): void
    {
        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('InvalidCategory');

        $this->logger->expects($this->atLeastOnce())->method('info');

        $this->assertTrue($this->processor->process($request));
    }

    public function testProcessWithMissingOrderId(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('orderId must not be empty');

        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn(null);

        $this->processor->process($request);
    }

    public function testProcessWithNonexistentPayPalOrder(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal order not found');

        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');

        $this->payPalOrderCache->method('has')->willReturn(false);
        $this->payPalOrderProvider
        ->method('getById')
        ->will($this->throwException(new PsCheckoutException('PayPal order not found', PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY)));

        $this->processor->process($request);
    }

    public function testProcessWithValidOrderAndDispatchesEvent(): void
    {
        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getEventType')->willReturn('SomeEvent');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderCache->method('has')->willReturn(true);
        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with('SomeEvent', $payPalOrderResponse);
        $this->payPalOrderCache->expects($this->once())
        ->method('delete')
        ->with($this->equalTo('12345'));

        $this->assertTrue($this->processor->process($request));
    }

    public function testHandlePaymentTokenEventsForCreatedToken(): void
    {
        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getEventType')->willReturn('VaultPaymentTokenCreated');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->savePaymentTokenAction->expects($this->once())->method('execute')->with($payPalOrderResponse);

        $this->assertTrue($this->processor->process($request));
    }

    public function testHandlePaymentTokenEventsForDeletedToken(): void
    {
        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getEventType')->willReturn('VaultPaymentTokenDeleted');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);
        $payPalOrderResponse->method('getVault')->willReturn(['id' => 'token123']);

        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->paymentTokenRepository->expects($this->once())->method('delete')->with('token123');

        $this->assertTrue($this->processor->process($request));
    }
}
