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

namespace PsCheckout\Core\Tests\Integration\PayPal\Order\Action;

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Action\CancelPayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CancelPayPalOrderRequest;
use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Core\Tests\Integration\Factory\PayPalOrderFactory;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class CancelPayPalOrderActionTest extends BaseTestCase
{
    /** @var CancelPayPalOrderAction */
    private $cancelPayPalOrderAction;

    /** @var PayPalOrderRepository */
    private $payPalRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->payPalRepository = $this->getService(PayPalOrderRepository::class);
        $this->cancelPayPalOrderAction = $this->getService(CancelPayPalOrderAction::class);
    }

    public function testItSuccessfullyCancelsPayPalOrder(): void
    {
        // Create and save initial PayPal order
        $payPalOrder = PayPalOrderFactory::create([
            'id' => 'PAY-123',
            'id_cart' => 1,
            'status' => 'PENDING',
        ]);
        $this->payPalRepository->savePayPalOrder($payPalOrder);

        // Create cancel request
        $request = new CancelPayPalOrderRequest([
            'orderID' => 'PAY-123',
            'orderStatus' => 'CANCELED',
            'fundingSource' => 'paypal',
            'isHostedFields' => false,
            'isExpressCheckout' => false,
        ], 1);

        // Execute action
        $this->cancelPayPalOrderAction->execute($request);

        // Verify order was updated
        $updatedOrder = $this->payPalRepository->getOneBy(['id' => 'PAY-123']);
        $this->assertEquals(1, $updatedOrder->getIdCart());
        $this->assertEquals('PAY-123', $updatedOrder->getPaymentTokenId());
        $this->assertEquals('paypal', $updatedOrder->getFundingSource());
        $this->assertFalse($updatedOrder->isCardFields());
        $this->assertFalse($updatedOrder->isExpressCheckout());
        $this->assertEquals('CANCELED', $updatedOrder->getStatus());
    }

    public function testItThrowsExceptionWhenOrderNotFound(): void
    {
        $request = new CancelPayPalOrderRequest([
            'orderID' => 'NONEXISTENT-ORDER',
            'orderStatus' => 'CANCELED',
            'fundingSource' => 'paypal',
            'isHostedFields' => false,
            'isExpressCheckout' => false,
        ], 1);

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionMessage('Unable to update PrestaShop Checkout session #1');

        $this->cancelPayPalOrderAction->execute($request);
    }

    public function testItHandlesCardPaymentOrder(): void
    {
        $payPalOrder = PayPalOrderFactory::create([
            'id' => 'PAY-123',
            'id_cart' => 1,
            'funding_source' => 'card',
            'is_card_fields' => true,
            'status' => 'PENDING',
        ]);
        $this->payPalRepository->savePayPalOrder($payPalOrder);

        $request = new CancelPayPalOrderRequest([
            'orderID' => 'PAY-123',
            'orderStatus' => 'CANCELED',
            'fundingSource' => 'card',
            'isHostedFields' => true,
            'isExpressCheckout' => false,
        ], 1);

        $this->cancelPayPalOrderAction->execute($request);

        $updatedOrder = $this->payPalRepository->getOneBy(['id' => 'PAY-123']);
        $this->assertEquals('card', $updatedOrder->getFundingSource());
        $this->assertTrue($updatedOrder->isCardFields());
    }

    public function testItHandlesExpressCheckoutOrder(): void
    {
        $payPalOrder = PayPalOrderFactory::create([
            'id' => 'PAY-123',
            'id_cart' => 1,
            'is_express_checkout' => true,
            'status' => 'PENDING',
        ]);
        $this->payPalRepository->savePayPalOrder($payPalOrder);

        $request = new CancelPayPalOrderRequest([
            'orderID' => 'PAY-123',
            'orderStatus' => 'CANCELED',
            'fundingSource' => 'paypal',
            'isHostedFields' => false,
            'isExpressCheckout' => true,
        ], 1);

        $this->cancelPayPalOrderAction->execute($request);

        $updatedOrder = $this->payPalRepository->getOneBy(['id' => 'PAY-123']);
        $this->assertTrue($updatedOrder->isExpressCheckout());
    }

    protected function tearDown(): void
    {
        // Clean up test data
        $this->payPalRepository->deletePayPalOrder('PAY-123');
        parent::tearDown();
    }
}
