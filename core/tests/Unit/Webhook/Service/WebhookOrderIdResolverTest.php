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

namespace PsCheckout\Tests\Unit\Webhook\Service;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderCapture;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderCaptureRepositoryInterface;
use PsCheckout\Core\Webhook\Service\WebhookOrderIdResolver;

class WebhookOrderIdResolverTest extends TestCase
{
    /** @var PayPalOrderCaptureRepositoryInterface|MockObject */
    private $captureRepository;

    /** @var WebhookOrderIdResolver */
    private $resolver;

    protected function setUp(): void
    {
        parent::setUp();

        $this->captureRepository = $this->createMock(PayPalOrderCaptureRepositoryInterface::class);
        $this->resolver = new WebhookOrderIdResolver($this->captureRepository);
    }

    public function testResolvesOrderIdFromOrderApprovedWebhook(): void
    {
        $this->captureRepository->expects($this->never())->method('getById');

        $orderId = $this->resolver->resolve(self::orderApprovedWebhook());

        $this->assertSame('5O190127TN364715T', $orderId);
    }

    public function testResolvesOrderIdFromOrderCompletedWebhook(): void
    {
        $this->captureRepository->expects($this->never())->method('getById');

        $orderId = $this->resolver->resolve(self::orderCompletedWebhook());

        $this->assertSame('5O190127TN364715T', $orderId);
    }

    public function testResolvesOrderIdFromCaptureCompletedWebhookViaSupplementaryData(): void
    {
        $this->captureRepository->expects($this->never())->method('getById');

        $orderId = $this->resolver->resolve(self::captureCompletedWebhook());

        $this->assertSame('8U481631H66031715', $orderId);
    }

    public function testResolvesOrderIdFromCapturePendingWebhookViaDbFallback(): void
    {
        $capture = $this->createMock(PayPalOrderCapture::class);
        $capture->method('getIdOrder')->willReturn('ORDER-FROM-DB');

        $this->captureRepository->expects($this->once())
            ->method('getById')
            ->with('0C90432034385860N')
            ->willReturn($capture);

        $orderId = $this->resolver->resolve(self::capturePendingWebhook());

        $this->assertSame('ORDER-FROM-DB', $orderId);
    }

    public function testReturnsNullForCapturePendingWebhookWhenCaptureNotInDb(): void
    {
        $this->captureRepository->expects($this->once())
            ->method('getById')
            ->with('0C90432034385860N')
            ->willReturn(null);

        $orderId = $this->resolver->resolve(self::capturePendingWebhook());

        $this->assertNull($orderId);
    }

    public function testResolvesOrderIdFromRefundWebhookViaCaptureRepository(): void
    {
        $capture = $this->createMock(PayPalOrderCapture::class);
        $capture->method('getIdOrder')->willReturn('ORDER-FROM-CAPTURE');

        $this->captureRepository->expects($this->once())
            ->method('getById')
            ->with('1JP041592N604715A')
            ->willReturn($capture);

        $orderId = $this->resolver->resolve(self::refundWebhook());

        $this->assertSame('ORDER-FROM-CAPTURE', $orderId);
    }

    public function testReturnsNullForRefundWebhookWhenCaptureNotInDb(): void
    {
        $this->captureRepository->expects($this->once())
            ->method('getById')
            ->with('1JP041592N604715A')
            ->willReturn(null);

        $orderId = $this->resolver->resolve(self::refundWebhook());

        $this->assertNull($orderId);
    }

    public function testReturnsNullForUnknownResourceType(): void
    {
        $this->captureRepository->expects($this->never())->method('getById');

        $orderId = $this->resolver->resolve([
            'resourceType' => 'unknown-type',
            'resource' => ['id' => 'SOME-ID'],
        ]);

        $this->assertNull($orderId);
    }

    /**
     * PAYMENT.CAPTURE.REFUNDED — resource_type: refund
     * Order ID must be resolved by looking up the capture ID extracted from the rel:up link.
     * Capture ID: 1JP041592N604715A
     *
     * @return array{resourceType: string, resource: array<string, mixed>, eventType: string, shopId: string, summary: string, webhookId: string}
     */
    public static function refundWebhook(): array
    {
        return [
            'webhookId' => 'WH-1GN16949P03751216-32500807RA133321G',
            'shopId' => 'f71f1417-1b9a-4f44-bc1c-24f828fd401f',
            'eventType' => 'PAYMENT.CAPTURE.REFUNDED',
            'resourceType' => 'refund',
            'summary' => 'A $ 1.0 USD capture payment was refunded',
            'resource' => [
                'id' => '9ED62142KY787122L',
                'amount' => ['currency_code' => 'USD', 'value' => '1.00'],
                'custom_id' => '83',
                'status' => 'COMPLETED',
                'links' => [
                    [
                        'href' => 'https://api.sandbox.paypal.com/v2/payments/refunds/9ED62142KY787122L',
                        'method' => 'GET',
                        'rel' => 'self',
                    ],
                    [
                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/1JP041592N604715A',
                        'method' => 'GET',
                        'rel' => 'up',
                    ],
                ],
            ],
        ];
    }

