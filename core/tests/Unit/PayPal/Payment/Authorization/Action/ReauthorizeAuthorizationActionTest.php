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
use PsCheckout\Api\Dto\PayPal\LinkDescription;
use PsCheckout\Api\Dto\PayPal\Payment\AuthorizationLinkRelation;
use PsCheckout\Api\Dto\PayPal\Payment\PaymentAuthorizationResponseDto;
use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Api\Http\PaymentHttpClientInterface;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\OrderState\Action\SetOrderStateActionInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalAuthorizationStatus;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Handler\EventHandlerInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;
use PsCheckout\Core\PayPal\Payment\Authorization\Action\ReauthorizeAuthorizationAction;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use Psr\Log\LoggerInterface;

/**
 * @covers \PsCheckout\Core\PayPal\Payment\Authorization\Action\ReauthorizeAuthorizationAction
 */
class ReauthorizeAuthorizationActionTest extends TestCase
{
    /**
     * @var MockObject|PaymentHttpClientInterface
     */
    private $paymentHttpClient;

    /**
     * @var MockObject|SetOrderStateActionInterface
     */
    private $setPendingOrderStateAction;

    /**
     * @var MockObject|EventHandlerInterface
     */
    private $paymentDeniedEventHandler;

    /**
     * @var MockObject|PayPalOrderAuthorizationRepositoryInterface
     */
    private $payPalOrderAuthorizationRepository;

    /**
     * @var ReauthorizeAuthorizationAction
     */
    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentHttpClient = $this->createMock(PaymentHttpClientInterface::class);
        $this->setPendingOrderStateAction = $this->createMock(SetOrderStateActionInterface::class);
        $this->paymentDeniedEventHandler = $this->createMock(EventHandlerInterface::class);
        $this->payPalOrderAuthorizationRepository = $this->createMock(PayPalOrderAuthorizationRepositoryInterface::class);

