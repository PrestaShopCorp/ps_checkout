<?php

declare(strict_types=1);

namespace Tests\Unit\PayPal\Order\CommandHandler;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Cache\PayPalOrderCache;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\CapturePayPalOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class CapturePayPalOrderCommandHandlerTest extends TestCase
{
    const PAYPAL_PAYMENT_SUCCESS = 'success';
    const PAYPAL_PAYMENT_FAILURE = 'failure';
    const CARD_PAYMENT_SUCCESS = 'success';
    const CARD_PAYMENT_FAILURE = 'failure';

    private $capturePayPalOrderCommandHandler;
    private $responseMock;

    protected function setUp()
    {
        $maaslandHttpClientMock = $this->createMock(MaaslandHttpClient::class);
        $eventDispatcherMock = $this->createMock(EventDispatcher::class);
        $prestashopContextMock = $this->createMock(PrestashopContext::class);
        $paypalCustomerRepositoryMock = $this->createMock(PayPalCustomerRepository::class);
        $paypalOrderRepositoryMock = $this->createMock(PayPalOrderRepository::class);
        $paypalConfigurationMock = $this->createMock(PayPalConfiguration::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $cacheMock = $this->createMock(PayPalOrderCache::class);

        $this->responseMock = $this->createMock(ResponseInterface::class);

        $this->capturePayPalOrderCommandHandler = new CapturePayPalOrderCommandHandler(
            $maaslandHttpClientMock,
            $eventDispatcherMock,
            $cacheMock,
            $prestashopContextMock,
            $paypalCustomerRepositoryMock,
            $paypalConfigurationMock,
            $paypalOrderRepositoryMock,
            $loggerMock
        );

        $maaslandHttpClientMock->method('captureOrder')->willReturn($this->responseMock);
        $prestashopContextMock->method('getCustomerId')->willReturn(null);
        $paypalConfigurationMock->method('getMerchantId')->willReturn(null);
        $paypalOrderRepositoryMock->method('getPayPalOrderById')->willReturn(null);
    }

    /** @test */
    public function payment_with_paypal_succeeds()
    {
        $this->responseMock->method('getBody')->willReturn('');
        $capturePayPalOrderCommand = new CapturePayPalOrderCommand();
        $expectedPaypalCapture = [];

        $capturePayPal = $this->capturePayPalOrderCommandHandler->handle($capturePayPalOrderCommand);

        $this->assertEquals($expectedPaypalCapture, $capturePayPal);
    }

    /** @test */
    public function payment_with_paypal_fails()
    {
        $this->responseMock->method('getBody')->willReturn('');
    }

    /** @test */
    public function payment_by_card_succeeds()
    {
        $this->responseMock->method('getBody')->willReturn('');
    }

    /** @test */
    public function payment_by_card_fails()
    {
        $this->responseMock->method('getBody')->willReturn('');
    }
}
