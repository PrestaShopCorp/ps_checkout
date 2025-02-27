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

namespace Tests\Unit\PayPal\Order\CommandHandler;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Client\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Http\HttpClientInterface;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CapturePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\CapturePayPalOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCaptureDeclinedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event\PayPalCapturePendingEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\PayPalCaptureStatus;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenDeletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalOrderRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class CapturePayPalOrderCommandHandlerTest extends TestCase
{
    /**
     * @dataProvider completedPayPalOrderProvider
     */
    public function testCapturePayPalOrder(array $order)
    {
        $fundingSource = isset($order['payment_source']) ? key($order['payment_source']) : null;
        $customerId = 1;
        $customerIdPayPal = isset($order['payment_source'][$fundingSource]['attributes']['vault']['customer']['id']) ? $order['payment_source'][$fundingSource]['attributes']['vault']['customer']['id'] : null;
        $merchantId = isset($order['purchase_units'][0]['payee']['merchant_id']) ? $order['purchase_units'][0]['payee']['merchant_id'] : null;
        $capture = isset($order['purchase_units'][0]['payments']['captures'][0]) ? $order['purchase_units'][0]['payments']['captures'][0] : null;

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(json_encode($order));

        $maaslandHttpClient = $this->createMock(MaaslandHttpClient::class);
        $maaslandHttpClient->method('captureOrder')->willReturn($response);

        $orderPayPalCache = $this->createMock(CacheInterface::class);
        $orderPayPalCache->method('get')->willReturn($order);

        $prestaShopContext = $this->createMock(PrestaShopContext::class);
        $payPalCustomerRepository = $this->createMock(PayPalCustomerRepository::class);
        if (isset($order['payment_source'][$fundingSource]['attributes']['vault'])) {
            $prestaShopContext->method('getCustomerId')->willReturn($customerId);
            $payPalCustomerRepository->expects($this->once())->method('save')->with(new CustomerId($customerId), new PayPalCustomerId($customerIdPayPal));
        }

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $expectedEvents = [];

        if (isset($order['payment_source'][$fundingSource]['attributes']['vault']['id'])) {
            $resource = $order['payment_source'][$fundingSource]['attributes']['vault'];
            $resource['metadata'] = [
                'order_id' => $order['id'],
            ];
            $paymentSource = $order['payment_source'];
            unset($paymentSource[$fundingSource]['attributes']['vault']);
            $resource['payment_source'] = $paymentSource;
            $resource['payment_source'][$fundingSource]['verification_status'] = $resource['status'];
            $expectedEvents[] = [new PaymentTokenCreatedEvent($resource, $merchantId)];
        }

        if (isset($order['payment_source']['card'])) {
            $expectedEvents[] = [new PaymentTokenUpdatedEvent($order, $merchantId)];
        }

        switch ($order['status']) {
            case 'COMPLETED':
                $expectedEvents[] = [new PayPalOrderCompletedEvent($order['id'], $order)];
                break;
        }

        if (isset($capture['status'])) {
            switch ($capture['status']) {
                case 'COMPLETED':
                    $expectedEvents[] = [new PayPalCaptureCompletedEvent($capture['id'], $order['id'], $capture)];
                    break;
                case 'FAILED':
                case 'DECLINED':
                    $expectedEvents[] = [new PayPalCaptureDeclinedEvent($capture['id'], $order['id'], $capture)];
                    break;
                case 'PENDING':
                    $expectedEvents[] = [new PayPalCapturePendingEvent($capture['id'], $order['id'], $capture)];
                    break;
            }
        }

        $eventDispatcher->expects($this->exactly(count($expectedEvents)))
            ->method('dispatch')
            ->withConsecutive(...$expectedEvents);

        $paypalOrder = $this->createMock(PayPalOrder::class);
        $paypalOrder->method('checkCustomerIntent')->willReturn(true);
        $payPalOrderRepository = $this->createMock(PayPalOrderRepository::class);
        $payPalOrderRepository->expects($this->once())->method('getPayPalOrderById')->with(new PayPalOrderId($order['id']))->willReturn($paypalOrder);

        $payPalConfiguration = $this->createMock(PayPalConfiguration::class);
        $payPalConfiguration->method('getMerchantId')->willReturn($merchantId);

        if (
            PayPalCaptureStatus::DECLINED === $capture['status']
            && false === empty($order['payment_source'])
            && false === empty($order['payment_source']['card'])
            && false === empty($capture['processor_response'])
        ) {
            $this->expectException(PsCheckoutException::class);
            $this->expectExceptionCode(PsCheckoutException::PAYPAL_PAYMENT_CARD_ERROR);
        } elseif (PayPalCaptureStatus::DECLINED === $capture['status'] || PayPalCaptureStatus::FAILED === $capture['status']) {
            $this->expectException(PsCheckoutException::class);
            $this->expectExceptionCode(PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED);
        }

        $logger = $this->createMock(LoggerInterface::class);

        $commandHandler = new CapturePayPalOrderCommandHandler(
            $maaslandHttpClient,
            $eventDispatcher,
            $orderPayPalCache,
            $prestaShopContext,
            $payPalCustomerRepository,
            $payPalOrderRepository,
            $payPalConfiguration,
            $logger
        );
        $commandHandler->handle(new CapturePayPalOrderCommand($order['id'], $fundingSource));
    }

