<?php

namespace PayPal\Order\CommandHandler;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Http\Client\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\Payload;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Http\HttpClientInterface;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CreatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\CommandHandler\CreatePayPalOrderCommandHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event\PayPalOrderCreatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Entity\PaymentToken;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenDeletedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Event\PaymentTokenUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\Repository\PaymentTokenRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PayPalCustomerRepository;
use PrestaShop\Module\PrestashopCheckout\ShopContext;

class CreatePayPalOrderCommandHandlerTest extends TestCase
{
    public function testPaymentTokenUpdate()
    {
        $maaslandHttpClient = $this->createMock(MaaslandHttpClient::class);

        $order = [
            'id' => 'ORDERID',
            'status' => 'COMPLETED',
        ];

        $httpResponse = new Response(200, [], json_encode($order));
        $maaslandHttpClient->method('createOrder')->willReturn($httpResponse);

        $shopContext = $this->createMock(ShopContext::class);
        $prestaShopContext = $this->createMock(PrestaShopContext::class);

        $prestaShopContext->method('getCustomerId')->willReturn(1);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $payPalCustomerRepository = $this->createMock(PayPalCustomerRepository::class);

        $payPalCustomerRepository
            ->method('findPayPalCustomerIdByCustomerId')
            ->willReturn(new PayPalCustomerId('PPCUSTOMERID'));

        $paymentTokenRepository = $this->createMock(PaymentTokenRepository::class);

        $paymentTokenRepository
            ->method('findById')
            ->willReturn(
                new PaymentToken('XXXXXXXXXXX', 'PPCUSTOMERID', 'card', [], 'MERCHANTID', 'status')
            );

        $orderPayloadBuilder = $this->createMock(OrderPayloadBuilder::class);
        $payload = $this->createMock(Payload::class);
        $payload->method('getArray')->willReturn([]);
        $orderPayloadBuilder->method('presentPayload')->willReturn($payload);

        $commandHandler = $this->getMockBuilder(CreatePayPalOrderCommandHandler::class)
            ->setConstructorArgs([
                $maaslandHttpClient,
                $shopContext,
                $prestaShopContext,
                $eventDispatcher,
                $payPalCustomerRepository,
                $paymentTokenRepository,
            ])
            ->setMethods(['getPayloadBuilder'])
            ->getMock();

        $commandHandler->method('getPayloadBuilder')
            ->willReturn($orderPayloadBuilder);

        $command = new CreatePayPalOrderCommand(1, 'card', true, true, 'XXXXXXXXXXX');

        $expectedEvents = [
            new PayPalOrderCreatedEvent(
                $order['id'],
                $order,
                $command->getCartId()->getValue(),
                $command->getFundingSource(),
                $command->isHostedFields(),
                $command->isExpressCheckout(),
                '',
                $command->getPaymentTokenId()
            ),
            new PaymentTokenUpdatedEvent([]), ];

        $eventDispatcher->expects($this->exactly(count($expectedEvents)))
            ->method('dispatch')
            ->withConsecutive(...$expectedEvents);

        $commandHandler->handle($command);
    }

    /**
     * @dataProvider createErrorProvider
     */
    public function testPaymentTokenDelete(array $request, $errorCode, $response)
    {
        $httpClient = $this->createMock(HttpClientInterface::class);
        $maaslandHttpClient = new MaaslandHttpClient($httpClient);

        $httpRequest = new Request('POST', '/payments/order/create', [], json_encode($request));
        $httpResponse = new Response($errorCode, [], $response);
        $exception = new HttpException('', $httpRequest, $httpResponse);
        $httpClient->method('sendRequest')->willThrowException($exception);

        $shopContext = $this->createMock(ShopContext::class);
        $prestaShopContext = $this->createMock(PrestaShopContext::class);

        $prestaShopContext->method('getCustomerId')->willReturn(1);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $payPalCustomerRepository = $this->createMock(PayPalCustomerRepository::class);

        $payPalCustomerRepository
            ->method('findPayPalCustomerIdByCustomerId')
            ->willReturn(new PayPalCustomerId('PPCUSTOMERID'));

        $paymentTokenRepository = $this->createMock(PaymentTokenRepository::class);

        $paymentTokenRepository
            ->method('findById')
            ->willReturn(
                new PaymentToken('XXXXXXXXXXX', 'PPCUSTOMERID', 'card', [], 'MERCHANTID', 'status')
            );

        $orderPayloadBuilder = $this->createMock(OrderPayloadBuilder::class);
        $payload = $this->createMock(Payload::class);
        $payload->method('getArray')->willReturn([]);
        $orderPayloadBuilder->method('presentPayload')->willReturn($payload);

        $commandHandler = $this->getMockBuilder(CreatePayPalOrderCommandHandler::class)
            ->setConstructorArgs([
                $maaslandHttpClient,
                $shopContext,
                $prestaShopContext,
                $eventDispatcher,
                $payPalCustomerRepository,
                $paymentTokenRepository
            ])
            ->setMethods(['getPayloadBuilder'])
            ->getMock();

        $commandHandler->method('getPayloadBuilder')
            ->willReturn($orderPayloadBuilder);

        $command = new CreatePayPalOrderCommand(1, 'card', true, true, 'XXXXXXXXXXX');

        $expectedEvents = [];

        if (str_contains($response, 'CARD_CLOSED')) {
            $expectedEvents = [
                new PaymentTokenDeletedEvent([])
            ];
        }

        $eventDispatcher->expects($this->exactly(count($expectedEvents)))
            ->method('dispatch')
            ->withConsecutive(...$expectedEvents);

        $this->expectException(PayPalException::class);

        $commandHandler->handle($command);
    }

    public function createErrorProvider()
    {
        return [
            [
                [
                    'id' => "9Y936175RH7229522",
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
                }'
            ],
            [
                [
                    'id' => "9Y936175RH7229522",
                    'funding_source' => 'card',
                ],
                422,
                '{
                    "name": "UNPROCESSABLE_ENTITY",
                    "details": [
                        {
                            "field": "payment_source/card",
                            "location": "body",
                            "issue": "CARD_BRAND_NOT_SUPPORTED",
                            "description": "Card brand not supported."
                        }
                    ],
                    "message": "The requested action could not be performed, semantically incorrect, or failed business validation.",
                    "debug_id": "990b09d46b647",
                    "links": [
                        {
                            "href": "https://developer.paypal.com/api/rest/reference/orders/v2/errors/#CARD_BRAND_NOT_SUPPORTED",
                            "rel": "information_link",
                            "method": "GET"
                        }
                    ]
                }'
            ],
        ];
    }
}
