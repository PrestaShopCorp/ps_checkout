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

use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PaymentToken\Action\SavePaymentTokenActionInterface;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Handler\PayPalEventDispatcherInterface;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\WebhookDispatcher\Processor\DispatchWebhookProcessor;
use PsCheckout\Core\WebhookDispatcher\Repository\WebhookEventRepositoryInterface;
use PsCheckout\Core\WebhookDispatcher\ValueObject\DispatchWebhookRequest;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\CartRepositoryInterface;
use Psr\Log\LoggerInterface;

class DispatchWebhookProcessorTest extends TestCase
{
    private $logger;

    private $payPalOrderProvider;

    private $eventDispatcher;

    private $payPalOrderCache;

    private $savePaymentTokenAction;

    private $paymentTokenRepository;

    private $webhookEventRepository;

    private $payPalOrderRepository;

    private $cartRepository;

    private $context;

    private $processor;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->payPalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->eventDispatcher = $this->createMock(PayPalEventDispatcherInterface::class);
        $this->payPalOrderCache = $this->createMock(PayPalOrderCacheInterface::class);
        $this->savePaymentTokenAction = $this->createMock(SavePaymentTokenActionInterface::class);
        $this->paymentTokenRepository = $this->createMock(PaymentTokenRepositoryInterface::class);
        $this->webhookEventRepository = $this->createMock(WebhookEventRepositoryInterface::class);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->cartRepository = $this->createMock(CartRepositoryInterface::class);
        $this->context = $this->createMock(ContextInterface::class);

        $this->processor = new DispatchWebhookProcessor(
            $this->logger,
            $this->payPalOrderProvider,
            $this->eventDispatcher,
            $this->payPalOrderCache,
            $this->savePaymentTokenAction,
            $this->paymentTokenRepository,
            $this->webhookEventRepository,
            $this->payPalOrderRepository,
            $this->cartRepository,
            $this->context
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

    public function testProcessWithMissingResourceId(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('resourceId must not be empty');

        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getResourceId')->willReturn(null);

        $this->processor->process($request);
    }

    public function testProcessWithNonexistentPayPalOrder(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('PayPal order not found');

        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getResourceId')->willReturn('capture-001');
        $request->method('getWebhookId')->willReturn('webhook-abc');
        $request->method('getEventType')->willReturn('SomeEvent');

        $this->payPalOrderCache->method('has')->willReturn(false);
        $this->webhookEventRepository->method('claim')->willReturn(true);
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
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
        $request->method('getResourceId')->willReturn('capture-001');
        $request->method('getEventType')->willReturn('SomeEvent');
        $request->method('getWebhookId')->willReturn('webhook-abc');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderCache->method('has')->willReturn(true);
        $this->webhookEventRepository->method('claim')->willReturn(true);
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with('SomeEvent', $payPalOrderResponse);
        $this->webhookEventRepository->expects($this->once())->method('markSucceeded')->with('webhook-abc');
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
        $request->method('getResourceId')->willReturn('token-001');
        $request->method('getEventType')->willReturn('VaultPaymentTokenCreated');
        $request->method('getWebhookId')->willReturn('webhook-abc');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->webhookEventRepository->method('claim')->willReturn(true);
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->savePaymentTokenAction->expects($this->once())->method('execute')->with($payPalOrderResponse);
        $this->webhookEventRepository->expects($this->once())->method('markSucceeded')->with('webhook-abc');

        $this->assertTrue($this->processor->process($request));
    }

    public function testProcessPaymentAuthorizationVoidedDispatchesEvent(): void
    {
        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getResourceId')->willReturn('auth-001');
        $request->method('getEventType')->willReturn('PaymentAuthorizationVoided');
        $request->method('getWebhookId')->willReturn('webhook-abc');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderCache->method('has')->willReturn(false);
        $this->webhookEventRepository->method('claim')->willReturn(true);
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->eventDispatcher->expects($this->once())->method('dispatch')->with('PaymentAuthorizationVoided', $payPalOrderResponse);
        $this->webhookEventRepository->expects($this->once())->method('markSucceeded')->with('webhook-abc');

        $this->assertTrue($this->processor->process($request));
    }

    public function testHandlePaymentTokenEventsForDeletedToken(): void
    {
        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getResourceId')->willReturn('token-001');
        $request->method('getEventType')->willReturn('VaultPaymentTokenDeleted');
        $request->method('getWebhookId')->willReturn('webhook-abc');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);
        $payPalOrderResponse->method('getVault')->willReturn(['id' => 'token123']);

        $this->webhookEventRepository->method('claim')->willReturn(true);
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->paymentTokenRepository->expects($this->once())->method('delete')->with('token123');
        $this->webhookEventRepository->expects($this->once())->method('markSucceeded')->with('webhook-abc');

        $this->assertTrue($this->processor->process($request));
    }

