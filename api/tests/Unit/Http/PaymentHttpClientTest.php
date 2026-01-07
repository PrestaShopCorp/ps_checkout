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

namespace PsCheckout\Tests\Api\Unit\Http;

use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Dto\PayPal\Money;
use PsCheckout\Api\Dto\PayPal\Payment\ReauthorizeAuthorizationRequestDto;
use PsCheckout\Api\Http\Configuration\PaymentHttpClientConfigurationBuilder;
use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Api\Http\PaymentHttpClient;
use PsCheckout\Api\Http\PaymentHttpClientInterface;
use Psr\Http\Message\ResponseInterface;
use PsCheckout\Api\Http\Serializer\PaymentSerializerFactory;
use Psr\Http\Client\ClientInterface;

/**
 * @coversDefaultClass PaymentHttpClientInterface
 */
class PaymentHttpClientTest extends TestCase
{
    /**
     * @var PaymentHttpClientInterface
     */
    private $paymentHttpClient;

    /**
     * @var ClientInterface|MockObject
     */
    private $httpClient;

    /**
     * @var PaymentHttpClientConfigurationBuilder|MockObject
     */
    private $paymentHttpClientConfigurationBuilder;

    public function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->paymentHttpClientConfigurationBuilder = $this->createMock(PaymentHttpClientConfigurationBuilder::class);

        $this->paymentHttpClient = new PaymentHttpClient(
            $this->paymentHttpClientConfigurationBuilder,
            PaymentSerializerFactory::create(),
            $this->httpClient
        );

    }

    public function testGetAuthorizationSuccessful(): void
    {
        $authorizationId = '0VF52814937998046';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn(json_encode([
            'id' => $authorizationId,
            'status' => 'CREATED',
            'amount' => [
                'value' => '10.99',
                'currency_code' => 'USD'
            ],
            'invoice_id' => 'INVOICE-123',
            'seller_protection' => [
                'status' => 'ELIGIBLE',
                'dispute_categories' => [
                    'ITEM_NOT_RECEIVED',
                    'UNAUTHORIZED_TRANSACTION'
                ]
            ],
            'payee' => [
                'email_address' => 'merchant@example.com',
                'merchant_id' => '7KNGBPH2U58GQ'
            ],
            'expiration_time' => '2017-10-10T23:23:45Z',
            'create_time' => '2017-09-11T23:23:45Z',
            'update_time' => '2017-09-11T23:23:45Z',
            'links' => [
                [
                    'rel' => 'self',
                    'method' => 'GET',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/0VF52814937998046'
                ],
                [
                    'rel' => 'capture',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/0VF52814937998046/capture'
                ],
                [
                    'rel' => 'void',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/0VF52814937998046/void'
                ],
                [
                    'rel' => 'reauthorize',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/0VF52814937998046/reauthorize'
                ]
            ]
        ]));

        $this->httpClient->expects($this->once())->method('sendRequest')->with(new Request('GET', "authorizations/$authorizationId"))->willReturn($response);

        $authorization = $this->paymentHttpClient->getAuthorization($authorizationId);

        $this->assertEquals($authorizationId, $authorization->getId());
    }

    public function testGetAuthorizationForbidden(): void
    {
        $authorizationId = 'NOT_AUTHORIZED';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(403);
        $response->method('getBody')->willReturn(json_encode([
            'name' => 'NOT_AUTHORIZED',
            'debugId' => 'b1d1f06c7446c',
            'message' => 'Authorization failed due to insufficient permissions.',
            'details' => [
                [
                    'issue' => 'PERMISSION_DENIED',
                    'description' => 'Permission denied.'
                ]
            ]
        ]));

        $this->httpClient->expects($this->once())->method('sendRequest')->with(new Request('GET', "authorizations/$authorizationId"))->willReturn($response);

        $this->expectException(PayPalException::class);

        $this->paymentHttpClient->getAuthorization($authorizationId);
    }

    public function testReauthorizeAuthorizationSuccessful(): void
    {
        $authorizationId = '0VF52814937998046';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(201);
        $response->method('getBody')->willReturn(json_encode([
            'id' => $authorizationId,
            'status' => 'CREATED',
            'links' => [
                [
                    'rel' => 'self',
                    'method' => 'GET',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L'
                ],
                [
                    'rel' => 'capture',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/capture'
                ],
                [
                    'rel' => 'void',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/void'
                ],
                [
                    'rel' => 'reauthorize',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/reauthorize'
                ]
            ]
        ]));

        $this->httpClient->expects($this->once())->method('sendRequest')->willReturn($response);

        $authorization = $this->paymentHttpClient->reauthorizeAuthorization($authorizationId, new ReauthorizeAuthorizationRequestDto(
            new Money('USD', '10.99')
        ));

        $this->assertEquals($authorizationId, $authorization->getId());
    }

    public function testEmptyReauthorizeAuthorizationSuccessful(): void
    {
        $authorizationId = '0VF52814937998046';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(201);
        $response->method('getBody')->willReturn(json_encode([
            'id' => $authorizationId,
            'status' => 'CREATED',
            'links' => [
                [
                    'rel' => 'self',
                    'method' => 'GET',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L'
                ],
                [
                    'rel' => 'capture',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/capture'
                ],
                [
                    'rel' => 'void',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/void'
                ],
                [
                    'rel' => 'reauthorize',
                    'method' => 'POST',
                    'href' => 'https://api-m.paypal.com/v2/payments/authorizations/8AA831015G517922L/reauthorize'
                ]
            ]
        ]));

        $this->httpClient->expects($this->once())->method('sendRequest')->willReturn($response);

        $authorization = $this->paymentHttpClient->reauthorizeAuthorization($authorizationId);

        $this->assertEquals($authorizationId, $authorization->getId());
    }

    public function testEmptyReauthorizeAuthorizationForbidden(): void
    {
        $authorizationId = 'NOT_AUTHORIZED';

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(403);
        $response->method('getBody')->willReturn(json_encode([
            'name' => 'NOT_AUTHORIZED',
            'debugId' => 'b1d1f06c7446c',
            'message' => 'Authorization failed due to insufficient permissions.',
            'details' => [
                [
                    'issue' => 'PERMISSION_DENIED',
                    'description' => 'Permission denied.'
                ]
            ]
        ]));

        $this->httpClient->expects($this->once())->method('sendRequest')->willReturn($response);

        $this->expectException(PayPalException::class);

        $this->paymentHttpClient->reauthorizeAuthorization($authorizationId);
    }
}
