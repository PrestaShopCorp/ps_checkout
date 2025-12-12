<?php

namespace PsCheckout\Core\Tests\Unit\PayPal\Order\Action;

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Action\AuthorizePayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Handler\EventHandlerInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class AuthorizePayPalOrderActionTest extends TestCase
{
    private $orderHttpClient;
    private $payPalOrderCache;
    private $paymentPendingEventHandler;
    private $paymentDeniedEventHandler;
    private $payPalOrderProvider;
    private $payPalOrderAuthorizationRepository;
    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderHttpClient = $this->createMock(OrderHttpClientInterface::class);
        $this->payPalOrderCache = $this->createMock(PayPalOrderCacheInterface::class);
        $this->paymentPendingEventHandler = $this->createMock(EventHandlerInterface::class);
        $this->paymentDeniedEventHandler = $this->createMock(EventHandlerInterface::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->payPalOrderAuthorizationRepository = $this->createMock(PayPalOrderAuthorizationRepositoryInterface::class);

        $this->action = new AuthorizePayPalOrderAction(
            $this->orderHttpClient,
            $this->payPalOrderCache,
            $this->paymentPendingEventHandler,
            $this->paymentDeniedEventHandler,
            $this->payPalOrderProvider,
            $this->payPalOrderAuthorizationRepository
        );
    }

    public function testSuccessfulAuthorizationWithCreatedStatus(): void
    {
        $payPalOrder = $this->createPayPalOrder('ORDER-123', PayPalOrderStatus::APPROVED);

        $authorizedOrderData = [
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'purchase_units' => [
                [
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-456',
                                'status' => PayPalAuthorizationStatus::CREATED,
                                'expiration_time' => '2099-12-31T23:59:59Z',
                                'create_time' => '2025-01-01T00:00:00Z',
                                'update_time' => '2025-01-01T00:00:00Z'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->orderHttpClient->expects($this->once())
            ->method('authorizeOrder')
            ->with('ORDER-123', [])
            ->willReturn($this->createHttpResponse($authorizedOrderData));

        $this->payPalOrderCache->expects($this->once())
            ->method('getValue')
            ->with('ORDER-123')
            ->willReturn([]);

        $this->payPalOrderCache->expects($this->once())
            ->method('set')
            ->with('ORDER-123', $authorizedOrderData);

        $authorizedResponse = PayPalOrderResponseFactory::create($authorizedOrderData);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->with('ORDER-123')
            ->willReturn($authorizedResponse);

        $this->payPalOrderAuthorizationRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($entity) {
                return $entity instanceof PayPalOrderAuthorization
                    && $entity->getId() === 'AUTH-456'
                    && $entity->getIdOrder() === 'ORDER-123'
                    && $entity->getStatus() === PayPalAuthorizationStatus::CREATED;
            }));

        $this->paymentPendingEventHandler->expects($this->once())
            ->method('handle')
            ->with($authorizedResponse);

        $this->paymentDeniedEventHandler->expects($this->never())
            ->method('handle');

        $result = $this->action->execute($payPalOrder);

        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
        $this->assertEquals('ORDER-123', $result->getId());
    }

    public function testSuccessfulAuthorizationWithPendingStatus(): void
    {
        $payPalOrder = $this->createPayPalOrder('ORDER-123', PayPalOrderStatus::APPROVED);

        $authorizedOrderData = [
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'purchase_units' => [
                [
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-456',
                                'status' => PayPalAuthorizationStatus::PENDING,
                                'expiration_time' => '2099-12-31T23:59:59Z',
                                'create_time' => '2025-01-01T00:00:00Z',
                                'update_time' => '2025-01-01T00:00:00Z'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->orderHttpClient->expects($this->once())
            ->method('authorizeOrder')
            ->willReturn($this->createHttpResponse($authorizedOrderData));

        $this->payPalOrderCache->method('getValue')->willReturn([]);
        $this->payPalOrderCache->expects($this->once())->method('set');

        $authorizedResponse = PayPalOrderResponseFactory::create($authorizedOrderData);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($authorizedResponse);

        $this->payPalOrderAuthorizationRepository->expects($this->once())->method('save');

        $this->paymentPendingEventHandler->expects($this->once())
            ->method('handle')
            ->with($authorizedResponse);

        $result = $this->action->execute($payPalOrder);
        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
    }

    public function testAuthorizationWithDeniedStatus(): void
    {
        $payPalOrder = $this->createPayPalOrder('ORDER-123', PayPalOrderStatus::APPROVED);

        $authorizedOrderData = [
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'purchase_units' => [
                [
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-456',
                                'status' => PayPalAuthorizationStatus::DENIED,
                                'expiration_time' => '2099-12-31T23:59:59Z',
                                'create_time' => '2025-01-01T00:00:00Z',
                                'update_time' => '2025-01-01T00:00:00Z'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->orderHttpClient->expects($this->once())
            ->method('authorizeOrder')
            ->willReturn($this->createHttpResponse($authorizedOrderData));

        $this->payPalOrderCache->method('getValue')->willReturn([]);
        $this->payPalOrderCache->expects($this->once())->method('set');

        $authorizedResponse = PayPalOrderResponseFactory::create($authorizedOrderData);

        $this->payPalOrderProvider->expects($this->once())
            ->method('getById')
            ->willReturn($authorizedResponse);

        $this->payPalOrderAuthorizationRepository->expects($this->once())->method('save');

        $this->paymentPendingEventHandler->expects($this->never())
            ->method('handle');

        $this->paymentDeniedEventHandler->expects($this->once())
            ->method('handle')
            ->with($authorizedResponse);

        $result = $this->action->execute($payPalOrder);
        $this->assertInstanceOf(PayPalOrderResponse::class, $result);
    }

    public function testThrowsExceptionWhenOrderStatusNotApproved(): void
    {
        $payPalOrder = $this->createPayPalOrder('ORDER-123', PayPalOrderStatus::CREATED);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order ORDER-123 status must be APPROVED, current status: CREATED');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_ORDER_STATUS_INVALID);

        $this->action->execute($payPalOrder);
    }

    public function testCachesOrderCorrectly(): void
    {
        $payPalOrder = $this->createPayPalOrder('ORDER-123', PayPalOrderStatus::APPROVED);

        $cachedData = [
            'id' => 'ORDER-123',
            'existing_field' => 'existing_value'
        ];

        $authorizedOrderData = [
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'new_field' => 'new_value',
            'purchase_units' => [
                [
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-456',
                                'status' => PayPalAuthorizationStatus::CREATED,
                                'expiration_time' => '2099-12-31T23:59:59Z',
                                'create_time' => '2025-01-01T00:00:00Z',
                                'update_time' => '2025-01-01T00:00:00Z'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->orderHttpClient->expects($this->once())
            ->method('authorizeOrder')
            ->willReturn($this->createHttpResponse($authorizedOrderData));

        $this->payPalOrderCache->expects($this->once())
            ->method('getValue')
            ->with('ORDER-123')
            ->willReturn($cachedData);

        $this->payPalOrderCache->expects($this->once())
            ->method('set')
            ->with(
                'ORDER-123',
                $this->callback(function ($data) {
                    return $data['id'] === 'ORDER-123'
                        && $data['existing_field'] === 'existing_value'
                        && $data['new_field'] === 'new_value';
                })
            );

        $authorizedResponse = PayPalOrderResponseFactory::create($authorizedOrderData);
        $this->payPalOrderProvider->method('getById')->willReturn($authorizedResponse);
        $this->payPalOrderAuthorizationRepository->expects($this->once())->method('save');

        $this->action->execute($payPalOrder);
    }

    public function testSavesAuthorizationEntityWithAllFields(): void
    {
        $payPalOrder = $this->createPayPalOrder('ORDER-123', PayPalOrderStatus::APPROVED);

        $authorizedOrderData = [
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'purchase_units' => [
                [
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-789',
                                'status' => PayPalAuthorizationStatus::CREATED,
                                'expiration_time' => '2099-12-31T23:59:59Z',
                                'create_time' => '2025-01-15T10:30:00Z',
                                'update_time' => '2025-01-15T10:30:00Z'
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->orderHttpClient->method('authorizeOrder')
            ->willReturn($this->createHttpResponse($authorizedOrderData));

        $this->payPalOrderCache->method('getValue')->willReturn([]);
        $this->payPalOrderCache->method('set');

        $authorizedResponse = PayPalOrderResponseFactory::create($authorizedOrderData);
        $this->payPalOrderProvider->method('getById')->willReturn($authorizedResponse);

        $this->payPalOrderAuthorizationRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($entity) {
                return $entity instanceof PayPalOrderAuthorization
                    && $entity->getId() === 'AUTH-789'
                    && $entity->getIdOrder() === 'ORDER-123'
                    && $entity->getStatus() === PayPalAuthorizationStatus::CREATED
                    && $entity->getExpirationTime() === '2099-12-31T23:59:59Z'
                    && $entity->getCreateTime() === '2025-01-15T10:30:00Z'
                    && $entity->getUpdateTime() === '2025-01-15T10:30:00Z';
            }));

        $this->action->execute($payPalOrder);
    }

    private function createPayPalOrder(string $orderId, string $status): PayPalOrderResponse
    {
        return PayPalOrderResponseFactory::create([
            'id' => $orderId,
            'status' => $status,
            'intent' => 'AUTHORIZE'
        ]);
    }

    private function createHttpResponse(array $data): ResponseInterface
    {
        $body = $this->createMock(StreamInterface::class);
        $body->method('__toString')->willReturn(json_encode($data));
        $body->method('getContents')->willReturn(json_encode($data));

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($body);

        return $response;
    }
}
