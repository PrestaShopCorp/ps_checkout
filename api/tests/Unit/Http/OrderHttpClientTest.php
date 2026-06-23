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
use GuzzleHttp\Psr7\Response;
use Http\Client\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\Configuration\HttpClientConfigurationBuilderInterface;
use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Api\Http\OrderHttpClient;
use PsCheckout\Api\Http\PsrHttpClientAdapter;
use Psr\Http\Client\ClientInterface;

class OrderHttpClientTest extends TestCase
{
    public function testItReturnsResponseWhenRequestSucceeds(): void
    {
        $response = new Response(200, [], '{"status":"ok"}');
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($response);

        $httpClient = $this->createClient($psrClient);

        $this->assertSame($response, $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID')));
    }

    public function testItRethrowsHttpExceptionWhenResponseBodyIsPlainText(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(502, ['Content-Type' => 'text/plain; charset=UTF-8'], 'error code: 502', '1.1', 'Bad Gateway'));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID'));
            $this->fail('An HttpException was expected.');
        } catch (HttpException $exception) {
            $this->assertSame('Bad Gateway', $exception->getMessage());
        }
    }

    public function testItThrowsPayPalExceptionWhenResponseContainsPayPalIssueCode(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->createErrorResponse([
                'details' => [
                    [
                        'issue' => 'INVALID_RESOURCE_ID',
                    ],
                ],
            ]));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID'));
            $this->fail('A PayPalException was expected.');
        } catch (PayPalException $exception) {
            $this->assertSame(PayPalException::INVALID_RESOURCE_ID, $exception->getCode());
            $this->assertInstanceOf(HttpException::class, $exception->getPrevious());
        }
    }

    public function testItThrowsPayPalExceptionWhenResponseContainsErrorCode(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->createErrorResponse(['error' => 'INVALID_PARAMETER']));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID'));
            $this->fail('A PayPalException was expected.');
        } catch (PayPalException $exception) {
            $this->assertSame(PayPalException::INVALID_PARAMETER, $exception->getCode());
            $this->assertInstanceOf(HttpException::class, $exception->getPrevious());
        }
    }

    public function testItThrowsUnknownPayPalExceptionWhenResponseContainsMessageArray(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->createErrorResponse([
                'message' => [
                    'first error',
                    'second error',
                ],
            ]));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID'));
            $this->fail('A PayPalException was expected.');
        } catch (PayPalException $exception) {
            $this->assertSame(PayPalException::UNKNOWN, $exception->getCode());
            $this->assertSame("first error\nsecond error", $exception->getMessage());
            $this->assertInstanceOf(HttpException::class, $exception->getPrevious());
        }
    }

    public function testItThrowsPayPalExceptionWhenResponseContainsMessageCode(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->createErrorResponse(['message' => 'INVALID_PAYER_ID']));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID'));
            $this->fail('A PayPalException was expected.');
        } catch (PayPalException $exception) {
            $this->assertSame(PayPalException::INVALID_PAYER_ID, $exception->getCode());
            $this->assertInstanceOf(HttpException::class, $exception->getPrevious());
        }
    }

    public function testItThrowsPayPalExceptionWhenResponseContainsNameCode(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn($this->createErrorResponse(['name' => 'INVALID_RESOURCE_ID']));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID'));
            $this->fail('A PayPalException was expected.');
        } catch (PayPalException $exception) {
            $this->assertSame(PayPalException::INVALID_RESOURCE_ID, $exception->getCode());
            $this->assertInstanceOf(HttpException::class, $exception->getPrevious());
        }
    }

    public function testItExtractsMessageFromNestedErrorObjectWhenErrorFieldIsArray(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(
                401,
                ['Content-Type' => 'application/json'],
                (string) json_encode([
                    'error' => [
                        'code' => 401,
                        'status' => 'Unauthorized',
                        'message' => 'The request could not be authorized',
                    ],
                ]),
                '1.1',
                'Unauthorized'
            ));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID'));
            $this->fail('A PayPalException was expected.');
        } catch (PayPalException $exception) {
            $this->assertSame(PayPalException::UNKNOWN, $exception->getCode());
            $this->assertSame('The request could not be authorized', $exception->getMessage());
            $this->assertInstanceOf(HttpException::class, $exception->getPrevious());
        }
    }

    public function testItRethrowsHttpExceptionWhenErrorFieldIsAnArrayWithNoMessage(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(
                401,
                ['Content-Type' => 'application/json'],
                (string) json_encode(['error' => ['code' => 401]]),
                '1.1',
                'Unauthorized'
            ));

        $httpClient = $this->createClient($psrClient);

        $this->expectException(HttpException::class);
        $httpClient->sendRequest(new Request('GET', 'orders/ORDER-ID'));
    }

    private function createClient(ClientInterface $psrClient): OrderHttpClient
    {
        $configurationBuilder = $this->createMock(HttpClientConfigurationBuilderInterface::class);
        $configurationBuilder->method('build')->willReturn([]);

        $httpClient = new OrderHttpClient($configurationBuilder);
        $clientProperty = new \ReflectionProperty(PsrHttpClientAdapter::class, 'client');
        $clientProperty->setAccessible(true);
        $clientProperty->setValue($httpClient, $psrClient);

        return $httpClient;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return Response
     */
    private function createErrorResponse(array $payload): Response
    {
        $json = json_encode($payload);

        return new Response(
            422,
            ['Content-Type' => 'application/json'],
            is_string($json) ? $json : '',
            '1.1',
            'Unprocessable Entity'
        );
    }
}
