<?php

namespace Tests\Unit\PaymentService;

use Http\Client\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PayPalOrderHttpClient;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\PaymentService;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\UnprocessableEntityException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PaymentServiceGetOrderTest extends TestCase
{
    /**
     * @dataProvider notAuthorizedErrorsProvider
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    public function testNotAuthorizedErrorsGetOrder($errorName, $errorCode)
    {
        $this->testErrorsGetOrder(401, $errorName, $errorCode);
    }

    /**
     * @param int $statusCode
     * @param string $errorName
     * @param int $errorCode
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    private function testErrorsGetOrder($statusCode, $errorName, $errorCode)
    {
        if ($errorName === 'invalid_token') {
            $error = $this->getInvalidTokenError();
        } else {
            $error = $this->getNotAuthorizedError($errorName);
        }

        $requestMock = $this->createMock(RequestInterface::class);

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode($error));

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn($statusCode);
        $responseMock->method('getBody')->willReturn($streamMock);

        $clientMock = $this->createMock(PayPalOrderHttpClient::class);
        $clientMock->method('fetchOrder')->willThrowException(new HttpException('An error occurred', $requestMock, $responseMock));

        $this->expectExceptionCode($errorCode);
        $paymentService = new PaymentService($clientMock);
        $paymentService->getOrder('LUX8l091NV');
    }

    private function getNotAuthorizedError($issueError)
    {
        return [
            "name" => $issueError,
            "message" => "Authentication failed due to invalid authentication credentials or a missing Authorization header.",
            "links" => [
                [
                    "href" => "https://developer.paypal.com/docs/api/overview/#error",
                    "rel" => "information_link"
                ]
            ]
        ];
    }

    private function getInvalidTokenError()
    {
        return [
            "error" => "invalid_token",
            "error_description" => "Current version only supports token for response_type"
        ];
    }

    public function notAuthorizedErrorsProvider()
    {
        return [
            ['PERMISSION_DENIED', NotAuthorizedException::PERMISSION_DENIED],
            ['invalid_token', NotAuthorizedException::INVALID_TOKEN]
        ];
    }
}