    /**
     * @dataProvider captureErrorProvider
     */
    public function testCapturePayPalOrderResponseDeletesPaymentToken(array $request, $errorCode, $response)
    {
        $merchantId = '12345';

        $httpClient = $this->createMock(HttpClientInterface::class);

        $maaslandHttpClient = new MaaslandHttpClient($httpClient);

        $httpRequest = new Request('POST', '/payments/order/capture', [], json_encode($request));
        $httpResponse = new Response($errorCode, [], $response);
        $exception = new HttpException('', $httpRequest, $httpResponse);
        $httpClient->method('sendRequest')->willThrowException($exception);

        $orderPayPalCache = $this->createMock(CacheInterface::class);

        $prestaShopContext = $this->createMock(PrestaShopContext::class);
        $payPalCustomerRepository = $this->createMock(PayPalCustomerRepository::class);

        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $payPalOrderRepository = $this->createMock(PayPalOrderRepository::class);

        $payPalOrder = new PayPalOrder(
            $request['id'], 1, 'CAPTURE', $request['funding_source'], 'APPROVED'
        );

        $paymentTokenId = new PaymentTokenId('XHAHASJNSSD21');
        $payPalOrder->setPaymentTokenId($paymentTokenId);

        $payPalOrderRepository->method('getPayPalOrderById')->willReturn($payPalOrder);

        $payPalConfiguration = $this->createMock(PayPalConfiguration::class);
        $payPalConfiguration->method('getMerchantId')->willReturn($merchantId);

        $logger = $this->createMock(LoggerInterface::class);

        $commandHandler = new CapturePayPalOrderCommandHandler(
            $maaslandHttpClient,
            $eventDispatcher,
            $orderPayPalCache,
            $prestaShopContext,
            $payPalCustomerRepository,
            $payPalOrderRepository,
            $payPalConfiguration,
            $logger
        );

        $expectedEvents = [new PaymentTokenDeletedEvent(['id' => $paymentTokenId->getValue()])];

        $eventDispatcher->expects($this->exactly(count($expectedEvents)))
            ->method('dispatch')
            ->withConsecutive(...$expectedEvents);

        $this->expectException(PayPalException::class);
        $this->expectExceptionCode(PayPalException::CARD_CLOSED);

        $commandHandler->handle(new CapturePayPalOrderCommand($request['id'], $request['funding_source']));
    }

