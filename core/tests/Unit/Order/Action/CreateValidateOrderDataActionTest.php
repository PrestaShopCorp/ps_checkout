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
use PsCheckout\Core\Order\Action\CreateValidateOrderDataAction;
use PsCheckout\Core\Order\Validator\OrderAmountValidator;
use PsCheckout\Core\Order\Validator\OrderAmountValidatorInterface;
use PsCheckout\Core\OrderState\Configuration\OrderStateConfiguration;
use PsCheckout\Core\OrderState\Service\OrderStateMapperInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CurrencyInterface;

class CreateValidateOrderDataActionTest extends TestCase
{
    private $context;

    private $orderStateMapper;

    private $currency;

    private $orderAmountValidator;

    private $payPalOrderRepository;

    private $cart;

    private $action;

    protected function setUp(): void
    {
        $this->context = $this->createMock(ContextInterface::class);
        $this->orderStateMapper = $this->createMock(OrderStateMapperInterface::class);
        $this->currency = $this->createMock(CurrencyInterface::class);
        $this->orderAmountValidator = $this->createMock(OrderAmountValidatorInterface::class);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);

        $this->cart = $this->createMock(\Cart::class);
        $this->cart->id = 1;
        $this->cart->id_currency = 3;
        $this->cart->secure_key = 'test-secure-key';
        $this->context->method('getCart')->willReturn($this->cart);

        $payPalOrder = $this->createMock(PayPalOrder::class);
        $payPalOrder->method('getFundingSource')->willReturn('paypal');
        $this->payPalOrderRepository->method('getOneBy')->willReturn($payPalOrder);

        $this->orderStateMapper->method('getIdByKey')->willReturnMap([
            [OrderStateConfiguration::PS_CHECKOUT_STATE_COMPLETED, 2],
            [OrderStateConfiguration::PS_CHECKOUT_STATE_PARTIALLY_PAID, 16],
            [OrderStateConfiguration::PS_CHECKOUT_STATE_PENDING, 14],
        ]);

        $this->action = new CreateValidateOrderDataAction(
            $this->context,
            $this->orderStateMapper,
            $this->currency,
            $this->orderAmountValidator,
            $this->payPalOrderRepository
        );
    }

    /**
     * @dataProvider provideCaptureIsoCodes
     */
    public function testCurrencyIdComesFromAdapterWhenCaptureExists(string $isoCode, int $expectedCurrencyId): void
    {
        $this->currency->expects($this->once())
            ->method('getIdByIsoCode')
            ->with($isoCode)
            ->willReturn($expectedCurrencyId);

        $this->cart->method('getOrderTotal')->willReturn(29.00);
        $this->orderAmountValidator->method('validate')->willReturn(OrderAmountValidator::ORDER_FULL_PAID);

        $result = $this->action->execute($this->buildResponseWithCapture($isoCode, '29.00', 'COMPLETED'));

        $this->assertSame($expectedCurrencyId, $result->getCurrencyId());
    }

    /**
     * @return array<string, array{string, int}>
     */
    public function provideCaptureIsoCodes(): array
    {
        return [
            'EUR' => ['EUR', 1],
            'USD' => ['USD', 2],
        ];
    }

    public function testCurrencyIdFallsBackToCartIdCurrencyWhenNoCapture(): void
    {
        $this->currency->expects($this->never())->method('getIdByIsoCode');

        $response = new PayPalOrderResponse(
            'TEST-ORDER-123',
            'PENDING',
            'CAPTURE',
            null,
            null,
            [['payments' => ['captures' => []]]],
            []
        );

        $result = $this->action->execute($response);

        $this->assertSame(3, $result->getCurrencyId());
    }

    public function testPendingCaptureStillResolveCurrencyViaAdapter(): void
    {
        $this->currency->expects($this->once())
            ->method('getIdByIsoCode')
            ->with('EUR')
            ->willReturn(1);

        $result = $this->action->execute($this->buildResponseWithCapture('EUR', '29.00', 'PENDING'));

        $this->assertSame(1, $result->getCurrencyId());
    }

    private function buildResponseWithCapture(string $isoCode, string $value, string $status): PayPalOrderResponse
    {
        return new PayPalOrderResponse(
            'TEST-ORDER-123',
            'COMPLETED',
            'CAPTURE',
            null,
            null,
            [[
                'payments' => ['captures' => [[
                    'id' => 'CAPTURE-123',
                    'status' => $status,
                    'amount' => ['currency_code' => $isoCode, 'value' => $value],
                ]]],
            ]],
            []
        );
    }
}
