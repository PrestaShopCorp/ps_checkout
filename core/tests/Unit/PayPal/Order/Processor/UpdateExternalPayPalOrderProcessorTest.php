<?php

namespace PsCheckout\Tests\Unit\PayPal\Order\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Order\Builder\OrderPayloadBuilder;
use PsCheckout\Core\PayPal\Order\Action\UpdatePayPalOrderPurchaseUnitActionInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Exception\PayPalOrderException;
use PsCheckout\Core\PayPal\Order\Processor\UpdateExternalPayPalOrderProcessor;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CheckPayPalOrderRequest;
use PsCheckout\Presentation\Presenter\PresenterInterface;
use Psr\Http\Message\ResponseInterface;

class UpdateExternalPayPalOrderProcessorTest extends TestCase
{
    /** @var UpdateExternalPayPalOrderProcessor */
    private $processor;

    /** @var PayPalOrderProviderInterface|MockObject */
    private $paypalOrderProvider;

    /** @var PresenterInterface|MockObject */
    private $cartPresenter;

    /** @var OrderPayloadBuilder|MockObject */
    private $orderPayloadBuilder;

    /** @var OrderHttpClientInterface|MockObject */
    private $httpClient;

    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $paypalOrderRepository;

    /** @var PayPalOrderCacheInterface|MockObject */
    private $paypalOrderCache;

    /** @var UpdatePayPalOrderPurchaseUnitActionInterface|MockObject */
    private $updatePayPalOrderPurchaseUnit;

    protected function setUp(): void
    {
        $this->paypalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->cartPresenter = $this->createMock(PresenterInterface::class);
        $this->orderPayloadBuilder = $this->getMockBuilder(OrderPayloadBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->httpClient = $this->createMock(OrderHttpClientInterface::class);
        $this->paypalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->paypalOrderCache = $this->createMock(PayPalOrderCacheInterface::class);
        $this->updatePayPalOrderPurchaseUnit = $this->createMock(UpdatePayPalOrderPurchaseUnitActionInterface::class);

        $this->processor = new UpdateExternalPayPalOrderProcessor(
            $this->paypalOrderProvider,
            $this->cartPresenter,
            $this->orderPayloadBuilder,
            $this->httpClient,
            $this->paypalOrderRepository,
            $this->paypalOrderCache,
            $this->updatePayPalOrderPurchaseUnit
        );
    }

    public function testItReturnsEarlyWhenPayPalOrderNotFound(): void
    {
        $request = $this->createMock(CheckPayPalOrderRequest::class);
        $request->method('getOrderId')->willReturn('ORDER-123');

        $this->paypalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn(null);

        $this->paypalOrderProvider->expects($this->never())->method('getById');

        $this->processor->execute($request);
    }

    public function testItReturnsEarlyWhenNoPurchaseUnits(): void
    {
        $request = $this->createMock(CheckPayPalOrderRequest::class);
        $request->method('getOrderId')->willReturn('ORDER-123');

        $payPalOrder = $this->createMock(PayPalOrder::class);

        $this->paypalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->willReturn($payPalOrder);

        $paypalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'COMPLETED',
            'CAPTURE',
            null,
            null,
            [],
            [],
            '2024-01-01T00:00:00Z'
        );

        $this->paypalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($paypalOrderResponse);

        $this->orderPayloadBuilder->expects($this->never())->method('build');

        $this->processor->execute($request);
    }

    public function testItUpdatesOrderWhenChangesDetected(): void
    {
        $request = $this->createMock(CheckPayPalOrderRequest::class);
        $request->method('getOrderId')->willReturn('ORDER-123');
        $request->method('getFundingSource')->willReturn('card');
        $request->method('isHostedFields')->willReturn(true);
        $request->method('isExpressCheckout')->willReturn(false);

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->expects($this->once())->method('setStatus')->with('COMPLETED');
        $payPalOrder->expects($this->once())->method('setFundingSource')->with('card');
        $payPalOrder->expects($this->once())->method('setIsCardFields')->with(true);
        $payPalOrder->expects($this->once())->method('setIsExpressCheckout')->with(false);
        $payPalOrder->expects($this->once())->method('setPaymentSource');

        $this->paypalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->willReturn($payPalOrder);

        $paypalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'COMPLETED',
            'CAPTURE',
            null,
            ['card' => []],
            [
                [
                    'amount' => ['value' => '10.00'],
                    'items' => [['id' => '1']],
                    'shipping' => ['old_data'],
                ],
            ],
            [],
            '2024-01-01T00:00:00Z'
        );