        $this->action = new ReauthorizeAuthorizationAction(
            $this->createMock(LoggerInterface::class),
            $this->paymentHttpClient,
            $this->payPalOrderAuthorizationRepository,
            $this->setPendingOrderStateAction,
            $this->paymentDeniedEventHandler
        );
    }

    public function testInvalidOrderIntent(): void
    {
        $this->expectExceptionObject(new PsCheckoutException('PayPal Order ORDER-123 intent must be AUTHORIZE, current intent: CAPTURE', PsCheckoutException::PAYPAL_ORDER_INTENT_INVALID));
        $this->action->execute(PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::CAPTURE,
        ]));
    }

    public function testEmptyOrderPurchaseUnits(): void
    {
        $this->expectExceptionObject(new PsCheckoutException('PayPal Order APPROVED contains invalid authorizations', PsCheckoutException::PAYPAL_ORDER_AUTHORIZATIONS_INVALID));
        $this->action->execute(PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [],
        ]));
    }

    public function testMultipleOrderPurchaseUnits(): void
    {
        $this->expectExceptionObject(new PsCheckoutException('PayPal Order APPROVED contains invalid authorizations', PsCheckoutException::PAYPAL_ORDER_AUTHORIZATIONS_INVALID));
        $this->action->execute(PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                ],
                [
                    'reference_id' => 'PU-456',
                ]
            ],
        ]));
    }

    public function testEmptyOrderAuthorizations(): void
    {
        $this->expectExceptionObject(new PsCheckoutException('PayPal Order ORDER-123 does not contain reauthorize-able authorizations', PsCheckoutException::PAYPAL_ORDER_AUTHORIZATIONS_EMPTY));
        $this->action->execute(PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [],
                    ]
                ],
            ],
        ]));
    }

    public function testMultipleOrderAuthorizations(): void
    {
        $this->expectExceptionObject(new PsCheckoutException('PayPal Order ORDER-123 contains more than one reauthorize-able authorizations', PsCheckoutException::PAYPAL_ORDER_AUTHORIZATIONS_NOT_UNIQUE));
        $this->action->execute(PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ],
                            [
                                'id' => 'AUTH-456',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ]
                        ],
                    ]
                ],
            ],
        ]));
    }

    public function testOrderAuthorizationStatusInvalid(): void
    {
        $this->paymentHttpClient->method('getAuthorization')->with('AUTH-123')->willReturn(new PaymentAuthorizationResponseDto(
            'AUTH-123',
            PayPalAuthorizationStatus::VOIDED
        ));

        $this->expectExceptionObject(new PsCheckoutException('PayPal Authorization AUTH-123 is not reauthorize-able, current status: VOIDED', PsCheckoutException::PAYPAL_AUTHORIZATION_STATUS_INVALID));
        $this->action->execute(PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ],
                        ],
                    ]
                ],
            ],
        ]));
    }

    public function testOrderAuthorizationMissingLinkRel(): void
    {
        $this->paymentHttpClient->method('getAuthorization')->with('AUTH-123')->willReturn(new PaymentAuthorizationResponseDto(
            'AUTH-123',
            PayPalAuthorizationStatus::VOIDED,
            [
                new LinkDescription(
                    'https://api-m.paypal.com/v2/payments/authorizations/AUTH-123',
                    AuthorizationLinkRelation::SELF,
                    'GET'
                )
            ]
        ));

        $this->expectExceptionObject(new PsCheckoutException('PayPal Authorization AUTH-123 is not reauthorize-able, current status: VOIDED', PsCheckoutException::PAYPAL_AUTHORIZATION_STATUS_INVALID));
        $this->action->execute(PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ],
                        ],
                    ]
                ],
            ],
        ]));
    }

    public function testOrderAuthorizationReauthorizationFailure(): void
    {
        $this->paymentHttpClient->method('getAuthorization')->with('AUTH-123')->willReturn(new PaymentAuthorizationResponseDto(
            'AUTH-123',
            PayPalAuthorizationStatus::CREATED,
            [
                new LinkDescription(
                    'https://api-m.paypal.com/v2/payments/authorizations/AUTH-123',
                    AuthorizationLinkRelation::REAUTHORIZE,
                    'POST'
                )
            ]
        ));

        $this->paymentHttpClient
            ->expects($this->once())
            ->method('reauthorizeAuthorization')
            ->with('AUTH-123')
            ->willThrowException(new PayPalException('Reauthorization failed'));

        $this->expectExceptionObject(new PsCheckoutException('PayPal Order ORDER-123 re-authorization failure for authorization AUTH-123', PsCheckoutException::PAYPAL_AUTHORIZATION_REAUTHORIZATION_FAILURE));
        $this->action->execute(PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ],
                        ],
                    ]
                ],
            ],
        ]));
    }

    public function testOrderAuthorizationReauthorizationPersistenceFailure(): void
    {
        $paypalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ],
                        ],
                    ]
                ],
            ],
        ]);

        $this->paymentHttpClient->method('getAuthorization')->with('AUTH-123')->willReturn(new PaymentAuthorizationResponseDto(
            'AUTH-123',
            PayPalAuthorizationStatus::CREATED,
            [
                new LinkDescription(
                    'https://api-m.paypal.com/v2/payments/authorizations/AUTH-123',
                    AuthorizationLinkRelation::REAUTHORIZE,
                    'POST'
                )
            ]
        ));

        $reauthorization = new PaymentAuthorizationResponseDto(
            'AUTH-456',
            PayPalAuthorizationStatus::PENDING
        );

        $this->paymentHttpClient
            ->expects($this->once())
            ->method('reauthorizeAuthorization')
            ->with('AUTH-123')
            ->willReturn($reauthorization);

        $this->payPalOrderAuthorizationRepository->expects($this->once())->method('save')->with(new PayPalOrderAuthorization(
            $reauthorization->getId(),
            $paypalOrderResponse->getId(),
            $reauthorization->getStatus(),
            $reauthorization->getExpirationTime(),
            $reauthorization->getCreateTime(),
            $reauthorization->getUpdateTime()
        ))->willThrowException(new \Exception('Persistence failure'));

        $this->expectExceptionObject(new PsCheckoutException('PayPal Order ORDER-123 re-authorization database failure for authorization AUTH-456', PsCheckoutException::PAYPAL_AUTHORIZATION_DATABASE_FAILURE));
        $this->action->execute($paypalOrderResponse);
    }

    public function testOrderAuthorizationReauthorizationPendingHandler(): void
    {
        $paypalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ],
                        ],
                    ]
                ],
            ],
        ]);

        $this->paymentHttpClient->method('getAuthorization')->with('AUTH-123')->willReturn(new PaymentAuthorizationResponseDto(
            'AUTH-123',
            PayPalAuthorizationStatus::CREATED,
            [
                new LinkDescription(
                    'https://api-m.paypal.com/v2/payments/authorizations/AUTH-123',
                    AuthorizationLinkRelation::REAUTHORIZE,
                    'POST'
                )
            ]
        ));

        $reauthorization = new PaymentAuthorizationResponseDto(
            'AUTH-456',
            PayPalAuthorizationStatus::PENDING
        );

        $this->paymentHttpClient
            ->expects($this->once())
            ->method('reauthorizeAuthorization')
            ->with('AUTH-123')
            ->willReturn($reauthorization);

        $this->payPalOrderAuthorizationRepository->expects($this->once())->method('save')->with(new PayPalOrderAuthorization(
            $reauthorization->getId(),
            $paypalOrderResponse->getId(),
            $reauthorization->getStatus(),
            $reauthorization->getExpirationTime(),
            $reauthorization->getCreateTime(),
            $reauthorization->getUpdateTime()
        ));

        $this->setPendingOrderStateAction->expects($this->once())->method('execute')->with($paypalOrderResponse->getId());
        $this->action->execute($paypalOrderResponse);
    }

    public function testOrderAuthorizationReauthorizationCreatedHandler(): void
    {
        $paypalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ],
                        ],
                    ]
                ],
            ],
        ]);

        $this->paymentHttpClient->method('getAuthorization')->with('AUTH-123')->willReturn(new PaymentAuthorizationResponseDto(
            'AUTH-123',
            PayPalAuthorizationStatus::CREATED,
            [
                new LinkDescription(
                    'https://api-m.paypal.com/v2/payments/authorizations/AUTH-123',
                    AuthorizationLinkRelation::REAUTHORIZE,
                    'POST'
                )
            ]
        ));

        $reauthorization = new PaymentAuthorizationResponseDto(
            'AUTH-456',
            PayPalAuthorizationStatus::CREATED
        );

        $this->paymentHttpClient
            ->expects($this->once())
            ->method('reauthorizeAuthorization')
            ->with('AUTH-123')
            ->willReturn($reauthorization);

        $this->payPalOrderAuthorizationRepository->expects($this->once())->method('save')->with(new PayPalOrderAuthorization(
            $reauthorization->getId(),
            $paypalOrderResponse->getId(),
            $reauthorization->getStatus(),
            $reauthorization->getExpirationTime(),
            $reauthorization->getCreateTime(),
            $reauthorization->getUpdateTime()
        ));

        $this->setPendingOrderStateAction->expects($this->once())->method('execute')->with($paypalOrderResponse->getId());
        $this->action->execute($paypalOrderResponse);
    }

    public function testOrderAuthorizationReauthorizationDeniedHandler(): void
    {
        $paypalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => 'ORDER-123',
            'status' => PayPalOrderStatus::APPROVED,
            'intent' => PayPalOrderIntent::AUTHORIZE,
            'purchase_units' => [
                [
                    'reference_id' => 'PU-123',
                    'payments' => [
                        'authorizations' => [
                            [
                                'id' => 'AUTH-123',
                                'status' => PayPalAuthorizationStatus::CREATED,
                            ],
                        ],
                    ]
                ],
            ],
        ]);

        $this->paymentHttpClient->method('getAuthorization')->with('AUTH-123')->willReturn(new PaymentAuthorizationResponseDto(
            'AUTH-123',
            PayPalAuthorizationStatus::CREATED,
            [
                new LinkDescription(
                    'https://api-m.paypal.com/v2/payments/authorizations/AUTH-123',
                    AuthorizationLinkRelation::REAUTHORIZE,
                    'POST'
                )
            ]
        ));

        $reauthorization = new PaymentAuthorizationResponseDto(
            'AUTH-456',
            PayPalAuthorizationStatus::DENIED
        );

        $this->paymentHttpClient
            ->expects($this->once())
            ->method('reauthorizeAuthorization')
            ->with('AUTH-123')
            ->willReturn($reauthorization);

        $this->payPalOrderAuthorizationRepository->expects($this->once())->method('save')->with(new PayPalOrderAuthorization(
            $reauthorization->getId(),
            $paypalOrderResponse->getId(),
            $reauthorization->getStatus(),
            $reauthorization->getExpirationTime(),
            $reauthorization->getCreateTime(),
            $reauthorization->getUpdateTime()
        ));

        $this->paymentDeniedEventHandler->expects($this->once())->method('handle')->with($paypalOrderResponse);
        $this->action->execute($paypalOrderResponse);
    }
}