    public function completedPayPalOrderProvider()
    {
        return [
            [[
                'id' => '55P13698XR4360722',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'paypal' => [
                                'email_address' => 'sb-26qa712980505@personal.example.com',
                                'account_id' => 'VRT593XYPLRRJ',
                                'account_status' => 'VERIFIED',
                                'name' => [
                                        'given_name' => 'John',
                                        'surname' => 'Doe',
                                    ],
                                'address' => [
                                        'address_line_1' => '2211 N First Street',
                                        'address_line_2' => '17.3.160',
                                        'admin_area_2' => 'San Jose',
                                        'admin_area_1' => 'CA',
                                        'postal_code' => '95131',
                                        'country_code' => 'US',
                                    ],
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'insurance' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'shipping_discount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'description' => 'T-Shirt',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                        'name' => [
                                                'full_name' => 'John Doe',
                                            ],
                                        'address' => [
                                                'address_line_1' => 'calle Vilamar� 76993- 17469',
                                                'admin_area_2' => 'Albacete',
                                                'admin_area_1' => 'Albacete',
                                                'postal_code' => '02001',
                                                'country_code' => 'ES',
                                            ],
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '5A6137018G665835P',
                                                        'status' => 'COMPLETED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '5.38',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '94.62',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/5A6137018G665835P',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/5A6137018G665835P/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/55P13698XR4360722',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T14:00:17Z',
                                                        'update_time' => '2025-01-23T14:00:17Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'payer' => [
                        'name' => [
                                'given_name' => 'John',
                                'surname' => 'Doe',
                            ],
                        'email_address' => 'sb-26qa712980505@personal.example.com',
                        'payer_id' => 'VRT593XYPLRRJ',
                        'address' => [
                                'address_line_1' => '2211 N First Street',
                                'address_line_2' => '17.3.160',
                                'admin_area_2' => 'San Jose',
                                'admin_area_1' => 'CA',
                                'postal_code' => '95131',
                                'country_code' => 'US',
                            ],
                    ],
                'create_time' => '2025-01-23T08:00:59Z',
                'update_time' => '2025-01-23T14:00:17Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/55P13698XR4360722',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '2YU2318604145471Y',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'paypal' => [
                                'email_address' => 'sb-26qa712980505@personal.example.com',
                                'account_id' => 'VRT593XYPLRRJ',
                                'account_status' => 'VERIFIED',
                                'name' => [
                                        'given_name' => 'John',
                                        'surname' => 'Doe',
                                    ],
                                'address' => [
                                        'address_line_1' => '2211 N First Street',
                                        'address_line_2' => '17.3.160',
                                        'admin_area_2' => 'San Jose',
                                        'admin_area_1' => 'CA',
                                        'postal_code' => '95131',
                                        'country_code' => 'US',
                                    ],
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'insurance' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'shipping_discount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'description' => 'T-Shirt',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                        'name' => [
                                                'full_name' => 'John Doe',
                                            ],
                                        'address' => [
                                                'address_line_1' => 'calle Vilamar� 76993- 17469',
                                                'admin_area_2' => 'Albacete',
                                                'admin_area_1' => 'Albacete',
                                                'postal_code' => '02001',
                                                'country_code' => 'ES',
                                            ],
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '5A6137018G665835P',
                                                        'status' => 'PENDING',
                                                        'status_details' => [
                                                            'reason' => 'PENDING_REVIEW',
                                                        ],
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '5.38',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '94.62',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/5A6137018G665835P',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/5A6137018G665835P/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/55P13698XR4360722',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T14:00:17Z',
                                                        'update_time' => '2025-01-23T14:00:17Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'payer' => [
                        'name' => [
                                'given_name' => 'John',
                                'surname' => 'Doe',
                            ],
                        'email_address' => 'sb-26qa712980505@personal.example.com',
                        'payer_id' => 'VRT593XYPLRRJ',
                        'address' => [
                                'address_line_1' => '2211 N First Street',
                                'address_line_2' => '17.3.160',
                                'admin_area_2' => 'San Jose',
                                'admin_area_1' => 'CA',
                                'postal_code' => '95131',
                                'country_code' => 'US',
                            ],
                    ],
                'create_time' => '2025-01-23T08:00:59Z',
                'update_time' => '2025-01-23T14:00:17Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2YU2318604145471Y',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '7LG21592AH387725G',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'paypal' => [
                                'email_address' => 'sb-26qa712980505@personal.example.com',
                                'account_id' => 'VRT593XYPLRRJ',
                                'account_status' => 'VERIFIED',
                                'name' => [
                                        'given_name' => 'John',
                                        'surname' => 'Doe',
                                    ],
                                'address' => [
                                        'address_line_1' => '2211 N First Street',
                                        'address_line_2' => '17.3.160',
                                        'admin_area_2' => 'San Jose',
                                        'admin_area_1' => 'CA',
                                        'postal_code' => '95131',
                                        'country_code' => 'US',
                                    ],
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'insurance' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'shipping_discount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'description' => 'T-Shirt',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                        'name' => [
                                                'full_name' => 'John Doe',
                                            ],
                                        'address' => [
                                                'address_line_1' => 'calle Vilamar� 76993- 17469',
                                                'admin_area_2' => 'Albacete',
                                                'admin_area_1' => 'Albacete',
                                                'postal_code' => '02001',
                                                'country_code' => 'ES',
                                            ],
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '5A6137018G665835P',
                                                        'status' => 'DECLINED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '5.38',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '94.62',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/3GD207524B790260J',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/3GD207524B790260J/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/7LG21592AH387725G',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T14:00:17Z',
                                                        'update_time' => '2025-01-23T14:00:17Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'payer' => [
                        'name' => [
                                'given_name' => 'John',
                                'surname' => 'Doe',
                            ],
                        'email_address' => 'sb-26qa712980505@personal.example.com',
                        'payer_id' => 'VRT593XYPLRRJ',
                        'address' => [
                                'address_line_1' => '2211 N First Street',
                                'address_line_2' => '17.3.160',
                                'admin_area_2' => 'San Jose',
                                'admin_area_1' => 'CA',
                                'postal_code' => '95131',
                                'country_code' => 'US',
                            ],
                    ],
                'create_time' => '2025-01-23T08:00:59Z',
                'update_time' => '2025-01-23T14:00:17Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/7LG21592AH387725G',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '9NJ975522N494903Y',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'card' => [
                                'name' => 'aaa vvv',
                                'last_digits' => '4424',
                                'expiry' => '2025-01',
                                'brand' => 'VISA',
                                'available_networks' => [
                                        0 => 'VISA',
                                    ],
                                'type' => 'DEBIT',
                                'authentication_result' => [
                                        'liability_shift' => 'POSSIBLE',
                                        'three_d_secure' => [
                                                'enrollment_status' => 'Y',
                                                'authentication_status' => 'Y',
                                            ],
                                    ],
                                'attributes' => [
                                        'vault' => [
                                                'id' => '3bk80394dr764533j',
                                                'status' => 'VAULTED',
                                                'customer' => [
                                                        'id' => 'WTEiJKlLIl',
                                                    ],
                                                'links' => [
                                                        0 => [
                                                                'href' => 'https://api.sandbox.paypal.com/v3/vault/payment-tokens/3bk80394dr764533j',
                                                                'rel' => 'self',
                                                                'method' => 'GET',
                                                            ],
                                                        1 => [
                                                                'href' => 'https://api.sandbox.paypal.com/v3/vault/payment-tokens/3bk80394dr764533j',
                                                                'rel' => 'delete',
                                                                'method' => 'DELETE',
                                                            ],
                                                        2 => [
                                                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/3RJ30031VD593541J',
                                                                'rel' => 'up',
                                                                'method' => 'GET',
                                                            ],
                                                    ],
                                            ],
                                    ],
                                'bin_details' => [
                                        'bin' => '41470443',
                                        'bin_country_code' => 'FR',
                                    ],
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => '1',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '43.20',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '29.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '8.40',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'tax_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '5.80',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'merchant_id' => 'U5XK34UWT2AFA',
                                        'display_data' => [
                                                'brand_name' => 'PrestaShop',
                                            ],
                                    ],
                                'payment_instruction' => [
                                        'disbursement_mode' => 'INSTANT',
                                    ],
                                'description' => 'Checking out with your cart #21 from PrestaShop',
                                'custom_id' => '7b89b49a-fde3-4bdc-a5c2-5549f109a416@1737622652934',
                                'invoice_id' => '',
                                'soft_descriptor' => 'JOHNDOESTES',
                                'items' => [
                                        0 => [
                                                'name' => 'Affiche encadrée The best is yet to come',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '29.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '5.80',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Dimension : 40x60cm',
                                                'sku' => 'demo_6',
                                                'category' => 'PHYSICAL_GOODS',
                                            ],
                                    ],
                                'shipping' => [
                                        'name' => '***',
                                        'address' => [
                                                'address_line_1' => '***',
                                                'address_line_2' => '***',
                                                'admin_area_2' => 'Paris ',
                                                'postal_code' => '75002',
                                                'country_code' => 'FR',
                                            ],
                                    ],
                                'supplementary_data' => [
                                        'card' => [
                                                'level_2' => [
                                                        'tax_total' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '5.8',
                                                            ],
                                                    ],
                                                'level_3' => [
                                                        'shipping_amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '8.4',
                                                            ],
                                                        'duty_amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '43.2',
                                                            ],
                                                        'discount_amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '0.0',
                                                            ],
                                                        'shipping_address' => [
                                                                'address_line_1' => '16, Main street',
                                                                'address_line_2' => '2nd floor',
                                                                'admin_area_2' => 'Paris ',
                                                                'postal_code' => '75002',
                                                                'country_code' => 'FR',
                                                            ],
                                                        'line_items' => [
                                                                0 => [
                                                                        'name' => 'Affiche encadrée The best is yet to come',
                                                                        'unit_amount' => [
                                                                                'currency_code' => 'EUR',
                                                                                'value' => '29.0',
                                                                            ],
                                                                        'tax' => [
                                                                                'currency_code' => 'EUR',
                                                                                'value' => '5.8',
                                                                            ],
                                                                        'quantity' => '1',
                                                                        'description' => 'Dimension : 40x60cm',
                                                                    ],
                                                            ],
                                                    ],
                                            ],
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '904491908K774600C',
                                                        'status' => 'DECLINED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '43.20',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'NOT_ELIGIBLE',
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '43.20',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '43.20',
                                                                    ],
                                                            ],
                                                        'custom_id' => '7b89b49a-fde3-4bdc-a5c2-5549f109a416@1737622652934',
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/904491908K774600C',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/904491908K774600C/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/9NJ975522N494903Y',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T08:57:45Z',
                                                        'update_time' => '2025-01-23T08:57:45Z',
                                                        'network_transaction_reference' => [
                                                                'id' => '133455343573488',
                                                                'network' => 'VISA',
                                                            ],
                                                        'processor_response' => [
                                                                'avs_code' => 'Z',
                                                                'cvv_code' => 'N',
                                                                'response_code' => '00N7',
                                                            ],
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:57:33Z',
                'update_time' => '2025-01-23T08:57:45Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/9NJ975522N494903Y',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '92163488YM718505A',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'card' => [
                                'name' => 'aaa vvv',
                                'last_digits' => '4424',
                                'expiry' => '2025-01',
                                'brand' => 'VISA',
                                'available_networks' => [
                                        0 => 'VISA',
                                    ],
                                'type' => 'DEBIT',
                                'authentication_result' => [
                                        'liability_shift' => 'POSSIBLE',
                                        'three_d_secure' => [
                                                'enrollment_status' => 'Y',
                                                'authentication_status' => 'Y',
                                            ],
                                    ],
                                'bin_details' => [
                                        'bin' => '41470443',
                                        'bin_country_code' => 'FR',
                                    ],
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => '1',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '43.20',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '29.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '8.40',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'tax_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '5.80',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'merchant_id' => 'U5XK34UWT2AFA',
                                        'display_data' => [
                                                'brand_name' => 'PrestaShop',
                                            ],
                                    ],
                                'payment_instruction' => [
                                        'disbursement_mode' => 'INSTANT',
                                    ],
                                'description' => 'Checking out with your cart #21 from PrestaShop',
                                'custom_id' => '7b89b49a-fde3-4bdc-a5c2-5549f109a416@1737622652934',
                                'invoice_id' => '',
                                'soft_descriptor' => 'JOHNDOESTES',
                                'items' => [
                                        0 => [
                                                'name' => 'Affiche encadrée The best is yet to come',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '29.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '5.80',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Dimension : 40x60cm',
                                                'sku' => 'demo_6',
                                                'category' => 'PHYSICAL_GOODS',
                                            ],
                                    ],
                                'shipping' => [
                                        'name' => '***',
                                        'address' => [
                                                'address_line_1' => '***',
                                                'address_line_2' => '***',
                                                'admin_area_2' => 'Paris ',
                                                'postal_code' => '75002',
                                                'country_code' => 'FR',
                                            ],
                                    ],
                                'supplementary_data' => [
                                        'card' => [
                                                'level_2' => [
                                                        'tax_total' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '5.8',
                                                            ],
                                                    ],
                                                'level_3' => [
                                                        'shipping_amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '8.4',
                                                            ],
                                                        'duty_amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '43.2',
                                                            ],
                                                        'discount_amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '0.0',
                                                            ],
                                                        'shipping_address' => [
                                                                'address_line_1' => '16, Main street',
                                                                'address_line_2' => '2nd floor',
                                                                'admin_area_2' => 'Paris ',
                                                                'postal_code' => '75002',
                                                                'country_code' => 'FR',
                                                            ],
                                                        'line_items' => [
                                                                0 => [
                                                                        'name' => 'Affiche encadrée The best is yet to come',
                                                                        'unit_amount' => [
                                                                                'currency_code' => 'EUR',
                                                                                'value' => '29.0',
                                                                            ],
                                                                        'tax' => [
                                                                                'currency_code' => 'EUR',
                                                                                'value' => '5.8',
                                                                            ],
                                                                        'quantity' => '1',
                                                                        'description' => 'Dimension : 40x60cm',
                                                                    ],
                                                            ],
                                                    ],
                                            ],
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '36V319627Y4223722',
                                                        'status' => 'DECLINED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '43.20',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'NOT_ELIGIBLE',
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '43.20',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '43.20',
                                                                    ],
                                                            ],
                                                        'custom_id' => '7b89b49a-fde3-4bdc-a5c2-5549f109a416@1737622652934',
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/36V319627Y4223722',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/36V319627Y4223722/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/92163488YM718505A',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T08:57:45Z',
                                                        'update_time' => '2025-01-23T08:57:45Z',
                                                        'network_transaction_reference' => [
                                                                'id' => '133455343573488',
                                                                'network' => 'VISA',
                                                            ],
                                                        'processor_response' => [
                                                                'avs_code' => 'Y',
                                                                'cvv_code' => 'S',
                                                                'response_code' => '0000',
                                                            ],
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:57:33Z',
                'update_time' => '2025-01-23T08:57:45Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/92163488YM718505A',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '2PT567357H024701J',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'bancontact' => [
                                'name' => 'John Doe',
                                'country_code' => 'BE',
                                'bic' => 'ABNANL2A',
                                'iban_last_chars' => '9344',
                            ],
                    ],
                'processing_instruction' => 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL',
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '34F772786F853570N',
                                                        'status' => 'COMPLETED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '5.38',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '94.62',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/34F772786F853570N',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/34F772786F853570N/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2PT567357H024701J',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T08:06:45Z',
                                                        'update_time' => '2025-01-23T08:06:45Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:06:20Z',
                'update_time' => '2025-01-23T08:06:45Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2PT567357H024701J',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '4XH97325YE9580105',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'blik' => [
                                'name' => 'John Doe',
                                'country_code' => 'PL',
                                'email' => 'buyer@example.com',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'PLN',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '100.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '0.00',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '0.00',
                                                    ],
                                                'insurance' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '0.00',
                                                    ],
                                                'shipping_discount' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '0.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'description' => 'T-Shirt',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '100.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '0.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '8N0189488V5987948',
                                                        'status' => 'COMPLETED',
                                                        'amount' => [
                                                                'currency_code' => 'PLN',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'PLN',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'PLN',
                                                                        'value' => '6.88',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'PLN',
                                                                        'value' => '93.12',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/8N0189488V5987948',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/8N0189488V5987948/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4XH97325YE9580105',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T14:06:01Z',
                                                        'update_time' => '2025-01-23T14:06:01Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:12:31Z',
                'update_time' => '2025-01-23T14:06:01Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4XH97325YE9580105',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '2RS70921X6989840S',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'eps' => [
                                'name' => 'John Doe',
                                'country_code' => 'AT',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'insurance' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'shipping_discount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'description' => 'T-Shirt',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '78954334YX5974519',
                                                        'status' => 'COMPLETED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '5.38',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '94.62',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/78954334YX5974519',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/78954334YX5974519/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2RS70921X6989840S',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T14:06:51Z',
                                                        'update_time' => '2025-01-23T14:06:51Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:13:44Z',
                'update_time' => '2025-01-23T14:06:51Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2RS70921X6989840S',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '29L95026GT2940426',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'ideal' => [
                                'name' => 'John Doe',
                                'country_code' => 'NL',
                                'bic' => 'INGBNL2A',
                                'iban_last_chars' => '9874',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'insurance' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'shipping_discount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'description' => 'T-Shirt',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '55U675180K6907839',
                                                        'status' => 'COMPLETED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '5.38',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '94.62',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/55U675180K6907839',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/55U675180K6907839/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/29L95026GT2940426',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T14:07:52Z',
                                                        'update_time' => '2025-01-23T14:07:52Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:14:50Z',
                'update_time' => '2025-01-23T14:07:52Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/29L95026GT2940426',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '4T58422450971401N',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'mybank' => [
                                'name' => 'John Doe',
                                'country_code' => 'IT',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'insurance' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'shipping_discount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'description' => 'T-Shirt',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '3VU305076K330470D',
                                                        'status' => 'COMPLETED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '5.38',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '94.62',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/3VU305076K330470D',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/3VU305076K330470D/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4T58422450971401N',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T14:12:06Z',
                                                        'update_time' => '2025-01-23T14:12:06Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:15:39Z',
                'update_time' => '2025-01-23T14:12:06Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4T58422450971401N',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '2XB29141AM710800Y',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                        'p24' => [
                                'name' => 'John Doe',
                                'email' => 'john.doe@example.com',
                                'country_code' => 'PL',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'shipping' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'handling' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'insurance' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'shipping_discount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'description' => 'T-Shirt',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'tax' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '0.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                    ],
                                'payments' => [
                                        'captures' => [
                                                0 => [
                                                        'id' => '2CR16480LT4151742',
                                                        'status' => 'COMPLETED',
                                                        'amount' => [
                                                                'currency_code' => 'EUR',
                                                                'value' => '100.00',
                                                            ],
                                                        'final_capture' => true,
                                                        'disbursement_mode' => 'INSTANT',
                                                        'seller_protection' => [
                                                                'status' => 'ELIGIBLE',
                                                                'dispute_categories' => [
                                                                        0 => 'ITEM_NOT_RECEIVED',
                                                                        1 => 'UNAUTHORIZED_TRANSACTION',
                                                                    ],
                                                            ],
                                                        'seller_receivable_breakdown' => [
                                                                'gross_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '100.00',
                                                                    ],
                                                                'paypal_fee' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '5.38',
                                                                    ],
                                                                'net_amount' => [
                                                                        'currency_code' => 'EUR',
                                                                        'value' => '94.62',
                                                                    ],
                                                            ],
                                                        'links' => [
                                                                0 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/2CR16480LT4151742',
                                                                        'rel' => 'self',
                                                                        'method' => 'GET',
                                                                    ],
                                                                1 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/2CR16480LT4151742/refund',
                                                                        'rel' => 'refund',
                                                                        'method' => 'POST',
                                                                    ],
                                                                2 => [
                                                                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2XB29141AM710800Y',
                                                                        'rel' => 'up',
                                                                        'method' => 'GET',
                                                                    ],
                                                            ],
                                                        'create_time' => '2025-01-23T14:13:14Z',
                                                        'update_time' => '2025-01-23T14:13:14Z',
                                                    ],
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:16:24Z',
                'update_time' => '2025-01-23T14:13:14Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2XB29141AM710800Y',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                    ],
            ]],
            [[
                'id' => '92163488YM718505A',
                'intent' => 'CAPTURE',
                'status' => 'COMPLETED',
                'payment_source' => [
                    'card' => [
                        'name' => 'aaa vvv',
                        'last_digits' => '4424',
                        'expiry' => '2025-01',
                        'brand' => 'VISA',
                        'available_networks' => [
                            0 => 'VISA',
                        ],
                        'type' => 'DEBIT',
                        'authentication_result' => [
                            'liability_shift' => 'POSSIBLE',
                            'three_d_secure' => [
                                'enrollment_status' => 'Y',
                                'authentication_status' => 'Y',
                            ],
                        ],
                        'bin_details' => [
                            'bin' => '41470443',
                            'bin_country_code' => 'FR',
                        ],
                    ],
                ],
                'purchase_units' => [
                    0 => [
                        'reference_id' => '1',
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => '43.20',
                            'breakdown' => [
                                'item_total' => [
                                    'currency_code' => 'EUR',
                                    'value' => '29.00',
                                ],
                                'shipping' => [
                                    'currency_code' => 'EUR',
                                    'value' => '8.40',
                                ],
                                'handling' => [
                                    'currency_code' => 'EUR',
                                    'value' => '0.00',
                                ],
                                'tax_total' => [
                                    'currency_code' => 'EUR',
                                    'value' => '5.80',
                                ],
                            ],
                        ],
                        'payee' => [
                            'merchant_id' => 'U5XK34UWT2AFA',
                            'display_data' => [
                                'brand_name' => 'PrestaShop',
                            ],
                        ],
                        'payment_instruction' => [
                            'disbursement_mode' => 'INSTANT',
                        ],
                        'description' => 'Checking out with your cart #21 from PrestaShop',
                        'custom_id' => '7b89b49a-fde3-4bdc-a5c2-5549f109a416@1737622652934',
                        'invoice_id' => '',
                        'soft_descriptor' => 'JOHNDOESTES',
                        'items' => [
                            0 => [
                                'name' => 'Affiche encadrée The best is yet to come',
                                'unit_amount' => [
                                    'currency_code' => 'EUR',
                                    'value' => '29.00',
                                ],
                                'tax' => [
                                    'currency_code' => 'EUR',
                                    'value' => '5.80',
                                ],
                                'quantity' => '1',
                                'description' => 'Dimension : 40x60cm',
                                'sku' => 'demo_6',
                                'category' => 'PHYSICAL_GOODS',
                            ],
                        ],
                        'shipping' => [
                            'name' => '***',
                            'address' => [
                                'address_line_1' => '***',
                                'address_line_2' => '***',
                                'admin_area_2' => 'Paris ',
                                'postal_code' => '75002',
                                'country_code' => 'FR',
                            ],
                        ],
                        'supplementary_data' => [
                            'card' => [
                                'level_2' => [
                                    'tax_total' => [
                                        'currency_code' => 'EUR',
                                        'value' => '5.8',
                                    ],
                                ],
                                'level_3' => [
                                    'shipping_amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '8.4',
                                    ],
                                    'duty_amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '43.2',
                                    ],
                                    'discount_amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '0.0',
                                    ],
                                    'shipping_address' => [
                                        'address_line_1' => '16, Main street',
                                        'address_line_2' => '2nd floor',
                                        'admin_area_2' => 'Paris ',
                                        'postal_code' => '75002',
                                        'country_code' => 'FR',
                                    ],
                                    'line_items' => [
                                        0 => [
                                            'name' => 'Affiche encadrée The best is yet to come',
                                            'unit_amount' => [
                                                'currency_code' => 'EUR',
                                                'value' => '29.0',
                                            ],
                                            'tax' => [
                                                'currency_code' => 'EUR',
                                                'value' => '5.8',
                                            ],
                                            'quantity' => '1',
                                            'description' => 'Dimension : 40x60cm',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'payments' => [
                            'captures' => [
                                0 => [
                                    'id' => '36V319627Y4223722',
                                    'status' => 'DECLINED',
                                    'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '43.20',
                                    ],
                                    'final_capture' => true,
                                    'disbursement_mode' => 'INSTANT',
                                    'seller_protection' => [
                                        'status' => 'NOT_ELIGIBLE',
                                    ],
                                    'seller_receivable_breakdown' => [
                                        'gross_amount' => [
                                            'currency_code' => 'EUR',
                                            'value' => '43.20',
                                        ],
                                        'net_amount' => [
                                            'currency_code' => 'EUR',
                                            'value' => '43.20',
                                        ],
                                    ],
                                    'custom_id' => '7b89b49a-fde3-4bdc-a5c2-5549f109a416@1737622652934',
                                    'links' => [
                                        0 => [
                                            'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/36V319627Y4223722',
                                            'rel' => 'self',
                                            'method' => 'GET',
                                        ],
                                        1 => [
                                            'href' => 'https://api.sandbox.paypal.com/v2/payments/captures/36V319627Y4223722/refund',
                                            'rel' => 'refund',
                                            'method' => 'POST',
                                        ],
                                        2 => [
                                            'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/92163488YM718505A',
                                            'rel' => 'up',
                                            'method' => 'GET',
                                        ],
                                    ],
                                    'create_time' => '2025-01-23T08:57:45Z',
                                    'update_time' => '2025-01-23T08:57:45Z',
                                    'network_transaction_reference' => [
                                        'id' => '133455343573488',
                                        'network' => 'VISA',
                                    ],
                                    'processor_response' => [
                                        'avs_code' => 'Y',
                                        'cvv_code' => 'S',
                                        'response_code' => '0000',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
                'create_time' => '2025-01-23T08:57:33Z',
                'update_time' => '2025-01-23T08:57:45Z',
                'links' => [
                    0 => [
                        'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/92163488YM718505A',
                        'rel' => 'self',
                        'method' => 'GET',
                    ],
                ],
            ]],
        ];
    }

    public function approvedPayPalOrderProvider()
    {
        return [
            '55P13698XR4360722' => [
                'id' => '55P13698XR4360722',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'payment_source' => [
                        'paypal' => [
                                'email_address' => 'sb-26qa712980505@personal.example.com',
                                'account_id' => 'VRT593XYPLRRJ',
                                'account_status' => 'VERIFIED',
                                'name' => [
                                        'given_name' => 'John',
                                        'surname' => 'Doe',
                                    ],
                                'address' => [
                                        'address_line_1' => '2211 N First Street',
                                        'address_line_2' => '17.3.160',
                                        'admin_area_2' => 'San Jose',
                                        'admin_area_1' => 'CA',
                                        'postal_code' => '95131',
                                        'country_code' => 'US',
                                    ],
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                        'name' => [
                                                'full_name' => 'John Doe',
                                            ],
                                        'address' => [
                                                'address_line_1' => 'calle Vilamar� 76993- 17469',
                                                'admin_area_2' => 'Albacete',
                                                'admin_area_1' => 'Albacete',
                                                'postal_code' => '02001',
                                                'country_code' => 'ES',
                                            ],
                                    ],
                            ],
                    ],
                'payer' => [
                        'name' => [
                                'given_name' => 'John',
                                'surname' => 'Doe',
                            ],
                        'email_address' => 'sb-26qa712980505@personal.example.com',
                        'payer_id' => 'VRT593XYPLRRJ',
                        'address' => [
                                'address_line_1' => '2211 N First Street',
                                'address_line_2' => '17.3.160',
                                'admin_area_2' => 'San Jose',
                                'admin_area_1' => 'CA',
                                'postal_code' => '95131',
                                'country_code' => 'US',
                            ],
                    ],
                'create_time' => '2025-01-23T08:00:59Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/55P13698XR4360722',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                        1 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/55P13698XR4360722',
                                'rel' => 'update',
                                'method' => 'PATCH',
                            ],
                        2 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/55P13698XR4360722/capture',
                                'rel' => 'capture',
                                'method' => 'POST',
                            ],
                    ],
            ],
            '36T380043W338264T' => [
                'id' => '36T380043W338264T',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'payment_source' => [
                        'card' => [
                                'name' => 'John Doe',
                                'last_digits' => '4424',
                                'expiry' => '2025-01',
                                'brand' => 'VISA',
                                'available_networks' => [
                                        0 => 'VISA',
                                    ],
                                'type' => 'DEBIT',
                                'authentication_result' => [
                                        'liability_shift' => 'POSSIBLE',
                                        'three_d_secure' => [
                                                'enrollment_status' => 'Y',
                                                'authentication_status' => 'Y',
                                            ],
                                    ],
                                'bin_details' => [
                                        'bin' => '41470443',
                                        'bin_country_code' => 'FR',
                                    ],
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                                'shipping' => [
                                        'name' => [
                                                'full_name' => 'John Doe',
                                            ],
                                        'address' => [
                                                'address_line_1' => 'calle Vilamar� 76993- 17469',
                                                'admin_area_2' => 'Albacete',
                                                'admin_area_1' => 'Albacete',
                                                'postal_code' => '02001',
                                                'country_code' => 'ES',
                                            ],
                                    ],
                            ],
                    ],
                'payer' => [
                        'name' => [
                                'given_name' => 'John',
                                'surname' => 'Doe',
                            ],
                        'email_address' => 'sb-26qa712980505@personal.example.com',
                        'payer_id' => 'VRT593XYPLRRJ',
                        'address' => [
                                'address_line_1' => '2211 N First Street',
                                'address_line_2' => '17.3.160',
                                'admin_area_2' => 'San Jose',
                                'admin_area_1' => 'CA',
                                'postal_code' => '95131',
                                'country_code' => 'US',
                            ],
                    ],
                'create_time' => '2025-01-23T08:02:36Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/36T380043W338264T',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                        1 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/36T380043W338264T',
                                'rel' => 'update',
                                'method' => 'PATCH',
                            ],
                        2 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/36T380043W338264T/capture',
                                'rel' => 'capture',
                                'method' => 'POST',
                            ],
                    ],
            ],
            '2PT567357H024701J' => [
                'id' => '2PT567357H024701J',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'payment_source' => [
                        'bancontact' => [
                                'name' => 'John Doe',
                                'country_code' => 'BE',
                                'bic' => 'ABNANL2A',
                                'iban_last_chars' => '9344',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:06:20Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2PT567357H024701J',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                        1 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2PT567357H024701J',
                                'rel' => 'update',
                                'method' => 'PATCH',
                            ],
                        2 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2PT567357H024701J/capture',
                                'rel' => 'capture',
                                'method' => 'POST',
                            ],
                    ],
            ],
            '4XH97325YE9580105' => [
                'id' => '4XH97325YE9580105',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'payment_source' => [
                        'blik' => [
                                'name' => 'John Doe',
                                'country_code' => 'PL',
                                'email' => 'buyer@example.com',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'PLN',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'PLN',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:12:31Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4XH97325YE9580105',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                        1 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4XH97325YE9580105',
                                'rel' => 'update',
                                'method' => 'PATCH',
                            ],
                        2 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4XH97325YE9580105/capture',
                                'rel' => 'capture',
                                'method' => 'POST',
                            ],
                    ],
            ],
            '2RS70921X6989840S' => [
                'id' => '2RS70921X6989840S',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'payment_source' => [
                        'eps' => [
                                'name' => 'John Doe',
                                'country_code' => 'AT',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:13:44Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2RS70921X6989840S',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                        1 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2RS70921X6989840S',
                                'rel' => 'update',
                                'method' => 'PATCH',
                            ],
                        2 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2RS70921X6989840S/capture',
                                'rel' => 'capture',
                                'method' => 'POST',
                            ],
                    ],
            ],
            '29L95026GT2940426' => [
                'id' => '29L95026GT2940426',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'payment_source' => [
                        'ideal' => [
                                'name' => 'John Doe',
                                'country_code' => 'NL',
                                'bic' => 'INGBNL2A',
                                'iban_last_chars' => '9874',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:14:50Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/29L95026GT2940426',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                        1 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/29L95026GT2940426',
                                'rel' => 'update',
                                'method' => 'PATCH',
                            ],
                        2 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/29L95026GT2940426/capture',
                                'rel' => 'capture',
                                'method' => 'POST',
                            ],
                    ],
            ],
            '4T58422450971401N' => [
                'id' => '4T58422450971401N',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'payment_source' => [
                        'mybank' => [
                                'name' => 'John Doe',
                                'country_code' => 'IT',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:15:39Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4T58422450971401N',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                        1 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4T58422450971401N',
                                'rel' => 'update',
                                'method' => 'PATCH',
                            ],
                        2 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/4T58422450971401N/capture',
                                'rel' => 'capture',
                                'method' => 'POST',
                            ],
                    ],
            ],
            '2XB29141AM710800Y' => [
                'id' => '2XB29141AM710800Y',
                'intent' => 'CAPTURE',
                'status' => 'APPROVED',
                'payment_source' => [
                        'p24' => [
                                'name' => 'John Doe',
                                'email' => 'john.doe@example.com',
                                'country_code' => 'PL',
                            ],
                    ],
                'purchase_units' => [
                        0 => [
                                'reference_id' => 'default',
                                'amount' => [
                                        'currency_code' => 'EUR',
                                        'value' => '100.00',
                                        'breakdown' => [
                                                'item_total' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                            ],
                                    ],
                                'payee' => [
                                        'email_address' => 'sb-vshwm21975461@business.example.com',
                                        'merchant_id' => 'F36Z4TNY9ZQE6',
                                    ],
                                'soft_descriptor' => 'TEST STORE',
                                'items' => [
                                        0 => [
                                                'name' => 'T-Shirt',
                                                'unit_amount' => [
                                                        'currency_code' => 'EUR',
                                                        'value' => '100.00',
                                                    ],
                                                'quantity' => '1',
                                                'description' => 'Green XL',
                                            ],
                                    ],
                            ],
                    ],
                'create_time' => '2025-01-23T08:16:24Z',
                'links' => [
                        0 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2XB29141AM710800Y',
                                'rel' => 'self',
                                'method' => 'GET',
                            ],
                        1 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2XB29141AM710800Y',
                                'rel' => 'update',
                                'method' => 'PATCH',
                            ],
                        2 => [
                                'href' => 'https://api.sandbox.paypal.com/v2/checkout/orders/2XB29141AM710800Y/capture',
                                'rel' => 'capture',
                                'method' => 'POST',
                            ],
                    ],
            ],
        ];
    }

    public function captureErrorProvider()
    {
        return [
            [
                [
                    'id' => '9Y936175RH7229522',
                    'funding_source' => 'card',
                ],
                422,
                '{
                "name": "UNPROCESSABLE_ENTITY",
                "details": [
                    {
                        "field": "payment_source/card",
                        "location": "body",
                        "issue": "CARD_CLOSED",
                        "description": "The card is closed."
                    }
                ],
                "message": "The requested action could not be performed, semantically incorrect, or failed business validation.",
                "debug_id": "990b09d46b647",
                "links": [
                    {
                        "href": "https://developer.paypal.com/api/rest/reference/orders/v2/errors/#CARD_CLOSED",
                        "rel": "information_link",
                        "method": "GET"
                    }
                ]
            }', ],
        ];
    }
}
