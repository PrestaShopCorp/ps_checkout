<?php

namespace PsCheckout\Core\Tests\Integration\PayPal\Order\Handler;

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Handler\OrderCompletedEventHandler;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderResponseFactory;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class OrderCompletedEventHandlerTest extends BaseTestCase
{
    private $payPalOrderRepository;

    private $orderCompletedEventHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payPalOrderRepository = $this->getService(PayPalOrderRepository::class);

        $this->orderCompletedEventHandler = $this->getService(OrderCompletedEventHandler::class);
    }

    public function testHandleSuccessfulCompletion(): void
    {
        // Create and save initial PayPalOrder with APPROVED status
        $payPalOrder = PayPalOrderFactory::create([
            'status' => PayPalOrderStatus::APPROVED, // Change from PENDING to APPROVED
        ]);

        $saved = $this->payPalOrderRepository->save($payPalOrder);
        $this->assertTrue($saved, 'Failed to save PayPalOrder');

        // Create response with matching ID
        $payPalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => $payPalOrder->getId(),
        ]);

        // Execute handler
        $this->orderCompletedEventHandler->handle($payPalOrderResponse);

        // Verify updates
        $updatedOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrder->getId()]);
        $this->assertNotNull($updatedOrder, 'PayPalOrder not found after update');
        $this->assertEquals('COMPLETED', $updatedOrder->getStatus());
        $this->assertEquals('CAPTURE', $updatedOrder->getIntent());
    }

    public function testHandleWithNonExistentOrder(): void
    {
        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::ORDER_NOT_FOUND);

        $payPalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => 'NON-EXISTENT-ORDER',
        ]);

        $this->orderCompletedEventHandler->handle($payPalOrderResponse);
    }

    public function testHandleWithInvalidStatusTransition(): void
    {
        // Create order with COMPLETED status
        $payPalOrder = PayPalOrderFactory::create([
            'status' => PayPalOrderStatus::COMPLETED,
        ]);
        $this->payPalOrderRepository->save($payPalOrder);

        // Try to complete it again with matching ID
        $payPalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => $payPalOrder->getId(),
        ]);

        // Should return early without throwing exception
        $this->orderCompletedEventHandler->handle($payPalOrderResponse);

        // Verify no changes were made
        $unchangedOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrder->getId()]);
        $this->assertEquals(PayPalOrderStatus::COMPLETED, $unchangedOrder->getStatus());
    }

    public function testHandleWithDifferentPaymentSource(): void
    {
        // Create initial order with APPROVED status
        $payPalOrder = PayPalOrderFactory::create([
            'status' => PayPalOrderStatus::APPROVED, // Change from PENDING to APPROVED
        ]);
        $this->payPalOrderRepository->save($payPalOrder);

        // Create response with different payment source and matching ID
        $payPalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => $payPalOrder->getId(),
            'payment_source' => [
                'paypal' => [
                    'email_address' => 'different@example.com',
                    'account_id' => 'DIFFERENT-123',
                ],
            ],
        ]);

        $this->orderCompletedEventHandler->handle($payPalOrderResponse);

        // Verify payment source was updated
        $updatedOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrder->getId()]);
        $this->assertEquals('different@example.com', $updatedOrder->getPaymentSource()['paypal']['email_address']);
        $this->assertEquals('DIFFERENT-123', $updatedOrder->getPaymentSource()['paypal']['account_id']);
    }

    public function testHandleWithDifferentFundingSource(): void
    {
        // Create initial order with APPROVED status
        $payPalOrder = PayPalOrderFactory::create([
            'status' => PayPalOrderStatus::APPROVED, // Change from PENDING to APPROVED
            'funding_source' => 'card',
        ]);
        $this->payPalOrderRepository->save($payPalOrder);

        // Create response with paypal funding source and matching ID
        $payPalOrderResponse = PayPalOrderResponseFactory::create([
            'id' => $payPalOrder->getId(),
        ]);

        $this->orderCompletedEventHandler->handle($payPalOrderResponse);

        // Verify funding source was updated
        $updatedOrder = $this->payPalOrderRepository->getOneBy(['id' => $payPalOrder->getId()]);
        $this->assertEquals('paypal', $updatedOrder->getFundingSource());
    }
}