        $this->paypalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($paypalOrderResponse);

        $this->cartPresenter->expects($this->once())
            ->method('present')
            ->willReturn(['cart_data']);

        $this->orderPayloadBuilder
            ->method('setCart')
            ->willReturnSelf();
        $this->orderPayloadBuilder
            ->method('setIsUpdate')
            ->willReturnSelf();
        $this->orderPayloadBuilder
            ->method('setPaypalOrderId')
            ->willReturnSelf();
        $this->orderPayloadBuilder
            ->method('setIsCard')
            ->willReturnSelf();
        $this->orderPayloadBuilder
            ->method('setIsExpressCheckout')
            ->willReturnSelf();

        $this->orderPayloadBuilder->expects($this->once())
            ->method('build')
            ->willReturn([
                'amount' => ['value' => '11.00'],
                'items' => [['id' => '2']],
                'shipping' => ['new_data'],
            ]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(204);

        $this->httpClient->expects($this->once())
            ->method('updateOrder')
            ->willReturn($response);

        $this->paypalOrderRepository->expects($this->once())
            ->method('save')
            ->with($payPalOrder);

        $this->updatePayPalOrderPurchaseUnit->expects($this->once())
            ->method('execute')
            ->with($paypalOrderResponse);

        $this->paypalOrderCache->expects($this->once())
            ->method('delete')
            ->with('ORDER-123');

        $this->processor->execute($request);
    }

    public function testItThrowsExceptionWhenUpdateFails(): void
    {
        $request = $this->createMock(CheckPayPalOrderRequest::class);
        $request->method('getOrderId')->willReturn('ORDER-123');
        $request->method('getFundingSource')->willReturn('card');
        $request->method('isHostedFields')->willReturn(true);
        $request->method('isExpressCheckout')->willReturn(false);

        $payPalOrder = $this->createMock(PayPalOrder::class);

        $this->paypalOrderRepository->method('getOneBy')->willReturn($payPalOrder);

        $paypalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'PENDING',
            'CAPTURE',
            null,
            ['card' => []],
            [
                [
                    'amount' => ['value' => '10.00'],
                    'shipping' => ['old_data'],
                ],
            ],
            [],
            '2024-01-01T00:00:00Z'
        );

        $this->paypalOrderProvider->method('getById')->willReturn($paypalOrderResponse);

        $this->cartPresenter->method('present')->willReturn(['cart_data']);

        // Configure the builder to return itself for all chainable methods
        $this->orderPayloadBuilder->expects($this->once())
            ->method('setCart')
            ->with(['cart_data'])
            ->willReturnSelf();
        $this->orderPayloadBuilder->expects($this->once())
            ->method('setIsUpdate')
            ->with(true)
            ->willReturnSelf();
        $this->orderPayloadBuilder->expects($this->once())
            ->method('setPaypalOrderId')
            ->with('ORDER-123')
            ->willReturnSelf();
        $this->orderPayloadBuilder->expects($this->once())
            ->method('setIsCard')
            ->with(true)
            ->willReturnSelf();
        $this->orderPayloadBuilder->expects($this->once())
            ->method('setIsExpressCheckout')
            ->with(false)
            ->willReturnSelf();
        $this->orderPayloadBuilder->expects($this->once())
            ->method('build')
            ->willReturn([
                'amount' => ['value' => '11.00'],
            ]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $this->httpClient->method('updateOrder')->willReturn($response);

        $this->expectException(PayPalOrderException::class);
        $this->expectExceptionMessage('Failed to update PayPal Order');
        $this->expectExceptionCode(PayPalOrderException::PAYPAL_ORDER_UPDATE_FAILED);

        $this->processor->execute($request);
    }
}
