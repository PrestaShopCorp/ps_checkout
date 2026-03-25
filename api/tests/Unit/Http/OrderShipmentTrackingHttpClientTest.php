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
use PsCheckout\Api\Http\OrderShipmentTrackingHttpClient;
use PsCheckout\Api\Http\PsrHttpClientAdapter;
use Psr\Http\Client\ClientInterface;

class OrderShipmentTrackingHttpClientTest extends TestCase
{
    public function testItRethrowsHttpExceptionWhenResponseBodyIsPlainText(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(502, ['Content-Type' => 'text/plain; charset=UTF-8'], 'error code: 502', '1.1', 'Bad Gateway'));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('POST', 'trackers'));
            $this->fail('An HttpException was expected.');
        } catch (HttpException $exception) {
            $this->assertSame('Bad Gateway', $exception->getMessage());
        }
    }

    public function testItRethrowsHttpExceptionWhenResponseBodyIsJsonScalar(): void
    {
        $psrClient = $this->createMock(ClientInterface::class);
        $psrClient->expects($this->once())
            ->method('sendRequest')
            ->willReturn(new Response(400, ['Content-Type' => 'application/json'], '"oops"', '1.1', 'Bad Request'));

        $httpClient = $this->createClient($psrClient);

        try {
            $httpClient->sendRequest(new Request('POST', 'trackers'));
            $this->fail('An HttpException was expected.');
        } catch (HttpException $exception) {
            $this->assertSame('Bad Request', $exception->getMessage());
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
            $httpClient->sendRequest(new Request('POST', 'trackers'));
            $this->fail('A PayPalException was expected.');
        } catch (PayPalException $exception) {
            $this->assertSame(PayPalException::INVALID_RESOURCE_ID, $exception->getCode());
            $this->assertInstanceOf(HttpException::class, $exception->getPrevious());
        }
    }

    private function createClient(ClientInterface $psrClient): OrderShipmentTrackingHttpClient
    {
        $configurationBuilder = $this->createMock(HttpClientConfigurationBuilderInterface::class);
        $configurationBuilder->method('build')->willReturn([]);

        $httpClient = new OrderShipmentTrackingHttpClient($configurationBuilder);
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
