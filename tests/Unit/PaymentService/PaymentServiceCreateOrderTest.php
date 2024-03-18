<?php

namespace Tests\Unit\PaymentService;

use Http\Client\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PayPalOrderHttpClient;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\PaymentService;
use PrestaShop\Module\PrestashopCheckout\DTO\Orders\CreatePayPalOrderRequestInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Exception\UnprocessableEntityException;
use PrestaShop\Module\PrestashopCheckout\Serializer\ObjectSerializer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class PaymentServiceCreateOrderTest extends TestCase
{
    /**
     * @dataProvider invalidRequestErrorsProvider
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException
     */
    public function testInvalidRequestErrorsCreateOrder($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(400, $errorName, $errorCode);
    }

    /**
     * @dataProvider notAuthorizedErrorsProvider
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException
     */
    public function testNotAuthorizedErrorsCreateOrder($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(401, $errorName, $errorCode);
    }

    /**
     * @dataProvider unprocessableEntityErrorsProvider
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException
     */
    public function testUnprocessableEntityErrorsCreateOrder($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(422, $errorName, $errorCode);
    }

    /**
     * @param int $statusCode
     * @param string $errorName
     * @param int $errorCode
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException
     */
    private function handleTestErrorsCreateOrder($statusCode, $errorName, $errorCode)
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
        $createRequestMock = $this->createMock(CreatePayPalOrderRequestInterface::class);

        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn(json_encode($error));

        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn($statusCode);
        $responseMock->method('getBody')->willReturn($streamMock);

        $clientMock = $this->createMock(PayPalOrderHttpClient::class);
        $clientMock->method('createOrder')->willThrowException(new HttpException('An error occurred', $requestMock, $responseMock));

        $this->expectExceptionCode($errorCode);
        $paymentService = new PaymentService($clientMock, new ObjectSerializer());
        $paymentService->createOrder($createRequestMock);
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
            ['INVALID_ARRAY_MAX_ITEMS', InvalidRequestException::INVALID_ARRAY_MAX_ITEMS],
            ['INVALID_ARRAY_MIN_ITEMS', InvalidRequestException::INVALID_ARRAY_MIN_ITEMS],
            ['INVALID_COUNTRY_CODE', InvalidRequestException::INVALID_COUNTRY_CODE],
            ['INVALID_PARAMETER_SYNTAX', InvalidRequestException::INVALID_PARAMETER_SYNTAX],
            ['INVALID_STRING_LENGTH', InvalidRequestException::INVALID_STRING_LENGTH],
            ['INVALID_PARAMETER_VALUE', InvalidRequestException::INVALID_PARAMETER_VALUE],
            ['MISSING_REQUIRED_PARAMETER', InvalidRequestException::MISSING_REQUIRED_PARAMETER],
            ['NOT_SUPPORTED', InvalidRequestException::NOT_SUPPORTED],
            ['PAYPAL_REQUEST_ID_REQUIRED', InvalidRequestException::PAYPAL_REQUEST_ID_REQUIRED],
            ['MALFORMED_REQUEST_JSON', InvalidRequestException::MALFORMED_REQUEST_JSON],
        ];
    }

    public function notAuthorizedErrorsProvider()
    {
        return [
            ['PERMISSION_DENIED', NotAuthorizedException::PERMISSION_DENIED],
            ['PERMISSION_DENIED_FOR_DONATION_ITEMS', NotAuthorizedException::PERMISSION_DENIED_FOR_DONATION_ITEMS],
            ['MALFORMED_REQUEST', NotAuthorizedException::MALFORMED_REQUEST],
        ];
    }

    public function unprocessableEntityErrorsProvider()
    {
        return [
            ['AMOUNT_MISMATCH', UnprocessableEntityException::AMOUNT_MISMATCH],
            ['BILLING_ADDRESS_INVALID', UnprocessableEntityException::BILLING_ADDRESS_INVALID],
            ['CANNOT_BE_NEGATIVE', UnprocessableEntityException::CANNOT_BE_NEGATIVE],
            ['CANNOT_BE_ZERO_OR_NEGATIVE', UnprocessableEntityException::CANNOT_BE_ZERO_OR_NEGATIVE],
            ['CARD_EXPIRED', UnprocessableEntityException::CARD_EXPIRED],
            ['CITY_REQUIRED', UnprocessableEntityException::CITY_REQUIRED],
            ['DECIMAL_PRECISION', UnprocessableEntityException::DECIMAL_PRECISION],
            ['DONATION_ITEMS_NOT_SUPPORTED', UnprocessableEntityException::DONATION_ITEMS_NOT_SUPPORTED],
            ['DUPLICATE_REFERENCE_ID', UnprocessableEntityException::DUPLICATE_REFERENCE_ID],
            ['INVALID_CURRENCY_CODE', UnprocessableEntityException::INVALID_CURRENCY_CODE],
            ['INVALID_PAYER_ID', UnprocessableEntityException::INVALID_PAYER_ID],
            ['ITEM_TOTAL_MISMATCH', UnprocessableEntityException::ITEM_TOTAL_MISMATCH],
            ['ITEM_TOTAL_REQUIRED', UnprocessableEntityException::ITEM_TOTAL_REQUIRED],
            ['MAX_VALUE_EXCEEDED', UnprocessableEntityException::MAX_VALUE_EXCEEDED],
            ['MISSING_PICKUP_ADDRESS', UnprocessableEntityException::MISSING_PICKUP_ADDRESS],
            ['MULTI_CURRENCY_ORDER', UnprocessableEntityException::MULTI_CURRENCY_ORDER],
            ['MULTIPLE_ITEM_CATEGORIES', UnprocessableEntityException::MULTIPLE_ITEM_CATEGORIES],
            ['MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED', UnprocessableEntityException::MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED],
            ['MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED', UnprocessableEntityException::MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED],
            ['PAYEE_ACCOUNT_INVALID', UnprocessableEntityException::PAYEE_ACCOUNT_INVALID],
            ['PAYEE_ACCOUNT_LOCKED_OR_CLOSED', UnprocessableEntityException::PAYEE_ACCOUNT_LOCKED_OR_CLOSED],
            ['PAYEE_ACCOUNT_RESTRICTED', UnprocessableEntityException::PAYEE_ACCOUNT_RESTRICTED],
            ['REFERENCE_ID_REQUIRED', UnprocessableEntityException::REFERENCE_ID_REQUIRED],
            ['PAYMENT_SOURCE_CANNOT_BE_USED', UnprocessableEntityException::PAYMENT_SOURCE_CANNOT_BE_USED],
            ['PAYMENT_SOURCE_DECLINED_BY_PROCESSOR', UnprocessableEntityException::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR],
            ['PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED', UnprocessableEntityException::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED],
            ['POSTAL_CODE_REQUIRED', UnprocessableEntityException::POSTAL_CODE_REQUIRED],
            ['SHIPPING_ADDRESS_INVALID', UnprocessableEntityException::SHIPPING_ADDRESS_INVALID],
            ['TAX_TOTAL_MISMATCH', UnprocessableEntityException::TAX_TOTAL_MISMATCH],
            ['TAX_TOTAL_REQUIRED', UnprocessableEntityException::TAX_TOTAL_REQUIRED],
            ['UNSUPPORTED_INTENT', UnprocessableEntityException::UNSUPPORTED_INTENT],
            ['UNSUPPORTED_PAYMENT_INSTRUCTION', UnprocessableEntityException::UNSUPPORTED_PAYMENT_INSTRUCTION],
            ['SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT', UnprocessableEntityException::SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT],
            ['UNSUPPORTED_SHIPPING_TYPE', UnprocessableEntityException::UNSUPPORTED_SHIPPING_TYPE],
            ['SHIPPING_OPTION_NOT_SELECTED', UnprocessableEntityException::SHIPPING_OPTION_NOT_SELECTED],
            ['SHIPPING_OPTIONS_NOT_SUPPORTED', UnprocessableEntityException::SHIPPING_OPTIONS_NOT_SUPPORTED],
            ['MULTIPLE_SHIPPING_OPTION_SELECTED', UnprocessableEntityException::MULTIPLE_SHIPPING_OPTION_SELECTED],
            ['PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH', UnprocessableEntityException::PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH],
            ['CARD_CLOSED', UnprocessableEntityException::CARD_CLOSED],
            ['ORDER_CANNOT_BE_SAVED', UnprocessableEntityException::ORDER_CANNOT_BE_SAVED],
            ['SAVE_ORDER_NOT_SUPPORTED', UnprocessableEntityException::SAVE_ORDER_NOT_SUPPORTED],
            ['PUI_DUPLICATE_ORDER', UnprocessableEntityException::PUI_DUPLICATE_ORDER],
        ];
    }
}
