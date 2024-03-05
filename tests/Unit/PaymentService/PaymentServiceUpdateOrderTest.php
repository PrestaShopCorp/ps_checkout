<?php

namespace Tests\Unit\PaymentService;

use Http\Client\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PayPalOrderHttpClient;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\PaymentService;
use PrestaShop\Module\PrestashopCheckout\DTO\Orders\UpdatePayPalOrderRequestInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\UnprocessableEntityException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PaymentServiceUpdateOrderTest extends TestCase
{
    /**
     * @dataProvider invalidRequestErrorsProvider
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    public function testInvalidRequestErrorsUpdateOrder($errorName, $errorCode)
    {
        $this->testErrorsUpdateOrder(400, $errorName, $errorCode);
    }

    /**
     * @dataProvider notAuthorizedErrorsProvider
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    public function testNotAuthorizedErrorsUpdateOrder($errorName, $errorCode)
    {
        $this->testErrorsUpdateOrder(401, $errorName, $errorCode);
    }

    /**
     * @dataProvider unprocessableEntityErrorsProvider
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    public function testUnprocessableEntityErrorsUpdateOrder($errorName, $errorCode)
    {
        $this->testErrorsUpdateOrder(422, $errorName, $errorCode);
    }

    /**
     * @param int $statusCode
     * @param string $errorName
     * @param int $errorCode
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    private function testErrorsUpdateOrder($statusCode, $errorName, $errorCode)
    {
        switch ($statusCode) {
            case 400:
                $error = $this->getInvalidRequestError($errorName);
                break;
            case 401:
                $error = $this->getNotAuthorizedError($errorName);
                break;
            case 422:
            default:
                $error = $this->getUnprocessableEntityError($errorName);
                break;
        }

        $requestMock = $this->createMock(RequestInterface::class);
        $updateRequestMock = $this->createMock(UpdatePayPalOrderRequestInterface::class);

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode($error));

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn($statusCode);
        $responseMock->method('getBody')->willReturn($streamMock);

        $clientMock = $this->createMock(PayPalOrderHttpClient::class);
        $clientMock->method('updateOrder')->willThrowException(new HttpException('An error occurred', $requestMock, $responseMock));

        $this->expectExceptionCode($errorCode);
        $paymentService = new PaymentService($clientMock);
        $paymentService->updateOrder($updateRequestMock);
    }

    private function getInvalidRequestError($issueError)
    {
        return [
            'name' => 'INVALID_REQUEST',
            'message' => 'Request is not well-formed, syntactically incorrect, or violates schema.',
            'debug_id' => 'b6b9a374802ea',
            'details' => [
                [
                    'field' => '',
                    'value' => '',
                    'location' => 'body',
                    'issue' => $issueError,
                    'description' => '',
                ],
            ],
            'links' => [
                [
                    'href' => 'https://developer.paypal.com/docs/api/orders/v2/#error-INVALID_PARAMETER_VALUE',
                    'rel' => 'information_link',
                    'encType' => 'application/json',
                ],
            ],
        ];
    }

    private function getNotAuthorizedError($issueError)
    {
        return [
            'name' => $issueError,
            'message' => 'Authentication failed due to invalid authentication credentials or a missing Authorization header.',
            'links' => [
                [
                    'href' => 'https://developer.paypal.com/docs/api/overview/#error',
                    'rel' => 'information_link',
                ],
            ],
        ];
    }

    private function getUnprocessableEntityError($issueError)
    {
        return [
            'name' => 'UNPROCESSABLE_ENTITY',
            'details' => [
                [
                    'field' => '',
                    'value' => '',
                    'issue' => $issueError,
                    'description' => '',
                ],
            ],
            'message' => 'The requested action could not be performed, semantically incorrect, or failed business validation.',
            'debug_id' => 'c9a75b43fc807',
            'links' => [
                [
                    'href' => 'https://developer.paypal.com/docs/api/orders/v2/#error-MAX_VALUE_EXCEEDED',
                    'rel' => 'information_link',
                    'method' => 'GET',
                ],
            ],
        ];
    }

    public function invalidRequestErrorsProvider()
    {
        return [
            ['FIELD_NOT_PATCHABLE', InvalidRequestException::FIELD_NOT_PATCHABLE],
            ['INVALID_ARRAY_MAX_ITEMS', InvalidRequestException::INVALID_ARRAY_MAX_ITEMS],
            ['INVALID_PARAMETER_SYNTAX', InvalidRequestException::INVALID_PARAMETER_SYNTAX],
            ['INVALID_STRING_LENGTH', InvalidRequestException::INVALID_STRING_LENGTH],
            ['INVALID_PARAMETER_VALUE', InvalidRequestException::INVALID_PARAMETER_VALUE],
            ['MISSING_REQUIRED_PARAMETER', InvalidRequestException::MISSING_REQUIRED_PARAMETER],
            ['AMOUNT_NOT_PATCHABLE', InvalidRequestException::AMOUNT_NOT_PATCHABLE],
            ['INVALID_PATCH_OPERATION', InvalidRequestException::INVALID_PATCH_OPERATION],
            ['ERROR_CURRENTLY_UNKNOWN', InvalidRequestException::UNKNOWN],
        ];
    }

    public function notAuthorizedErrorsProvider()
    {
        return [
            ['PERMISSION_DENIED', NotAuthorizedException::PERMISSION_DENIED],
            ['PAYEE_ACCOUNT_NOT_SUPPORTED', NotAuthorizedException::PAYEE_ACCOUNT_NOT_SUPPORTED],
            ['PAYEE_ACCOUNT_NOT_VERIFIED', NotAuthorizedException::PAYEE_ACCOUNT_NOT_VERIFIED],
            ['PAYEE_NOT_CONSENTED', NotAuthorizedException::PAYEE_NOT_CONSENTED],
            ['ERROR_CURRENTLY_UNKNOWN', NotAuthorizedException::UNKNOWN],
        ];
    }

    public function unprocessableEntityErrorsProvider()
    {
        return [
            ['INVALID_JSON_POINTER_FORMAT', UnprocessableEntityException::INVALID_JSON_POINTER_FORMAT],
            ['INVALID_PARAMETER', UnprocessableEntityException::INVALID_PARAMETER],
            ['NOT_PATCHABLE', UnprocessableEntityException::NOT_PATCHABLE],
            ['UNSUPPORTED_PATCH_PARAMETER_VALUE', UnprocessableEntityException::UNSUPPORTED_PATCH_PARAMETER_VALUE],
            ['PATCH_VALUE_REQUIRED', UnprocessableEntityException::PATCH_VALUE_REQUIRED],
            ['PATCH_PATH_REQUIRED', UnprocessableEntityException::PATCH_PATH_REQUIRED],
            ['REFERENCE_ID_NOT_FOUND', UnprocessableEntityException::REFERENCE_ID_NOT_FOUND],
            ['MULTI_CURRENCY_ORDER', UnprocessableEntityException::MULTI_CURRENCY_ORDER],
            ['SHIPPING_OPTION_NOT_SELECTED', UnprocessableEntityException::SHIPPING_OPTION_NOT_SELECTED],
            ['SHIPPING_OPTIONS_NOT_SUPPORTED', UnprocessableEntityException::SHIPPING_OPTIONS_NOT_SUPPORTED],
            ['MULTIPLE_SHIPPING_OPTION_SELECTED', UnprocessableEntityException::MULTIPLE_SHIPPING_OPTION_SELECTED],
            ['ORDER_ALREADY_COMPLETED', UnprocessableEntityException::ORDER_ALREADY_COMPLETED],
            ['PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH', UnprocessableEntityException::PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH],
            ['ERROR_CURRENTLY_UNKNOWN', UnprocessableEntityException::UNKNOWN],
        ];
    }
}
