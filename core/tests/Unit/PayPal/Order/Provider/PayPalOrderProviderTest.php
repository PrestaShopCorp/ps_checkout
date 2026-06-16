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

namespace PsCheckout\Tests\Unit\PayPal\Order\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Exception\PayPalOrderException;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use Psr\Log\LoggerInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PayPalOrderProviderTest extends TestCase
{
    /** @var PayPalOrderProvider */
    private $provider;

    /** @var PayPalOrderCacheInterface|MockObject */
    private $orderPayPalCache;

    /** @var OrderHttpClientInterface|MockObject */
    private $orderHttpClient;

    protected function setUp(): void
    {
        $this->orderPayPalCache = $this->createMock(PayPalOrderCacheInterface::class);
        $this->orderHttpClient = $this->createMock(OrderHttpClientInterface::class);

        $this->provider = new PayPalOrderProvider(
            $this->orderPayPalCache,
            $this->orderHttpClient,
            $this->createMock(LoggerInterface::class),
            $this->createMock(PayPalOrderRepositoryInterface::class)
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
            'intent' => PayPalOrderIntent::CAPTURE,
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
            'intent' => PayPalOrderIntent::CAPTURE,
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
            ->with($orderId)
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

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('checkCustomerIntent')
            ->with(PayPalOrder::CUSTOMER_INTENT_USES_VAULTING)
            ->willReturn(true);

        $orderData = [
            'id' => $orderId,
            'status' => 'PENDING',
            'intent' => PayPalOrderIntent::CAPTURE,
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
            ->with($orderId)
            ->willReturn($response);

        $result = $this->provider->getById($orderId);

        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
        $this->assertEquals($orderId, $result->getId());
    }

    /**
     * When a shipping tracking number is added, the PayPal add-tracker API response
     * contains `purchase_units` (with tracker details) but omits `status` and `intent`.
     * The provider must not throw a TypeError when building the value object from such data.
     */
    public function testItHandlesOrderResponseMissingStatusAndIntentAfterTrackingAdded(): void
    {
        $orderId = 'ORDER-123';
        // Simulates the PayPal response shape returned by POST /trackers after shipping tracking is added:
        // top-level `status` and `intent` are absent; only tracking-enriched purchase_units are present.
        $trackingApiResponseData = [
            'id' => $orderId,
            'purchase_units' => [
                [
                    'reference_id' => 'default',
                    'shipping' => [
                        'trackers' => [
                            ['id' => 'TRACKER-ID-123', 'status' => 'SHIPPED'],
                        ],
                    ],
                ],
            ],
            'links' => [],
        ];

        $this->orderPayPalCache->method('has')->willReturn(false);

        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn(json_encode($trackingApiResponseData));

        $this->orderHttpClient->expects($this->once())
            ->method('fetchOrder')
            ->with($orderId)
            ->willReturn($response);

        $result = $this->provider->getById($orderId);

        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
        $this->assertSame('', $result->getStatus());
        $this->assertSame('', $result->getIntent());
    }

    /**
     * When cached order data is merged with a fresh fetch that lacks `status`,
     * null values are stripped before merging so the cached status is preserved.
     */
    public function testItPreservesCachedStatusWhenFreshFetchHasNullStatus(): void
    {
        $orderId = 'ORDER-123';

        $cachedData = [
            'id' => $orderId,
            'status' => 'APPROVED',
            'intent' => PayPalOrderIntent::CAPTURE,
            'purchase_units' => [],
            'links' => [],
        ];

        // Fresh fetch from PayPal (e.g. after tracker was added) has no status/intent
        $freshResponseData = [
            'id' => $orderId,
            'status' => null,
            'intent' => null,
            'purchase_units' => [
                [
                    'shipping' => [
                        'trackers' => [
                            ['id' => 'TRACKER-ID-456', 'status' => 'SHIPPED'],
                        ],
                    ],
                ],
            ],
            'links' => [],
        ];

        $this->orderPayPalCache->method('has')
            ->with($orderId)
            ->willReturn(true);

        $this->orderPayPalCache->method('getValue')
            ->with($orderId)
            ->willReturn($cachedData);

        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);

        $response->method('getStatusCode')->willReturn(200);
        $response->method('getBody')->willReturn($stream);
        $stream->method('__toString')->willReturn(json_encode($freshResponseData));

        $this->orderHttpClient->expects($this->once())
            ->method('fetchOrder')
            ->with($orderId)
            ->willReturn($response);

        $result = $this->provider->getById($orderId);

        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
        $this->assertSame('APPROVED', $result->getStatus());
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
