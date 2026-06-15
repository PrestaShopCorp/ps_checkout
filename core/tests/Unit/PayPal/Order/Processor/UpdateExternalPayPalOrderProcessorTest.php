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

namespace PsCheckout\Tests\Unit\PayPal\Order\Processor;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Order\Builder\CheckoutContextBuilderInterface;
use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PayPal\Order\Action\UpdatePayPalOrderPurchaseUnitActionInterface;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Exception\PayPalOrderException;
use PsCheckout\Core\PayPal\Order\Processor\UpdateExternalPayPalOrderProcessor;
use PsCheckout\Core\PayPal\Order\Provider\PayPalOrderProviderInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CheckPayPalOrderRequest;
use Psr\Http\Message\ResponseInterface;

class UpdateExternalPayPalOrderProcessorTest extends TestCase
{
    /** @var UpdateExternalPayPalOrderProcessor */
    private $processor;

    /** @var PayPalOrderProviderInterface|MockObject */
    private $paypalOrderProvider;

    /** @var CheckoutContextBuilderInterface|MockObject */
    private $checkoutContextBuilder;

    /** @var OrderPayloadBuilderInterface|MockObject */
    private $orderPayloadBuilder;

    /** @var OrderHttpClientInterface|MockObject */
    private $httpClient;

    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $paypalOrderRepository;

    /** @var PayPalOrderCacheInterface|MockObject */
    private $paypalOrderCache;

    /** @var UpdatePayPalOrderPurchaseUnitActionInterface|MockObject */
    private $updatePayPalOrderPurchaseUnit;

    protected function setUp(): void
    {
        $this->paypalOrderProvider = $this->createMock(PayPalOrderProviderInterface::class);
        $this->checkoutContextBuilder = $this->createMock(CheckoutContextBuilderInterface::class);
        $this->orderPayloadBuilder = $this->createMock(OrderPayloadBuilderInterface::class);
        $this->httpClient = $this->createMock(OrderHttpClientInterface::class);
        $this->paypalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->paypalOrderCache = $this->createMock(PayPalOrderCacheInterface::class);
        $this->updatePayPalOrderPurchaseUnit = $this->createMock(UpdatePayPalOrderPurchaseUnitActionInterface::class);

        $this->processor = new UpdateExternalPayPalOrderProcessor(
            $this->paypalOrderProvider,
            $this->checkoutContextBuilder,
            $this->orderPayloadBuilder,
            $this->httpClient,
            $this->paypalOrderRepository,
            $this->paypalOrderCache,
            $this->updatePayPalOrderPurchaseUnit
        );
    }

