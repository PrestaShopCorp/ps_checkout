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

namespace PsCheckout\Tests\Unit\PaymentToken\Action;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\PaymentToken\Action\SavePaymentTokenAction;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrder;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use Psr\Log\LoggerInterface;

class SavePaymentTokenActionTest extends TestCase
{
    /** @var SavePaymentTokenAction */
    private $action;

    /** @var PayPalCustomerRepositoryInterface|MockObject */
    private $customerRepository;

    /** @var PayPalOrderRepositoryInterface|MockObject */
    private $payPalOrderRepository;

    /** @var PaymentTokenRepositoryInterface|MockObject */
    private $paymentTokenRepository;

    /** @var ContextInterface|MockObject */
    private $context;

    /** @var ConfigurationInterface|MockObject */
    private $configuration;

    /** @var LoggerInterface|MockObject */
    private $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerRepository = $this->createMock(PayPalCustomerRepositoryInterface::class);
        $this->payPalOrderRepository = $this->createMock(PayPalOrderRepositoryInterface::class);
        $this->paymentTokenRepository = $this->createMock(PaymentTokenRepositoryInterface::class);
        $this->context = $this->createMock(ContextInterface::class);
        $this->configuration = $this->createMock(ConfigurationInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $customer = new \stdClass();
        $customer->id = 42;
        $this->context->method('getCustomer')->willReturn($customer);

        $this->action = new SavePaymentTokenAction(
            $this->customerRepository,
            $this->payPalOrderRepository,
            $this->paymentTokenRepository,
            $this->context,
            $this->configuration,
            $this->logger
        );
    }

    public function testItSetsPaymentTokenIdOnPayPalOrderAfterSavingToken(): void
    {
        $orderId = 'ORDER-123';
        $vaultId = 'vault-token-456';
        $customerId = 'CUST-789';
        $merchantId = 'MERCHANT-001';

        $payPalOrder = new PayPalOrder(
            $orderId,
            1,
            'CAPTURE',
            'card',
            'COMPLETED',
            [],
            'live',
            true,
            false,
            [PayPalConfiguration::PS_CHECKOUT_CUSTOMER_INTENT_FAVORITE]
        );

        $payPalOrderResponse = new PayPalOrderResponse(
            $orderId,
            'COMPLETED',
            'CAPTURE',
            null,
            [
                'card' => [
                    'last_digits' => '1234',
                    'brand' => 'VISA',
                    'attributes' => [
                        'vault' => [
                            'id' => $vaultId,
                            'status' => 'VAULTED',
                            'customer' => [
                                'id' => $customerId,
                            ],
                        ],
                    ],
                ],
            ],
            [['amount' => ['value' => '10.00', 'currency_code' => 'EUR']]],
            []
        );

        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => $orderId])
            ->willReturn($payPalOrder);

        $this->configuration->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
            ->willReturn($merchantId);

        $this->paymentTokenRepository->expects($this->once())
            ->method('save');

        $this->payPalOrderRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PayPalOrder $order) use ($vaultId) {
                return $order->getPaymentTokenId() === $vaultId;
            }))
            ->willReturn(true);

        $this->action->execute($payPalOrderResponse);
    }

    public function testItSetsPaymentTokenIdOnAuthorizeIntent(): void
    {
        $orderId = 'ORDER-AUTH-123';
        $vaultId = 'vault-token-auth-456';
        $customerId = 'CUST-AUTH-789';
        $merchantId = 'MERCHANT-001';

        $payPalOrder = new PayPalOrder(
            $orderId,
            2,
            'AUTHORIZE',
            'card',
            'COMPLETED',
            [],
            'live',
            true,
            false,
            []
        );

        $payPalOrderResponse = new PayPalOrderResponse(
            $orderId,
            'COMPLETED',
            'AUTHORIZE',
            null,
            [
                'card' => [
                    'last_digits' => '5678',
                    'brand' => 'MASTERCARD',
                    'attributes' => [
                        'vault' => [
                            'id' => $vaultId,
                            'status' => 'VAULTED',
                            'customer' => [
                                'id' => $customerId,
                            ],
                        ],
                    ],
                ],
            ],
            [['amount' => ['value' => '25.00', 'currency_code' => 'USD']]],
            []
        );

        $this->payPalOrderRepository->expects($this->once())
            ->method('getOneBy')
            ->with(['id' => $orderId])
            ->willReturn($payPalOrder);

        $this->configuration->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
            ->willReturn($merchantId);

        $this->paymentTokenRepository->expects($this->once())
            ->method('save');

        $this->payPalOrderRepository->expects($this->once())
            ->method('save')
            ->with($this->callback(function (PayPalOrder $order) use ($vaultId) {
                return $order->getPaymentTokenId() === $vaultId;
            }))
            ->willReturn(true);

        $this->action->execute($payPalOrderResponse);
    }

    public function testItDoesNotSavePayPalOrderWhenNoVaultData(): void
    {
        $payPalOrderResponse = new PayPalOrderResponse(
            'ORDER-NO-VAULT',
            'COMPLETED',
            'CAPTURE',
            null,
            [
                'card' => [
                    'last_digits' => '1234',
                    'brand' => 'VISA',
                ],
            ],
            [['amount' => ['value' => '10.00', 'currency_code' => 'EUR']]],
            []
        );

        $this->payPalOrderRepository->expects($this->never())
            ->method('save');

        $this->action->execute($payPalOrderResponse);
    }
}
