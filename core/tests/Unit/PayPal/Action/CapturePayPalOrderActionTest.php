<?php

namespace PsCheckout\Core\Tests\Unit\PayPal\Order\Action;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Action\CapturePayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCache;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalCaptureStatus;
use PsCheckout\Core\PayPal\Order\Handler\EventHandlerInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use PsCheckout\Core\Tests\Integration\Response\CaptureOrderResponse;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CapturePayPalOrderActionTest extends TestCase
{
    private $configuration;

    private $payPalOrderRepository;

    private $orderHttpClient;

    private $payPalOrderCache;

    private $orderCompletedEventHandler;

    private $paymentPendingEventHandler;

    private $paymentCompletedEventHandler;

    private $paymentDeniedEventHandler;

    private $payPalOrderProvider;

    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->orderHttpClient = $this->createMock(OrderHttpClientInterface::class);
        $this->payPalOrderCache = $this->createMock(PayPalOrderCache::class);
        $this->orderCompletedEventHandler = $this->createMock(EventHandlerInterface::class);
        $this->paymentPendingEventHandler = $this->createMock(EventHandlerInterface::class);
        $this->paymentCompletedEventHandler = $this->createMock(EventHandlerInterface::class);
        $this->paymentDeniedEventHandler = $this->createMock(EventHandlerInterface::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);

        $this->action = new CapturePayPalOrderAction(
            $this->configuration,
            $this->payPalOrderRepository,
            $this->orderHttpClient,
            $this->payPalOrderCache,
            $this->orderCompletedEventHandler,
            $this->paymentPendingEventHandler,
            $this->paymentCompletedEventHandler,
            $this->paymentDeniedEventHandler,
            $this->payPalOrderProvider
        );
    }

    public function testSuccessfulCapture(): void
    {
        // Create test PayPalOrder
        $payPalOrder = PayPalOrderFactory::create([
            'id' => 'TEST-ORDER-123',
            'funding_source' => 'paypal',
        ]);

        // Create initial PayPalOrderResponse
        $initialResponse = PayPalOrderResponseFactory::create();

        // Setup repository expectations
        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'TEST-ORDER-123'])
            ->willReturn($payPalOrder);

        // Setup configuration expectation
        $this->configuration->expects($this->once())
            ->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
            ->willReturn('TEST_MERCHANT_ID');

        // Setup HTTP client response
        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('__toString')->willReturn(json_encode(CaptureOrderResponse::getSuccessResponse()));
        
        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('getBody')->willReturn($responseBody);
        
        $this->orderHttpClient->expects($this->once())
            ->method('captureOrder')
            ->willReturn($httpResponse);

        // Setup cache expectations
        $this->payPalOrderCache->expects($this->once())
            ->method('getValue')
            ->willReturn([]);
        
        $this->payPalOrderCache->expects($this->once())
            ->method('set');

        // Setup provider response
        $capturedResponse = PayPalOrderResponseFactory::create([
            'status' => PayPalCaptureStatus::COMPLETED
        ]);
        
        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($capturedResponse);

        // Setup event handler expectations
        $this->orderCompletedEventHandler->expects($this->once())
            ->method('handle')
            ->with($capturedResponse);
        
        $this->paymentCompletedEventHandler->expects($this->once())
            ->method('handle')
            ->with($capturedResponse);

        // Execute and verify
        $result = $this->action->execute($initialResponse);
        
        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
        $this->assertEquals(PayPalCaptureStatus::COMPLETED, $result->getStatus());
    }

    public function testCaptureDeclined(): void
    {
        $initialResponse = PayPalOrderResponseFactory::create();
        $payPalOrder = PayPalOrderFactory::create();

        $this->payPalOrderRepository->method('getOneBy')->willReturn($payPalOrder);
        $this->configuration->method('get')->willReturn('TEST_MERCHANT_ID');

        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('__toString')->willReturn(json_encode(CaptureOrderResponse::getSuccessResponse()));
        
        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('getBody')->willReturn($responseBody);
        
        $this->orderHttpClient->method('captureOrder')->willReturn($httpResponse);
        
        $this->payPalOrderCache->method('getValue')->willReturn([]);

        // Create a declined response instead of returning null
        $declinedResponse = PayPalOrderResponseFactory::create([
            'status' => PayPalCaptureStatus::DECLINED,
            'purchase_units' => [
                [
                    'payments' => [
                        'captures' => [
                            ['status' => PayPalCaptureStatus::DECLINED]
                        ]
                    ]
                ]
            ]
        ]);
        
        $this->payPalOrderProvider->method('getById')->willReturn($declinedResponse);
        
        $this->paymentDeniedEventHandler->expects($this->once())
            ->method('handle')
            ->with($declinedResponse);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal declined the capture');

        $this->action->execute($initialResponse);
    }

    public function testCapturePending(): void
    {
        $initialResponse = PayPalOrderResponseFactory::create();
        $payPalOrder = PayPalOrderFactory::create();

        $this->payPalOrderRepository->method('getOneBy')->willReturn($payPalOrder);
        
        $capturedResponse = PayPalOrderResponseFactory::create([
            'purchase_units' => [
                [
                    'payments' => [
                        'captures' => [
                            ['status' => PayPalCaptureStatus::PENDING]
                        ]
                    ]
                ]
            ]
        ]);

        $responseBody = $this->createMock(StreamInterface::class);
        $responseBody->method('__toString')->willReturn(json_encode(CaptureOrderResponse::getSuccessResponse()));
        
        $httpResponse = $this->createMock(ResponseInterface::class);
        $httpResponse->method('getBody')->willReturn($responseBody);
        
        $this->orderHttpClient->method('captureOrder')->willReturn($httpResponse);

        // Fix: Return empty array instead of null for cache->getValue()
        $this->payPalOrderCache->method('getValue')->willReturn([]);
        
        $this->payPalOrderProvider->method('getById')->willReturn($capturedResponse);

        $this->paymentPendingEventHandler->expects($this->once())
            ->method('handle')
            ->with($capturedResponse);

        $result = $this->action->execute($initialResponse);
        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
    }
}
