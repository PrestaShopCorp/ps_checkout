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

namespace PsCheckout\Tests\Unit\PayPal\Order\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Action\CancelPayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderStatus;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CancelPayPalOrderRequest;

class CancelPayPalOrderActionTest extends TestCase
{
    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $payPalOrderRepository;

    /** @var CancelPayPalOrderAction */
    private $action;

    protected function setUp(): void
    {
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->action = new CancelPayPalOrderAction($this->payPalOrderRepository);
    }

    public function testItCancelsThePayPalOrderOfTheRequestCart(): void
    {
        $payPalOrder = $this->createPayPalOrder(1, 'TOKEN-1');

        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn($payPalOrder);
        $this->payPalOrderRepository->expects($this->once())
            ->method('save')
            ->with($payPalOrder)
            ->willReturn(true);

        $this->action->execute(new CancelPayPalOrderRequest([
            'orderID' => 'ORDER-123',
            'fundingSource' => 'card',
            'isHostedFields' => true,
            'isExpressCheckout' => false,
        ], 1));

        $this->assertSame(1, $payPalOrder->getIdCart());
        $this->assertSame(PayPalOrderStatus::CANCELED, $payPalOrder->getStatus());
        $this->assertSame('card', $payPalOrder->getFundingSource());
        $this->assertTrue($payPalOrder->isCardFields());
        $this->assertFalse($payPalOrder->isExpressCheckout());
        $this->assertSame('TOKEN-1', $payPalOrder->getPaymentTokenId(), 'The payment token must not be overwritten with the order id');
    }

    public function testItNeverTakesTheStatusFromTheRequest(): void
    {
        $payPalOrder = $this->createPayPalOrder(1);

        $this->payPalOrderRepository->method('getOneBy')->willReturn($payPalOrder);
        $this->payPalOrderRepository->expects($this->once())->method('save')->willReturn(true);

        $this->action->execute(new CancelPayPalOrderRequest([
            'orderID' => 'ORDER-123',
            'orderStatus' => PayPalOrderStatus::COMPLETED,
        ], 1));

        $this->assertSame(PayPalOrderStatus::CANCELED, $payPalOrder->getStatus());
    }

    public function testItRefusesWhenPayPalOrderIsLinkedToAnotherCart(): void
    {
        $payPalOrder = $this->createPayPalOrder(1);

        $this->payPalOrderRepository->method('getOneBy')->willReturn($payPalOrder);
        $this->payPalOrderRepository->expects($this->never())->method('save');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::PAYPAL_ORDER_CART_MISMATCH);

        try {
            $this->action->execute(new CancelPayPalOrderRequest(['orderID' => 'ORDER-123'], 2));
        } finally {
            $this->assertSame(1, $payPalOrder->getIdCart(), 'The PayPal Order must stay linked to its own cart');
            $this->assertSame(PayPalOrderStatus::APPROVED, $payPalOrder->getStatus());
        }
    }

    public function testItThrowsWhenPayPalOrderIsUnknown(): void
    {
        $this->payPalOrderRepository->method('getOneBy')->willReturn(null);
        $this->payPalOrderRepository->expects($this->never())->method('save');

        $this->expectException(PsCheckoutException::class);
        $this->expectExceptionCode(PsCheckoutException::UPDATE_FAILED);

        $this->action->execute(new CancelPayPalOrderRequest(['orderID' => 'UNKNOWN'], 1));
    }

    /**
     * @param int $cartId
     * @param string|null $paymentTokenId
     *
     * @return PayPalOrder
     */
    private function createPayPalOrder(int $cartId, $paymentTokenId = null): PayPalOrder
    {
        return new PayPalOrder(
            'ORDER-123',
            $cartId,
            PayPalOrderIntent::CAPTURE,
            'paypal',
            PayPalOrderStatus::APPROVED,
            [],
            'SANDBOX',
            false,
            false,
            [],
            $paymentTokenId
        );
    }
}
