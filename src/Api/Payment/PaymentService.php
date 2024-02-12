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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment;

use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\RequestException;
use Http\Client\Exception\TransferException;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PayPalOrderHttpClient;
use PrestaShop\Module\PrestashopCheckout\DTO\Orders\CreatePayPalOrderRequest;
use Psr\Http\Message\ResponseInterface;

class PaymentService
{
    /**
     * @var PayPalOrderHttpClient
     */
    private $client;

    public function __construct(PayPalOrderHttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * @param CreatePayPalOrderRequest $request
     * @return ResponseInterface|void
     */
    public function createOrder(CreatePayPalOrderRequest $request)
    {
        $payload = (array) $request;
        try {
            return $this->client->createOrder($payload);
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            if ($response->getStatusCode() === 400) {
                // INVALID_REQUEST :
                // - INVALID_ARRAY_MAX_ITEMS
                // - INVALID_ARRAY_MIN_ITEMS
                // - INVALID_COUNTRY_CODE
                // - INVALID_PARAMETER_SYNTAX
                // - INVALID_STRING_LENGTH
                // - INVALID_PARAMETER_VALUE
                // - MISSING_REQUIRED_PARAMETER
                // - NOT_SUPPORTED
                // - PAYPAL_REQUEST_ID_REQUIRED
                // - MALFORMED_REQUEST_JSON
            }
            if ($response->getStatusCode() === 401) {
                // NOT_AUTHORIZED
                // - PERMISSION_DENIED
                // - PERMISSION_DENIED_FOR_DONATION_ITEMS
                // - MALFORMED_REQUEST
            }
            if ($response->getStatusCode() === 422) {
                // UNPROCESSABLE_ENTITY
                // - AMOUNT_MISMATCH
                // - BILLING_ADDRESS_INVALID
                // - CANNOT_BE_NEGATIVE
                // - CANNOT_BE_ZERO_OR_NEGATIVE
                // - CARD_EXPIRED
                // - CITY_REQUIRED
                // - DECIMAL_PRECISION
                // - DONATION_ITEMS_NOT_SUPPORTED
                // - DUPLICATE_REFERENCE_ID
                // - INVALID_CURRENCY_CODE
                // - INVALID_PAYER_ID
                // - ITEM_TOTAL_MISMATCH
                // - ITEM_TOTAL_REQUIRED
                // - MAX_VALUE_EXCEEDED
                // - MISSING_PICKUP_ADDRESS
                // - MULTI_CURRENCY_ORDER
                // - MULTIPLE_ITEM_CATEGORIES
                // - MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED
                // - MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED
                // - PAYEE_ACCOUNT_INVALID
                // - PAYEE_ACCOUNT_LOCKED_OR_CLOSED
                // - PAYEE_ACCOUNT_RESTRICTED
                // - REFERENCE_ID_REQUIRED
                // - PAYMENT_SOURCE_CANNOT_BE_USED
                // - PAYMENT_SOURCE_DECLINED_BY_PROCESSOR
                // - PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED
                // - POSTAL_CODE_REQUIRED
                // - SHIPPING_ADDRESS_INVALID
                // - TAX_TOTAL_MISMATCH
                // - TAX_TOTAL_REQUIRED
                // - UNSUPPORTED_INTENT
                // - UNSUPPORTED_PAYMENT_INSTRUCTION
                // - SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT
                // - UNSUPPORTED_SHIPPING_TYPE
                // - SHIPPING_OPTION_NOT_SELECTED
                // - SHIPPING_OPTIONS_NOT_SUPPORTED
                // - MULTIPLE_SHIPPING_OPTION_SELECTED
                // - PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH
                // - CARD_CLOSED
                // - ORDER_CANNOT_BE_SAVED
                // - SAVE_ORDER_NOT_SUPPORTED
                // - PUI_DUPLICATE_ORDER
            }
        }
    }

    public function updateOrder(array $payload)
    {
        return $this->sendRequest('POST', '/payments/order/update', [], $payload);
    }

    /**
     * @param array{order_id: string} $data
     *
     * @return ResponseInterface
     */
    public function getOrder(array $data)
    {
        $payload = [
            'orderId' => $data['order_id'],
        ];

        return $this->sendRequest('POST', '/payments/order/fetch', [], $payload);
    }

    /**
     * @param array{funding_source: string, order_id: string, merchant_id: string} $data
     *
     * @return ResponseInterface
     */
    public function captureOrder(array $data)
    {
        $payload = [
            'mode' => $data['funding_source'],
            'orderId' => (string) $data['order_id'],
            'payee' => [
                'merchant_id' => $data['merchant_id'],
            ],
        ];

        return $this->sendRequest('POST', '/payments/order/capture', [], $payload);
    }

    public function refundOrder(array $payload)
    {
        return $this->sendRequest('POST', '/payments/order/refund', [], $payload);
    }

    /**
     * @param array{merchant_id: string} $data
     *
     * @return ResponseInterface
     */
    public function getIdentityToken(array $data)
    {
        $payload = [
            'return_payload' => true,
            'payee' => [
                'merchant_id' => $data['merchant_id'],
            ],
        ];

        try {
            return $this->sendRequest('POST', '/payments/order/generate_client_token', [], $payload);
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            if ($response->getStatusCode() === 400) {
                // INVALID_REQUEST
            }
            if ($response->getStatusCode() === 401) {
                // NOT_AUTHORIZED
            }
            if ($response->getStatusCode() === 404) {
                // RESOURCE_NOT_FOUND
            }
            if ($response->getStatusCode() === 422) {
                // UNPROCESSABLE_ENTITY
            }

            return $response;
        }
    }

    /**
     * @param string $method
     * @param string $uri
     * @param array $options
     * @param array $payload
     *
     * @return ResponseInterface
     *
     * @throws NetworkException
     * @throws HttpException
     * @throws RequestException
     * @throws TransferException
     */
    private function sendRequest($method, $uri, $options, $payload)
    {
        try {
            return $this->client->sendRequest(new Request($method, $uri, $options, json_encode($payload)));
        } catch (NetworkException $exception) {
            throw $exception; // TODO Replace
            // Thrown when the request cannot be completed because of network issues.
            // No response here
        } catch (HttpException $exception) {
            // Thrown when a response was received but the request itself failed.
            // There a response here
            // So this one contains why response failed with Maasland error response
            if ($exception->getResponse()->getStatusCode() === 500) {
                throw $exception; // TODO Replace
                // Internal Server Error: retry then stop using Maasland for XXX times after X failed retries, requires a circuit breaker
            }
            if ($exception->getResponse()->getStatusCode() === 503) {
                throw $exception; // TODO Replace
                // Service Unavailable: we should stop using Maasland, requires a circuit breaker
            }
            // response status code 4XX throw exception to be catched on specific method
            throw $exception; // Avoid this to be catched next
        } catch (RequestException $exception) {
            throw $exception; // TODO Replace
            // No response here
        } catch (TransferException $exception) {
            throw $exception; // TODO Replace
            // others without response
        }
    }
}
