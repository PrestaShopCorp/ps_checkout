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

namespace PsCheckout\Core\Tests\Unit\PayPal\Order\Action;

use Exception;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\OrderHttpClientInterface;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Core\PaymentToken\Action\DeletePaymentTokenActionInterface;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\Order\Action\CreatePayPalOrderAction;
use PsCheckout\Core\PayPal\Order\Cache\PayPalOrderCacheInterface;
use PsCheckout\Core\PayPal\Order\Processor\CreatePayPalOrderProcessorInterface;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRepositoryInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class CreatePayPalOrderActionTest extends TestCase
{
    /** @var OrderHttpClientInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $orderHttpClient;

    /** @var CreatePayPalOrderAction */
    private $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderHttpClient = $this->createMock(OrderHttpClientInterface::class);
        $this->action = new CreatePayPalOrderAction(
            $this->createMock(ContextInterface::class),
            $this->createMock(PayPalCustomerRepositoryInterface::class),
            $this->createMock(PayPalOrderRepositoryInterface::class),
            $this->orderHttpClient,
            $this->createMock(OrderPayloadBuilderInterface::class),
            $this->createMock(PresenterInterface::class),
            $this->createMock(CreatePayPalOrderProcessorInterface::class),
            $this->createMock(PayPalOrderCacheInterface::class),
            $this->createMock(DeletePaymentTokenActionInterface::class)
        );
    }

    private function createPayPalOrder(): void
    {
        $method = new \ReflectionMethod(CreatePayPalOrderAction::class, 'createPayPalOrder');
        $method->setAccessible(true);

        $method->invoke($this->action, ['intent' => 'CAPTURE']);
    }

    public function testShopNotRegisteredInMduIsNotWrappedSoThatItKeepsItsDedicatedHandling(): void
    {
        $mduException = new PsCheckoutException(
            'Shop is not registered in the PrestaShop Checkout services.',
            PsCheckoutException::SHOP_NOT_REGISTERED_IN_MDU
        );
        $this->orderHttpClient->method('createOrder')->willThrowException($mduException);

        try {
            $this->createPayPalOrder();
            $this->fail('An exception was expected');
        } catch (PsCheckoutException $exception) {
            $this->assertSame($mduException, $exception);
            $this->assertSame(PsCheckoutException::SHOP_NOT_REGISTERED_IN_MDU, $exception->getCode());
        }
    }

    public function testOtherErrorsAreStillWrappedAsUnknown(): void
    {
        $cause = new Exception('Connection reset');
        $this->orderHttpClient->method('createOrder')->willThrowException($cause);

        try {
            $this->createPayPalOrder();
            $this->fail('An exception was expected');
        } catch (PsCheckoutException $exception) {
            $this->assertSame(PsCheckoutException::UNKNOWN, $exception->getCode());
            $this->assertSame('Failed to create order', $exception->getMessage());
            $this->assertSame($cause, $exception->getPrevious());
        }
    }

    public function testOtherPsCheckoutExceptionsAreStillWrappedAsUnknown(): void
    {
        $cause = new PsCheckoutException('Something else', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
        $this->orderHttpClient->method('createOrder')->willThrowException($cause);

        try {
            $this->createPayPalOrder();
            $this->fail('An exception was expected');
        } catch (PsCheckoutException $exception) {
            $this->assertSame(PsCheckoutException::UNKNOWN, $exception->getCode());
            $this->assertSame($cause, $exception->getPrevious());
        }
    }
}