    /**
     * CHECKOUT.ORDER.APPROVED — resource_type: checkout-order
     * Order ID is resource.id directly.
     *
     * @return array{resourceType: string, resource: array<string, mixed>, eventType: string, shopId: string, summary: string, webhookId: string}
     */
    public static function orderApprovedWebhook(): array
    {
        return [
            'webhookId' => 'WH-COC11055RA711503B-4YM959094A144403T',
            'shopId' => '',
            'eventType' => 'CHECKOUT.ORDER.APPROVED',
            'resourceType' => 'checkout-order',
            'summary' => 'An order has been approved by buyer',
            'resource' => [
                'id' => '5O190127TN364715T',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'purchase_units' => [
                    [
                        'reference_id' => 'd9f80740-38f0-11e8-b467-0ed5f89f718b',
                        'amount' => ['currency_code' => 'USD', 'value' => '100.00'],
                    ],
                ],
                'links' => [
                    [
                        'href' => 'https://api.paypal.com/v2/checkout/orders/5O190127TN364715T',
                        'rel' => 'self',
                        'method' => 'GET',
                    ],
                    [
                        'href' => 'https://api.paypal.com/v2/checkout/orders/5O190127TN364715T/capture',
                        'method' => 'POST',
                    ],
                ],
            ],
        ];
    }

    /**
     * CHECKOUT.ORDER.COMPLETED — resource_type: checkout-order
     * Order ID is resource.id directly.
     *
     * @return array{resourceType: string, resource: array<string, mixed>, eventType: string, shopId: string, summary: string, webhookId: string}
     */
    public static function orderCompletedWebhook(): array
    {
        return [
            'webhookId' => 'WH-COC11055RA711503B-4YM959094A144403T',
            'shopId' => '',
            'eventType' => 'CHECKOUT.ORDER.COMPLETED',
            'resourceType' => 'checkout-order',
            'summary' => 'Checkout Order Completed',
            'resource' => [
                'id' => '5O190127TN364715T',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'purchase_units' => [
                    [
                        'reference_id' => 'd9f80740-38f0-11e8-b467-0ed5f89f718b',
                        'amount' => ['currency_code' => 'USD', 'value' => '100.00'],
                        'payments' => [
                            'captures' => [
                                [
                                    'id' => '3C679366HH908993F',
                                    'status' => 'COMPLETED',
                                    'amount' => ['currency_code' => 'USD', 'value' => '100.00'],
                                ],
                            ],
                        ],
                    ],
                ],
                'links' => [
                    [
                        'href' => 'https://api.paypal.com/v2/checkout/orders/5O190127TN364715T',
                        'rel' => 'self',
                        'method' => 'GET',
                    ],
                ],
            ],
        ];
    }

    /**
     * PAYMENT.CAPTURE.COMPLETED — resource_type: capture
     * Order ID is in resource.supplementary_data.related_ids.order_id.
     * Also available via rel:up link.
     *
     * @return array{resourceType: string, resource: array<string, mixed>, eventType: string, shopId: string, summary: string, webhookId: string}
     */
    public static function captureCompletedWebhook(): array
    {
        return [
            'webhookId' => 'WH-7Y7254563A4550640-11V2185806837105M',
            'shopId' => '',
            'eventType' => 'PAYMENT.CAPTURE.COMPLETED',
            'resourceType' => 'capture',
            'summary' => 'Payment completed for $ 57.0 USD',
            'resource' => [
                'id' => '42311647XV020574X',
                'status' => 'COMPLETED',
                'supplementary_data' => [
                    'related_ids' => [
                        'order_id' => '8U481631H66031715',
                    ],
                ],
                'links' => [
                    [
                        'href' => 'https://api.paypal.com/v2/payments/captures/0KG12345VG343800K',
                        'rel' => 'self',
                        'method' => 'GET',
                    ],
                    [
                        'href' => 'https://api.paypal.com/v2/payments/captures/0KG12345VG343880K/refund',
                        'rel' => 'refund',
                        'method' => 'POST',
                    ],
                    [
                        'href' => 'https://api.paypal.com/v2/checkout/orders/8U481631H66031715',
                        'rel' => 'up',
                        'method' => 'GET',
                    ],
                ],
            ],
        ];
    }

    /**
     * PAYMENT.CAPTURE.PENDING — resource_type: capture
     * No supplementary_data, no rel:up link to orders (v1 API style).
     * Order ID must be resolved via DB lookup by capture ID.
     * Capture ID: 0C90432034385860N
     *
     * @return array{resourceType: string, resource: array<string, mixed>, eventType: string, shopId: string, summary: string, webhookId: string}
     */
    public static function capturePendingWebhook(): array
    {
        return [
            'webhookId' => 'WH-0J4956174R483764T-3MK691094K212612H',
            'shopId' => '',
            'eventType' => 'PAYMENT.CAPTURE.PENDING',
            'resourceType' => 'capture',
            'summary' => 'Payment pending for EUR 2.25 EUR',
            'resource' => [
                'id' => '0C90432034385860N',
                'state' => 'pending',
                'parent_payment' => 'PAY-02U23179LV908860DKVISFGA',
                'links' => [
                    [
                        'href' => 'https://api.paypal.com/v1/payments/capture/0C90432034385860N',
                        'rel' => 'self',
                        'method' => 'GET',
                    ],
                    [
                        'href' => 'https://api.paypal.com/v1/payments/capture/0C90432034385860N/refund',
                        'rel' => 'refund',
                        'method' => 'POST',
                    ],
                    [
                        'href' => 'https://api.paypal.com/v1/payments/payment/PAY-02U23179LV908860DKVISFGA',
                        'rel' => 'parent_payment',
                        'method' => 'GET',
                    ],
                ],
            ],
        ];
    }
}