    public function testItReturnsEarlyWhenPayPalOrderNotFound(): void
    {
        $request = $this->createMock(CheckPayPalOrderRequest::class);
        $request->method('getOrderId')->willReturn('ORDER-123');

        $this->paypalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => 'ORDER-123'])
            ->willReturn(null);

        $this->paypalOrderProvider->expects($this->never())->method('getById');

        $this->processor->execute($request);
    }

    public function testItReturnsEarlyWhenOrderIsCompleted(): void
    {
        $request = $this->createMock(CheckPayPalOrderRequest::class);
        $request->method('getOrderId')->willReturn('ORDER-123');

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $this->paypalOrderRepository->method('getOneBy')->willReturn($payPalOrder);

        $paypalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'COMPLETED',
            PayPalOrderIntent::CAPTURE,
            null,
            null,
            [],
            []
        );
        $this->paypalOrderProvider->method('getById')->willReturn($paypalOrderResponse);

        $this->orderPayloadBuilder->expects($this->never())->method('build');

        $this->processor->execute($request);
    }

    public function testItUpdatesOrderWhenChangesDetected(): void
    {
        $request = $this->createMock(CheckPayPalOrderRequest::class);
        $request->method('getOrderId')->willReturn('ORDER-123');
        $request->method('getFundingSource')->willReturn('card');
        $request->method('isHostedFields')->willReturn(true);
        $request->method('isExpressCheckout')->willReturn(false);

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->expects($this->once())->method('setStatus')->with('APPROVED');
        $payPalOrder->expects($this->once())->method('setFundingSource')->with('card');
        $payPalOrder->expects($this->once())->method('setIsCardFields')->with(true);
        $payPalOrder->expects($this->once())->method('setIsExpressCheckout')->with(false);

        $this->paypalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->willReturn($payPalOrder);

        $paypalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'APPROVED',
            PayPalOrderIntent::CAPTURE,
            null,
            null,
            [
                [
                    'amount' => ['currency_code' => 'USD', 'value' => '10.00'],
                    'items' => [['id' => '1']],
                ],
            ],
            []
        );
        $this->paypalOrderProvider->method('getById')->willReturn($paypalOrderResponse);

        $context = $this->createMock(CheckoutContextInterface::class);

        $this->checkoutContextBuilder->method('setIsUpdate')->willReturnSelf();
        $this->checkoutContextBuilder->method('setPaypalOrderId')->willReturnSelf();
        $this->checkoutContextBuilder->method('setFundingSource')->willReturnSelf();
        $this->checkoutContextBuilder->method('setIsCard')->willReturnSelf();
        $this->checkoutContextBuilder->method('setIsExpressCheckout')->willReturnSelf();
        $this->checkoutContextBuilder->method('build')->willReturn($context);

        $this->orderPayloadBuilder->expects($this->once())
            ->method('build')
            ->with($context)
            ->willReturn([
                'purchase_units' => [
                    [
                        'amount' => ['currency_code' => 'USD', 'value' => '11.00'],
                        'items' => [['id' => '2']],
                    ],
                ],
            ]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(204);

        $this->httpClient->expects($this->once())
            ->method('updateOrder')
            ->willReturn($response);

        $this->paypalOrderRepository->expects($this->once())
            ->method('save')
            ->with($payPalOrder);

        $this->updatePayPalOrderPurchaseUnit->expects($this->once())
            ->method('execute')
            ->with($paypalOrderResponse);

        $this->paypalOrderCache->expects($this->once())
            ->method('delete')
            ->with('ORDER-123');

        $this->processor->execute($request);
    }

    public function testItThrowsExceptionWhenUpdateFails(): void
    {
        $request = $this->createMock(CheckPayPalOrderRequest::class);
        $request->method('getOrderId')->willReturn('ORDER-123');
        $request->method('getFundingSource')->willReturn('card');
        $request->method('isHostedFields')->willReturn(true);
        $request->method('isExpressCheckout')->willReturn(false);

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $this->paypalOrderRepository->method('getOneBy')->willReturn($payPalOrder);

        $paypalOrderResponse = new PayPalOrderResponse(
            'ORDER-123',
            'PENDING',
            PayPalOrderIntent::CAPTURE,
            null,
            null,
            [
                [
                    'amount' => ['currency_code' => 'USD', 'value' => '10.00'],
                    'shipping' => ['old_data'],
                ],
            ],
            []
        );
        $this->paypalOrderProvider->method('getById')->willReturn($paypalOrderResponse);

        $context = $this->createMock(CheckoutContextInterface::class);

        $this->checkoutContextBuilder->method('setIsUpdate')->willReturnSelf();
        $this->checkoutContextBuilder->method('setPaypalOrderId')->willReturnSelf();
        $this->checkoutContextBuilder->method('setFundingSource')->willReturnSelf();
        $this->checkoutContextBuilder->method('setIsCard')->willReturnSelf();
        $this->checkoutContextBuilder->method('setIsExpressCheckout')->willReturnSelf();
        $this->checkoutContextBuilder->method('build')->willReturn($context);

        $this->orderPayloadBuilder->method('build')->willReturn([
            'purchase_units' => [
                [
                    'amount' => ['currency_code' => 'USD', 'value' => '11.00'],
                ],
            ],
        ]);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(400);

        $this->httpClient->method('updateOrder')->willReturn($response);

        $this->expectException(PayPalOrderException::class);
        $this->expectExceptionMessage('Failed to update PayPal Order');
        $this->expectExceptionCode(PayPalOrderException::PAYPAL_ORDER_UPDATE_FAILED);

        $this->processor->execute($request);
    }
}
