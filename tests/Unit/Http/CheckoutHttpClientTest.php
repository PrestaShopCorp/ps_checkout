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

namespace Tests\Unit\Http;

use Http\Client\Exception\HttpException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Http\CheckoutHttpClient;
use PrestaShop\Module\PrestashopCheckout\Http\HttpClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class CheckoutHttpClientTest extends TestCase
{
    /**
     * @dataProvider invalidRequestErrorsProvider
     *
     * @throws PayPalException
     */
    public function testInvalidRequestErrorsCreateOrder($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(400, $errorName, $errorCode);
    }

    /**
     * @dataProvider notAuthorizedErrorsProvider
     *
     * @throws PayPalException
     */
    public function testNotAuthorizedErrorsCreateOrder($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(401, $errorName, $errorCode);
    }

    /**
     * @dataProvider unprocessableEntityErrorsProvider
     *
     * @throws PayPalException
     */
    public function testUnprocessableEntityErrorsCreateOrder($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(422, $errorName, $errorCode);
    }

    /**
     * @dataProvider notFoundErrorsProvider
     *
     * @throws PayPalException
     */
    public function testNotFoundErrorsCreateOrder($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(404, $errorName, $errorCode);
    }

    /**
     * @dataProvider invalidRequestErrorsProvider
     *
     * @throws PayPalException
     */
    public function testInvalidRequestErrorsCreateOrderLegacy($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(400, $errorName, $errorCode, true);
    }

    /**
     * @dataProvider notAuthorizedErrorsProvider
     *
     * @throws PayPalException
     */
    public function testNotAuthorizedErrorsCreateOrderLegacy($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(401, $errorName, $errorCode, true);
    }

    /**
     * @dataProvider unprocessableEntityErrorsProvider
     *
     * @throws PayPalException
     */
    public function testUnprocessableEntityErrorsCreateOrderLegacy($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(422, $errorName, $errorCode, true);
    }

    /**
     * @dataProvider notFoundErrorsProvider
     *
     * @throws PayPalException
     */
    public function testNotFoundErrorsCreateOrderLegacy($errorName, $errorCode)
    {
        $this->handleTestErrorsCreateOrder(404, $errorName, $errorCode, true);
    }

    /**
     * @param int $statusCode
     * @param string $errorName
     * @param int $errorCode
     * @param bool $legacy
     *
     * @throws PayPalException
     */
    private function handleTestErrorsCreateOrder($statusCode, $errorName, $errorCode, $legacy = false)
    {
        $error = $this->getErrorResponse($statusCode, $errorName, $legacy);
        $requestMock = $this->createMock(RequestInterface::class);
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('__toString')->willReturn(json_encode($error));
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getStatusCode')->willReturn($statusCode);
        $responseMock->method('getBody')->willReturn($streamMock);
        $httpClient = $this->createMock(HttpClientInterface::class);
        $httpClient->method('sendRequest')->willThrowException(new HttpException('An error occurred', $requestMock, $responseMock));
        $this->expectExceptionCode($errorCode);
        $this->expectException(PayPalException::class);
        $checkoutHttpClient = new CheckoutHttpClient($httpClient);
        $checkoutHttpClient->createOrder([]);
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

    /**
     * @param int $statusCode
     * @param string $errorName
     * @param bool $legacy
     *
     * @return array
     */
    private function getErrorResponse($statusCode, $errorName, $legacy)
    {
        if ($legacy) {
            switch ($statusCode) {
                case 400:
                    return $this->getInvalidRequestErrorLegacy($errorName);
                case 401:
                    return $this->getNotAuthorizedErrorLegacy($errorName);
                case 404:
                    return $this->getNotFoundErrorLegacy($errorName);
                case 422:
                    return $this->getUnprocessableEntityErrorLegacy($errorName);
            }
        }

        switch ($statusCode) {
            case 400:
                return $this->getInvalidRequestError($errorName);
            case 401:
                return $this->getNotAuthorizedError($errorName);
            case 404:
                return $this->getNotFoundError($errorName);
            case 422:
                return $this->getUnprocessableEntityError($errorName);
        }

        return [];
    }

    private function getInvalidRequestErrorLegacy($issueError)
    {
        return [
            'statusCode' => 400,
            'error' => 'Bad Request',
            'message' => $issueError,
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

    private function getNotAuthorizedErrorLegacy($issueError)
    {
        return [
            'statusCode' => 401,
            'error' => 'Unprocessable Entity',
            'message' => $issueError,
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

    private function getUnprocessableEntityErrorLegacy($issueError)
    {
        return [
            'statusCode' => 422,
            'error' => 'Unprocessable Entity',
            'message' => $issueError,
        ];
    }

    /**
     * @param string $errorName
     *
     * @return array
     */
    private function getNotFoundError($errorName)
    {
        return [
            'name' => $errorName,
            'message' => 'The specified resource does not exist.',
            'debug_id' => 'b6b9a374802ea',
            'links' => [
                [
                    'href' => 'https://developer.paypal.com/docs/api/orders/v2/#error-' . $errorName,
                    'rel' => 'information_link',
                    'encType' => 'application/json',
                ],
            ],
        ];
    }

    private function getNotFoundErrorLegacy($issueError)
    {
        return [
            'statusCode' => 404,
            'error' => 'Not found',
            'message' => $issueError,
        ];
    }

    public function notFoundErrorsProvider()
    {
        return [
            ['INVALID_RESOURCE_ID', PayPalException::INVALID_RESOURCE_ID],
        ];
    }

    public function invalidRequestErrorsProvider()
    {
        return [
            ['INVALID_ARRAY_MAX_ITEMS', PayPalException::INVALID_ARRAY_MAX_ITEMS],
            ['INVALID_ARRAY_MIN_ITEMS', PayPalException::INVALID_ARRAY_MIN_ITEMS],
            ['INVALID_COUNTRY_CODE', PayPalException::INVALID_COUNTRY_CODE],
            ['INVALID_PARAMETER_SYNTAX', PayPalException::INVALID_PARAMETER_SYNTAX],
            ['INVALID_STRING_LENGTH', PayPalException::INVALID_STRING_LENGTH],
            ['INVALID_PARAMETER_VALUE', PayPalException::INVALID_PARAMETER_VALUE],
            ['MISSING_REQUIRED_PARAMETER', PayPalException::MISSING_REQUIRED_PARAMETER],
            ['NOT_SUPPORTED', PayPalException::NOT_SUPPORTED],
            ['PAYPAL_REQUEST_ID_REQUIRED', PayPalException::PAYPAL_REQUEST_ID_REQUIRED],
            ['MALFORMED_REQUEST_JSON', PayPalException::MALFORMED_REQUEST_JSON],
            ['FIELD_NOT_PATCHABLE', PayPalException::FIELD_NOT_PATCHABLE],
            ['AMOUNT_NOT_PATCHABLE', PayPalException::AMOUNT_NOT_PATCHABLE],
            ['INVALID_PATCH_OPERATION', PayPalException::INVALID_PATCH_OPERATION],
        ];
    }

    public function notAuthorizedErrorsProvider()
    {
        return [
            ['PERMISSION_DENIED', PayPalException::PERMISSION_DENIED],
            ['PERMISSION_DENIED_FOR_DONATION_ITEMS', PayPalException::PERMISSION_DENIED_FOR_DONATION_ITEMS],
            ['MALFORMED_REQUEST', PayPalException::MALFORMED_REQUEST],
            ['PAYEE_ACCOUNT_NOT_SUPPORTED', PayPalException::PAYEE_ACCOUNT_NOT_SUPPORTED],
            ['PAYEE_ACCOUNT_NOT_VERIFIED', PayPalException::PAYEE_ACCOUNT_NOT_VERIFIED],
            ['PAYEE_NOT_CONSENTED', PayPalException::PAYEE_NOT_CONSENTED],
            ['CONSENT_NEEDED', PayPalException::CONSENT_NEEDED],
            ['INVALID_ACCOUNT_STATUS', PayPalException::INVALID_ACCOUNT_STATUS],
        ];
    }

    public function unprocessableEntityErrorsProvider()
    {
        return [
            ['AMOUNT_MISMATCH', PayPalException::AMOUNT_MISMATCH],
            ['BILLING_ADDRESS_INVALID', PayPalException::BILLING_ADDRESS_INVALID],
            ['CANNOT_BE_NEGATIVE', PayPalException::CANNOT_BE_NEGATIVE],
            ['CANNOT_BE_ZERO_OR_NEGATIVE', PayPalException::CANNOT_BE_ZERO_OR_NEGATIVE],
            ['CARD_EXPIRED', PayPalException::CARD_EXPIRED],
            ['CITY_REQUIRED', PayPalException::CITY_REQUIRED],
            ['DECIMAL_PRECISION', PayPalException::DECIMAL_PRECISION],
            ['DONATION_ITEMS_NOT_SUPPORTED', PayPalException::DONATION_ITEMS_NOT_SUPPORTED],
            ['DUPLICATE_REFERENCE_ID', PayPalException::DUPLICATE_REFERENCE_ID],
            ['INVALID_CURRENCY_CODE', PayPalException::INVALID_CURRENCY_CODE],
            ['INVALID_PAYER_ID', PayPalException::INVALID_PAYER_ID],
            ['ITEM_TOTAL_MISMATCH', PayPalException::ITEM_TOTAL_MISMATCH],
            ['ITEM_TOTAL_REQUIRED', PayPalException::ITEM_TOTAL_REQUIRED],
            ['MAX_VALUE_EXCEEDED', PayPalException::MAX_VALUE_EXCEEDED],
            ['MISSING_PICKUP_ADDRESS', PayPalException::MISSING_PICKUP_ADDRESS],
            ['MULTIPLE_ITEM_CATEGORIES', PayPalException::MULTIPLE_ITEM_CATEGORIES],
            ['MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED', PayPalException::MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED],
            ['MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED', PayPalException::MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED],
            ['PAYEE_ACCOUNT_INVALID', PayPalException::PAYEE_ACCOUNT_INVALID],
            ['PAYEE_ACCOUNT_LOCKED_OR_CLOSED', PayPalException::PAYEE_ACCOUNT_LOCKED_OR_CLOSED],
            ['PAYEE_ACCOUNT_RESTRICTED', PayPalException::PAYEE_ACCOUNT_RESTRICTED],
            ['REFERENCE_ID_REQUIRED', PayPalException::REFERENCE_ID_REQUIRED],
            ['PAYMENT_SOURCE_CANNOT_BE_USED', PayPalException::PAYMENT_SOURCE_CANNOT_BE_USED],
            ['PAYMENT_SOURCE_DECLINED_BY_PROCESSOR', PayPalException::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR],
            ['PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED', PayPalException::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED],
            ['POSTAL_CODE_REQUIRED', PayPalException::POSTAL_CODE_REQUIRED],
            ['SHIPPING_ADDRESS_INVALID', PayPalException::SHIPPING_ADDRESS_INVALID],
            ['TAX_TOTAL_MISMATCH', PayPalException::TAX_TOTAL_MISMATCH],
            ['TAX_TOTAL_REQUIRED', PayPalException::TAX_TOTAL_REQUIRED],
            ['UNSUPPORTED_INTENT', PayPalException::UNSUPPORTED_INTENT],
            ['UNSUPPORTED_PAYMENT_INSTRUCTION', PayPalException::UNSUPPORTED_PAYMENT_INSTRUCTION],
            ['SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT', PayPalException::SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT],
            ['UNSUPPORTED_SHIPPING_TYPE', PayPalException::UNSUPPORTED_SHIPPING_TYPE],
            ['SHIPPING_OPTION_NOT_SELECTED', PayPalException::SHIPPING_OPTION_NOT_SELECTED],
            ['SHIPPING_OPTIONS_NOT_SUPPORTED', PayPalException::SHIPPING_OPTIONS_NOT_SUPPORTED],
            ['MULTIPLE_SHIPPING_OPTION_SELECTED', PayPalException::MULTIPLE_SHIPPING_OPTION_SELECTED],
            ['PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH', PayPalException::PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH],
            ['CARD_CLOSED', PayPalException::CARD_CLOSED],
            ['ORDER_CANNOT_BE_SAVED', PayPalException::ORDER_CANNOT_BE_SAVED],
            ['SAVE_ORDER_NOT_SUPPORTED', PayPalException::SAVE_ORDER_NOT_SUPPORTED],
            ['PUI_DUPLICATE_ORDER', PayPalException::PUI_DUPLICATE_ORDER],
            ['INVALID_JSON_POINTER_FORMAT', PayPalException::INVALID_JSON_POINTER_FORMAT],
            ['INVALID_PARAMETER', PayPalException::INVALID_PARAMETER],
            ['NOT_PATCHABLE', PayPalException::NOT_PATCHABLE],
            ['UNSUPPORTED_PATCH_PARAMETER_VALUE', PayPalException::UNSUPPORTED_PATCH_PARAMETER_VALUE],
            ['PATCH_VALUE_REQUIRED', PayPalException::PATCH_VALUE_REQUIRED],
            ['PATCH_PATH_REQUIRED', PayPalException::PATCH_PATH_REQUIRED],
            ['REFERENCE_ID_NOT_FOUND', PayPalException::REFERENCE_ID_NOT_FOUND],
            ['MULTI_CURRENCY_ORDER', PayPalException::MULTI_CURRENCY_ORDER],
            ['ORDER_ALREADY_COMPLETED', PayPalException::ORDER_ALREADY_COMPLETED],
            ['AGREEMENT_ALREADY_CANCELLED', PayPalException::AGREEMENT_ALREADY_CANCELLED],
            ['BILLING_AGREEMENT_NOT_FOUND', PayPalException::BILLING_AGREEMENT_NOT_FOUND],
            ['COMPLIANCE_VIOLATION', PayPalException::COMPLIANCE_VIOLATION],
            ['DOMESTIC_TRANSACTION_REQUIRED', PayPalException::DOMESTIC_TRANSACTION_REQUIRED],
            ['DUPLICATE_INVOICE_ID', PayPalException::DUPLICATE_INVOICE_ID],
            ['INSTRUMENT_DECLINED', PayPalException::INSTRUMENT_DECLINED],
            ['ORDER_NOT_APPROVED', PayPalException::ORDER_NOT_APPROVED],
            ['MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED', PayPalException::MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED],
            ['PAYEE_BLOCKED_TRANSACTION', PayPalException::PAYEE_BLOCKED_TRANSACTION],
            ['PAYER_ACCOUNT_LOCKED_OR_CLOSED', PayPalException::PAYER_ACCOUNT_LOCKED_OR_CLOSED],
            ['PAYER_ACCOUNT_RESTRICTED', PayPalException::PAYER_ACCOUNT_RESTRICTED],
            ['PAYER_CANNOT_PAY', PayPalException::PAYER_CANNOT_PAY],
            ['TRANSACTION_LIMIT_EXCEEDED', PayPalException::TRANSACTION_LIMIT_EXCEEDED],
            ['TRANSACTION_RECEIVING_LIMIT_EXCEEDED', PayPalException::TRANSACTION_RECEIVING_LIMIT_EXCEEDED],
            ['TRANSACTION_REFUSED', PayPalException::TRANSACTION_REFUSED],
            ['REDIRECT_PAYER_FOR_ALTERNATE_FUNDING', PayPalException::REDIRECT_PAYER_FOR_ALTERNATE_FUNDING],
            ['ORDER_ALREADY_CAPTURED', PayPalException::ORDER_ALREADY_CAPTURED],
            ['TRANSACTION_BLOCKED_BY_PAYEE', PayPalException::TRANSACTION_BLOCKED_BY_PAYEE],
            ['ORDER_ALREADY_CAPTURED', PayPalException::ORDER_ALREADY_CAPTURED],
            ['AUTH_CAPTURE_NOT_ENABLED', PayPalException::AUTH_CAPTURE_NOT_ENABLED],
            ['NOT_ENABLED_FOR_CARD_PROCESSING', PayPalException::NOT_ENABLED_FOR_CARD_PROCESSING],
            ['PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING', PayPalException::PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING],
            ['INVALID_PICKUP_ADDRESS', PayPalException::INVALID_PICKUP_ADDRESS],
            ['SHIPPING_ADDRESS_INVALID', PayPalException::SHIPPING_ADDRESS_INVALID],
            ['CANNOT_PROCESS_REFUNDS', PayPalException::CANNOT_PROCESS_REFUNDS],
            ['INVALID_REFUND_AMOUNT', PayPalException::INVALID_REFUND_AMOUNT],
            ['PAYMENT_DENIED', PayPalException::PAYMENT_DENIED],
        ];
    }
}
