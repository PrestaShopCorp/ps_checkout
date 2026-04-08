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

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PsCheckout\Api\Http\PaymentHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Payment\Authorization\Action\VoidAuthorizationAction;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class VoidAuthorizationActionTest extends TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    /** @var MockObject|PaymentHttpClientInterface */
    private $paymentHttpClient;

    /** @var MockObject|PayPalOrderAuthorizationRepositoryInterface */
    private $authorizationRepository;

    /** @var VoidAuthorizationAction */
    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->logger = $this->createMock(LoggerInterface::class);
        $this->paymentHttpClient = $this->createMock(PaymentHttpClientInterface::class);
        $this->authorizationRepository = $this->createMock(PayPalOrderAuthorizationRepositoryInterface::class);

        $this->action = new VoidAuthorizationAction(
            $this->logger,
            $this->paymentHttpClient,
            $this->authorizationRepository
        );
    }

    public function testSuccessfulVoidUpdatesExistingAuthorization(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::CREATED,
                'expiration_time' => '2099-12-31T23:59:59Z'
            ]
        );

        $voidedAuthData = [
            'id' => 'AUTH-456',
            'status' => PayPalAuthorizationStatus::VOIDED,
            'update_time' => '2025-12-16T10:00:00Z'
        ];

        $this->paymentHttpClient->expects($this->once())
            ->method('voidAuthorization')
            ->with('AUTH-456')
            ->willReturn($this->createHttpResponse($voidedAuthData));

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
            ->with($this->callback(function (PayPalOrderAuthorization $entity): bool {
                return $entity->getStatus() === PayPalAuthorizationStatus::VOIDED
                    && $entity->getUpdateTime() === '2025-12-16T10:00:00Z';
            }));

        $result = $this->action->execute($payPalOrder);

        $this->assertEquals(PayPalAuthorizationStatus::VOIDED, $result->getStatus());
        $this->assertEquals('2025-12-16T10:00:00Z', $result->getUpdateTime());
    }

    public function testSuccessfulVoidWithPendingStatus(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::PENDING,
                'expiration_time' => '2099-12-31T23:59:59Z'
            ]
        );

        $this->paymentHttpClient->expects($this->once())
            ->method('voidAuthorization')
            ->willReturn($this->createHttpResponse([
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::VOIDED,
                'update_time' => '2025-12-16T10:00:00Z'
            ]));

        $existingEntity = new PayPalOrderAuthorization(
            'AUTH-456',
            'ORDER-123',
            PayPalAuthorizationStatus::PENDING,
            '2099-12-31T23:59:59Z',
            []
        );

        $this->authorizationRepository->method('getById')->willReturn($existingEntity);
        $this->authorizationRepository->expects($this->once())->method('save');

        $result = $this->action->execute($payPalOrder);
        $this->assertEquals(PayPalAuthorizationStatus::VOIDED, $result->getStatus());
    }

    public function testSuccessfulVoidWithPartiallyCapturedStatus(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::PARTIALLY_CAPTURED,
                'expiration_time' => '2099-12-31T23:59:59Z'
            ]
        );

        $this->paymentHttpClient->expects($this->once())
            ->method('voidAuthorization')
            ->willReturn($this->createHttpResponse([
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::VOIDED,
                'update_time' => '2025-12-16T10:00:00Z'
            ]));

        $existingEntity = new PayPalOrderAuthorization(
            'AUTH-456',
            'ORDER-123',
            PayPalAuthorizationStatus::PARTIALLY_CAPTURED,
            '2099-12-31T23:59:59Z',
            []
        );

        $this->authorizationRepository->method('getById')->willReturn($existingEntity);
        $this->authorizationRepository->expects($this->once())->method('save');

        $result = $this->action->execute($payPalOrder);
        $this->assertEquals(PayPalAuthorizationStatus::VOIDED, $result->getStatus());
    }

    public function testThrowsExceptionWhenIntentNotAuthorize(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'CAPTURE',
            ['id' => 'AUTH-456', 'status' => PayPalAuthorizationStatus::CREATED]
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
                    'payments' => []
                ]
            ]
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
                'status' => PayPalAuthorizationStatus::CAPTURED
            ]
        );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order Authorization AUTH-456 status must be PENDING, CREATED or PARTIALLY_CAPTURED , current status: CAPTURED');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_AUTHORIZATION_STATUS_INVALID);

        $this->action->execute($payPalOrder);
    }

    public function testThrowsExceptionWhenAuthorizationAlreadyVoided(): void
    {
        $payPalOrder = $this->createPayPalOrder(
            'ORDER-123',
            PayPalOrderStatus::APPROVED,
            'AUTHORIZE',
            [
                'id' => 'AUTH-456',
                'status' => PayPalAuthorizationStatus::VOIDED
            ]
        );

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal Order Authorization AUTH-456 status must be PENDING, CREATED or PARTIALLY_CAPTURED , current status: VOIDED');
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_AUTHORIZATION_STATUS_INVALID);

        $this->action->execute($payPalOrder);
    }

    /**
     * @param array<string, mixed> $authorization
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
                        'authorizations' => [$authorization]
                    ]
                ]
            ]
        ]);
    }

    /**
     * @param array<string, mixed> $data
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