    public function testProcessSkipsWhenEventAlreadyClaimed(): void
    {
        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getResourceId')->willReturn('capture-001');
        $request->method('getWebhookId')->willReturn('webhook-abc');
        $request->method('getEventType')->willReturn('SomeEvent');

        $this->payPalOrderCache->method('has')->willReturn(false);
        $this->webhookEventRepository->method('claim')->willReturn(false);

        $this->payPalOrderProvider->expects($this->never())->method('getById');
        $this->eventDispatcher->expects($this->never())->method('dispatch');
        $this->webhookEventRepository->expects($this->never())->method('markSucceeded');

        $this->assertTrue($this->processor->process($request));
    }

    public function testProcessDispatchThrowsUnexpectedExceptionCallsMarkFailed(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Something unexpected');

        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getResourceId')->willReturn('capture-001');
        $request->method('getWebhookId')->willReturn('webhook-abc');
        $request->method('getEventType')->willReturn('SomeEvent');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderCache->method('has')->willReturn(false);
        $this->webhookEventRepository->method('claim')->willReturn(true);
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->eventDispatcher->method('dispatch')
            ->willThrowException(new \RuntimeException('Something unexpected'));

        $this->webhookEventRepository->expects($this->once())
            ->method('markFailed')
            ->with('webhook-abc', 'Something unexpected');
        $this->webhookEventRepository->expects($this->never())->method('markSucceeded');

        $this->processor->process($request);
    }

    public function testProcessOrderAlreadyExistsMarksSucceeded(): void
    {
        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getResourceId')->willReturn('capture-001');
        $request->method('getWebhookId')->willReturn('webhook-abc');
        $request->method('getEventType')->willReturn('SomeEvent');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderCache->method('has')->willReturn(false);
        $this->webhookEventRepository->method('claim')->willReturn(true);
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->eventDispatcher->method('dispatch')
            ->willThrowException(new PsCheckoutException('Order already exist', PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS));

        // PRESTASHOP_ORDER_ALREADY_EXISTS is a success case: the order exists, so mark succeeded
        $this->webhookEventRepository->expects($this->once())
            ->method('markSucceeded')
            ->with('webhook-abc');
        $this->webhookEventRepository->expects($this->never())->method('markFailed');

        $this->assertTrue($this->processor->process($request));
    }

    public function testProcessPsCheckoutExceptionCallsMarkFailed(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Business rule failed');

        $request = $this->createMock(DispatchWebhookRequest::class);
        $request->method('getCategory')->willReturn('ShopNotificationOrderChange');
        $request->method('getOrderId')->willReturn('12345');
        $request->method('getResourceId')->willReturn('capture-001');
        $request->method('getWebhookId')->willReturn('webhook-abc');
        $request->method('getEventType')->willReturn('SomeEvent');

        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);

        $this->payPalOrderCache->method('has')->willReturn(false);
        $this->webhookEventRepository->method('claim')->willReturn(true);
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
        $this->payPalOrderProvider->method('getById')->willReturn($payPalOrderResponse);
        $this->eventDispatcher->method('dispatch')
            ->willThrowException(new PsCheckoutException('Business rule failed', 0));

        $this->webhookEventRepository->expects($this->once())
            ->method('markFailed')
            ->with('webhook-abc', 'Business rule failed');
        $this->webhookEventRepository->expects($this->never())->method('markSucceeded');

        $this->processor->process($request);
    }
}
