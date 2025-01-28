<?php

declare(strict_types=1);

namespace Tests\Unit\PayPal\Order\CommandHandler;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\CapturePayPalOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use PPsr\SimpleCache\CacheInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

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
        $eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);
        $prestashopContextMock = $this->createMock(PrestashopContext::class);
        $paypalCustomerRepositoryMock = $this->createMock(PayPalCustomerRepository::class);
        $paypalOrderRepositoryMock = $this->createMock(PayPalOrderRepository::class);
        $paypalConfigurationMock = $this->createMock(PayPalConfiguration::class);
        $loggerMock = $this->createMock(LoggerInterface::class);
        $cacheMock = $this->createMock(CacheInterface::class);

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
        $response = file_get_contents(dirname(__DIR__));
        $capturePayPalOrderCommand = $this->buildCapturePayPalOrderCommand('paypal');
        $this->responseMock->method('getBody')->willReturn($response);
        $expectedPaypalCapture = [];

        $capturePayPal = $this->capturePayPalOrderCommandHandler->handle($capturePayPalOrderCommand);

        $this->assertEquals($expectedPaypalCapture, $capturePayPal);
    }


    public function payment_with_paypal_fails()
    {
        $response = file_get_contents(dirname(__DIR__));
        $this->responseMock->method('getBody')->willReturn($response);

        $this->expectException(PayPalException::class);
    }


    public function payment_by_card_succeeds()
    {
        $capturePayPalOrderCommand = $this->buildCapturePayPalOrderCommand('card');
        $this->responseMock->method('getBody')->willReturn('');
    }


    public function payment_by_card_fails()
    {
        $this->responseMock->method('getBody')->willReturn('');
        $this->expectException(PayPalException::class);
    }

    private function buildCapturePayPalOrderCommand($fundingSource)
    {
        $orderId = '2AC63827BP9351315';
        return new CapturePayPalOrderCommand($orderId, $fundingSource);
    }
}
