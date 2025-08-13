<?php

namespace PsCheckout\Tests\Unit\PayPal\Order\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Exception\PayPalOrderException;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProvider;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PayPalOrderProviderTest extends TestCase
{
    /** @var PayPalOrderProvider */
    private $provider;

    /** @var ConfigurationInterface|MockObject */
    private $configuration;

    /** @var PayPalOrderCacheInterface|MockObject */
    private $orderPayPalCache;

    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $payPalOrderRepository;

    /** @var OrderHttpClientInterface|MockObject */
    private $orderHttpClient;

    protected function setUp(): void
    {
        $this->configuration = $this->createMock(ConfigurationInterface::class);

        $this->orderPayPalCache = $this->createMock(PayPalOrderCacheInterface::class);

        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->orderHttpClient = $this->createMock(OrderHttpClientInterface::class);

        $this->provider = new PayPalOrderProvider(
            $this->configuration,
            $this->orderPayPalCache,
            $this->payPalOrderRepository,
            $this->orderHttpClient
        );
    }

    public function testItThrowsExceptionWhenOrderIdIsEmpty(): void
    {
        $this->expectException(PayPalOrderException::class);
        $this->expectExceptionMessage('Paypal order id is not provided');
        $this->expectExceptionCode(PayPalOrderException::INVALID_ID);

        $this->provider->getById('');
    }

    public function testItReturnsOrderFromCacheWhenCompleted(): void
    {
        $orderId = 'ORDER-123';
        $cachedData = [
            'id' => $orderId,
            'status' => 'COMPLETED',
            'intent' => 'CAPTURE',
            'purchase_units' => [],
            'links' => [],
            'create_time' => '2024-01-01T00:00:00Z',
        ];

        $this->orderPayPalCache->expects($this->once())
            ->method('has')
            ->with($orderId)
            ->willReturn(true);

        $this->orderPayPalCache->expects($this->once())
            ->method('getValue')
            ->with($orderId)
            ->willReturn($cachedData);

        $result = $this->provider->getById($orderId);

        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
        $this->assertEquals($orderId, $result->getId());
        $this->assertEquals('COMPLETED', $result->getStatus());
    }

    public function testItFetchesOrderWhenNotInCache(): void
    {
        $orderId = 'ORDER-123';
        $orderData = [
            'id' => $orderId,
            'status' => 'PENDING',
            'intent' => 'CAPTURE',
            'purchase_units' => [],
            'links' => [],
            'create_time' => '2024-01-01T00:00:00Z',
        ];

        $this->orderPayPalCache->method('has')
            ->with($orderId)
            ->willReturn(false);

        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn(json_encode($orderData));

        $this->orderHttpClient->expects($this->once())
            ->method('fetchOrder')
            ->with(['orderId' => $orderId])
            ->willReturn($response);

        $this->orderPayPalCache->expects($this->once())
            ->method('set')
            ->with($orderId, $orderData);

        $result = $this->provider->getById($orderId);

        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
        $this->assertEquals($orderId, $result->getId());
        $this->assertEquals('PENDING', $result->getStatus());
    }

    public function testItHandlesVaultingOrder(): void
    {
        $orderId = 'ORDER-123';
        $merchantId = 'MERCHANT-123';

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('checkCustomerIntent')
            ->with(PayPalOrder::CUSTOMER_INTENT_USES_VAULTING)
            ->willReturn(true);

        $this->payPalOrderRepository->method('getOneBy')
            ->with(['id' => $orderId])
            ->willReturn($payPalOrder);

        $this->configuration->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
            ->willReturn($merchantId);

        $orderData = [
            'id' => $orderId,
            'status' => 'PENDING',
            'intent' => 'CAPTURE',
            'purchase_units' => [],
            'links' => [],
            'create_time' => '2024-01-01T00:00:00Z',
        ];

        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn(json_encode($orderData));

        $this->orderHttpClient->expects($this->once())
            ->method('fetchOrder')
            ->with([
                'orderId' => $orderId,
                'vault' => true,
                'payee' => ['merchant_id' => $merchantId],
            ])
            ->willReturn($response);

        $result = $this->provider->getById($orderId);

        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
        $this->assertEquals($orderId, $result->getId());
    }

    public function testItThrowsExceptionWhenOrderNotFound(): void
    {
        $orderId = 'ORDER-123';

        $this->orderPayPalCache->method('has')->willReturn(false);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(404);
        $response->method('getBody')->willReturn('');

        $this->orderHttpClient->method('fetchOrder')
            ->willReturn($response);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order not found');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_ORDER_NOT_FOUND);

        $this->provider->getById($orderId);
    }
}
