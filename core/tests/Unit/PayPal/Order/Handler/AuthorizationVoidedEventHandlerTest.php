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
use PsCheckout\Core\OrderState\Action\SetOrderStateActionInterface;
use PsCheckout\Core\PayPal\Order\Handler\AuthorizationVoidedEventHandler;

class AuthorizationVoidedEventHandlerTest extends TestCase
{
    /**
     * @var SetOrderStateActionInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $setVoidedOrderStateAction;

    /**
     * @var AuthorizationVoidedEventHandler
     */
    private $handler;

    protected function setUp(): void
    {
        $this->setVoidedOrderStateAction = $this->createMock(SetOrderStateActionInterface::class);
        $this->handler = new AuthorizationVoidedEventHandler($this->setVoidedOrderStateAction);
    }

    public function testHandleSingleAuthorizationNoOtherItems(): void
    {
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);
        $payPalOrderResponse->method('getId')->willReturn('ORDER-123');
        $payPalOrderResponse->method('getAuthorizations')->willReturn([['id' => 'AUTH-1', 'status' => 'VOIDED']]);
        $payPalOrderResponse->method('getCapture')->willReturn(null);
        $payPalOrderResponse->method('getRefunds')->willReturn(null);

        $this->setVoidedOrderStateAction->expects($this->once())->method('execute')->with('ORDER-123');

        $this->handler->handle($payPalOrderResponse);
    }

    public function testHandleMultipleAuthorizationsSkips(): void
    {
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);
        $payPalOrderResponse->method('getAuthorizations')->willReturn([
            ['id' => 'AUTH-1', 'status' => 'VOIDED'],
            ['id' => 'AUTH-2', 'status' => 'CREATED'],
        ]);
        $payPalOrderResponse->method('getCapture')->willReturn(null);
        $payPalOrderResponse->method('getRefunds')->willReturn(null);

        $this->setVoidedOrderStateAction->expects($this->never())->method('execute');

        $this->handler->handle($payPalOrderResponse);
    }

    public function testHandleWithCapturesSkips(): void
    {
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);
        $payPalOrderResponse->method('getAuthorizations')->willReturn([['id' => 'AUTH-1', 'status' => 'VOIDED']]);
        $payPalOrderResponse->method('getCapture')->willReturn(['id' => 'CAP-1', 'status' => 'COMPLETED']);
        $payPalOrderResponse->method('getRefunds')->willReturn(null);

        $this->setVoidedOrderStateAction->expects($this->never())->method('execute');

        $this->handler->handle($payPalOrderResponse);
    }

    public function testHandleWithRefundsSkips(): void
    {
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);
        $payPalOrderResponse->method('getAuthorizations')->willReturn([['id' => 'AUTH-1', 'status' => 'VOIDED']]);
        $payPalOrderResponse->method('getCapture')->willReturn(null);
        $payPalOrderResponse->method('getRefunds')->willReturn([['id' => 'REF-1', 'status' => 'COMPLETED']]);

        $this->setVoidedOrderStateAction->expects($this->never())->method('execute');

        $this->handler->handle($payPalOrderResponse);
    }

    public function testHandleWithCapturesAndRefundsSkips(): void
    {
        $payPalOrderResponse = $this->createMock(PayPalOrderResponse::class);
        $payPalOrderResponse->method('getAuthorizations')->willReturn([['id' => 'AUTH-1', 'status' => 'VOIDED']]);
        $payPalOrderResponse->method('getCapture')->willReturn(['id' => 'CAP-1', 'status' => 'COMPLETED']);
        $payPalOrderResponse->method('getRefunds')->willReturn([['id' => 'REF-1', 'status' => 'COMPLETED']]);

        $this->setVoidedOrderStateAction->expects($this->never())->method('execute');

        $this->handler->handle($payPalOrderResponse);
    }
}
