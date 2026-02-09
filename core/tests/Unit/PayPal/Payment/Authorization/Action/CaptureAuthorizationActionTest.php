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

namespace PsCheckout\Core\Tests\Unit\PayPal\Payment\Authorization\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\PaymentHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Payment\Authorization\Action\CaptureAuthorizationAction;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CaptureAuthorizationActionTest extends TestCase
{
    /** @var PaymentHttpClientInterface|MockObject */
    private $paymentHttpClient;

    /** @var PayPalOrderAuthorizationRepositoryInterface|MockObject */
    private $authorizationRepository;

    /** @var CaptureAuthorizationAction */
    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentHttpClient = $this->createMock(PaymentHttpClientInterface::class);
        $this->authorizationRepository = $this->createMock(PayPalOrderAuthorizationRepositoryInterface::class);

        $this->action = new CaptureAuthorizationAction(
            $this->paymentHttpClient,
            $this->authorizationRepository
        );
    }

    public function testSuccessfulCaptureWithCreatedStatus(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CREATED,
                'expiration_time' => '2100-01-28T23:59:59Z',
                'seller_protection' => ['status' => 'ELIGIBLE'],
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $capturedAuthData = [
            'id' => 'AUTH-456',
            'status' => PayPalAuthorizationStatus::CAPTURED,
            'expiration_time' => '2099-12-31T23:59:59Z',
            'seller_protection' => ['status' => 'ELIGIBLE'],
        ];

        $this->paymentHttpClient->expects($this->once())
            ->method('captureAuthorization')
            ->with('AUTH-456')
            ->willReturn($this->createHttpResponse($capturedAuthData));

        $existingEntity = new PayPalOrderAuthorization(
            'AUTH-456',
            'ORDER-123',
            PayPalAuthorizationStatus::CREATED,
            '2099-12-31T23:59:59Z',
            '2025-01-01T00:00:00Z',
            '2025-01-01T00:00:00Z'
        );

        $this->authorizationRepository->expects($this->once())
            ->method('getById')
            ->with('AUTH-456')
            ->willReturn($existingEntity);

        $this->authorizationRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($entity) {
                return $entity instanceof PayPalOrderAuthorization
                    && $entity->getId() === 'AUTH-456'
                    && $entity->getStatus() === PayPalAuthorizationStatus::CAPTURED
                    && $entity->getIdOrder() === 'ORDER-123';
            }));

        $result = $this->action->execute($payPalOrder);

        $this->assertEquals('AUTH-456', $result->getId());
        $this->assertEquals(PayPalAuthorizationStatus::CAPTURED, $result->getStatus());
    }

    public function testSuccessfulCaptureUpdatesExistingAuthorization(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CREATED,
                'expiration_time' => '2099-12-31T23:59:59Z',
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $capturedAuthData = [
            'id' => 'AUTH-456',
            'status' => PayPalAuthorizationStatus::CAPTURED,
            'expiration_time' => '2099-12-31T23:59:59Z',
            'seller_protection' => [],
        ];

        $this->paymentHttpClient->expects($this->once())
            ->method('captureAuthorization')
            ->with('AUTH-456')
            ->willReturn($this->createHttpResponse($capturedAuthData));

        $existingEntity = new PayPalOrderAuthorization(
            'AUTH-456',
            'ORDER-123',
            PayPalAuthorizationStatus::CREATED,
            '2099-12-31T23:59:59Z',
            []
        );

        $this->authorizationRepository->expects($this->once())
            ->method('getById')
            ->with('AUTH-456')
            ->willReturn($existingEntity);

        $this->authorizationRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PayPalOrderAuthorization $entity) {
                return $entity->getStatus() === PayPalAuthorizationStatus::CAPTURED;
            }));

        $result = $this->action->execute($payPalOrder);

        $this->assertEquals(PayPalAuthorizationStatus::CAPTURED, $result->getStatus());
    }

    public function testSuccessfulCaptureWithPartiallycapturedStatus(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::PARTIALLY_CAPTURED,
                'expiration_time' => '2099-12-31T23:59:59Z',
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $this->paymentHttpClient->expects($this->once())
            ->method('captureAuthorization')
            ->willReturn($this->createHttpResponse([
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CAPTURED,
            ]));

        $existingEntity = new PayPalOrderAuthorization(
            'AUTH-456',
            'ORDER-123',
            PayPalAuthorizationStatus::PARTIALLY_CAPTURED,
            '2099-12-31T23:59:59Z',
            '2025-01-01T00:00:00Z',
            '2025-01-01T00:00:00Z'
        );

        $this->authorizationRepository->method('getById')->willReturn($existingEntity);
        $this->authorizationRepository->expects($this->once())->method('save');

        $result = $this->action->execute($payPalOrder);
        $this->assertEquals(PayPalAuthorizationStatus::CAPTURED, $result->getStatus());
    }

    public function testThrowsExceptionWhenOrderStatusNotApproved(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::CREATED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CREATED,
                'expiration_time' => '2020-01-01T00:00:00Z',
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order ORDER-123 status must be APPROVED, current status: CREATED');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_ORDER_STATUS_INVALID);

        $this->action->execute($payPalOrder);
    }

    public function testThrowsExceptionWhenIntentNotAuthorize(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'CAPTURE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CREATED,
                'expiration_time' => '2020-01-01T00:00:00Z',
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order ORDER-123 intent must be AUTHORIZE, current intent: CAPTURE');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_ORDER_INTENT_INVALID);

        $this->action->execute($payPalOrder);
    }

    public function testThrowsExceptionWhenAuthorizationNotFound(): void
    {
        $payPalOrder = PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => 'AUTHORIZE',
            'purchase_units' => [
                [
                    'payments' => [],
                ],
            ],
        ]);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order ORDER-123 does not have a valid authorization');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_AUTHORIZATION_NOT_FOUND);

        $this->action->execute($payPalOrder);
    }

    public function testThrowsExceptionWhenAuthorizationStatusInvalid(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CAPTURED,
                'expiration_time' => '2020-01-01T00:00:00Z',
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Authorization AUTH-456 status must be CREATED or PARTIALLY_CAPTURED, current status: CAPTURED');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_AUTHORIZATION_STATUS_INVALID);

        $this->action->execute($payPalOrder);
    }

    public function testThrowsExceptionWhenAuthorizationVoided(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::VOIDED,
                'expiration_time' => '2020-01-01T00:00:00Z',
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Authorization AUTH-456 is voided and cannot be captured');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_AUTHORIZATION_VOIDED);

        $this->action->execute($payPalOrder);
    }

    public function testThrowsExceptionWhenAuthorizationExpired(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CREATED,
                'expiration_time' => '2020-01-01T00:00:00Z',
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Authorization AUTH-456 has expired');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_AUTHORIZATION_EXPIRED);

        $this->action->execute($payPalOrder);
    }

    public function testDoesNotThrowExceptionWhenExpirationTimeInvalid(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CREATED,
                'expiration_time' => 'invalid-date',
                'create_time' => '2099-12-31T23:59:59Z',
                'update_time' => '2099-12-31T23:59:59Z',
            ]
        );

        $this->paymentHttpClient->expects($this->once())
            ->method('captureAuthorization')
            ->willReturn($this->createHttpResponse([
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CAPTURED,
            ]));

        $existingEntity = new PayPalOrderAuthorization(
            'AUTH-456',
            'ORDER-123',
            PayPalAuthorizationStatus::CREATED,
            'invalid-date',
            '2025-01-01T00:00:00Z',
            '2025-01-01T00:00:00Z'
        );

        $this->authorizationRepository->method('getById')->willReturn($existingEntity);
        $this->authorizationRepository->expects($this->once())->method('save');

        $result = $this->action->execute($payPalOrder);
        $this->assertEquals(PayPalAuthorizationStatus::CAPTURED, $result->getStatus());
    }

    /**
     * @param string $orderId
     * @param string $status
     * @param string $intent
     * @param array{
     *      id: string,
     *      status: string,
     *      expiration_time: string,
     *      create_time: string,
     *      update_time: string
     *  } $authorization
     *
     * @return PayPalOrderResponse
     */
    private function createPayPalOrder(
        string $orderId,
        string $status,
        string $intent,
        array $authorization
    ): PayPalOrderResponse {
        return PayPalOrderResponseFactory::create([
            'id' => $orderId,
            'status' => $status,
            'intent' => $intent,
            'purchase_units' => [
                [
                    'payments' => [
                        'authorizations' => [$authorization],
                    ],
                ],
            ],
        ]);
    }

    /**
     * @param array<mixed> $data
     *
     * @return ResponseInterface
     */
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
