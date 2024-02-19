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

use Http\Client\Exception\HttpException;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PayPalOrderHttpClient;
use PrestaShop\Module\PrestashopCheckout\DTO\Orders\CreatePayPalOrderRequestInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Exception\UnprocessableEntityException;
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
     * @param CreatePayPalOrderRequestInterface $request
     *
     * @return ResponseInterface|void
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException
     */
    public function createOrder(CreatePayPalOrderRequestInterface $request)
    {
        $payload = (array) $request;
        try {
            return $this->client->createOrder($payload);
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $errorMsg = $this->getErrorMessage($response->getBody()->getContents());
            switch ($response->getStatusCode()) {
                case 400:
                    switch ($errorMsg) {
                        case 'INVALID_ARRAY_MAX_ITEMS':
                            throw new InvalidRequestException('The number of items in an array parameter is too large', InvalidRequestException::INVALID_ARRAY_MAX_ITEMS);
                        case 'INVALID_ARRAY_MIN_ITEMS':
                            throw new InvalidRequestException('The number of items in an array parameter is too small', InvalidRequestException::INVALID_ARRAY_MIN_ITEMS);
                        case 'INVALID_COUNTRY_CODE':
                            throw new InvalidRequestException('Country code is invalid', InvalidRequestException::INVALID_COUNTRY_CODE);
                        case 'INVALID_PARAMETER_SYNTAX':
                            throw new InvalidRequestException('The value of a field does not conform to the expected format', InvalidRequestException::INVALID_PARAMETER_SYNTAX);
                        case 'INVALID_STRING_LENGTH':
                            throw new InvalidRequestException('The value of a field is either too short or too long', InvalidRequestException::INVALID_STRING_LENGTH);
                        case 'INVALID_PARAMETER_VALUE':
                            throw new InvalidRequestException('A parameter value is not valid', InvalidRequestException::INVALID_PARAMETER_VALUE);
                        case 'MISSING_REQUIRED_PARAMETER':
                            throw new InvalidRequestException('A required parameter is missing', InvalidRequestException::MISSING_REQUIRED_PARAMETER);
                        case 'NOT_SUPPORTED':
                            throw new InvalidRequestException('A field used is not currently supported', InvalidRequestException::NOT_SUPPORTED);
                        case 'PAYPAL_REQUEST_ID_REQUIRED':
                            throw new InvalidRequestException('A PayPal-Request-Id is required if you are trying to process payment for an Order', InvalidRequestException::PAYPAL_REQUEST_ID_REQUIRED);
                        case 'MALFORMED_REQUEST_JSON':
                            throw new InvalidRequestException('The request JSON is not well formed', InvalidRequestException::MALFORMED_REQUEST_JSON);
                        default:
                            throw new InvalidRequestException(sprintf('InvalidRequest unknown error : %s', $errorMsg), InvalidRequestException::UNKNOWN);
                    }
                    // no break
                case 401:
                    switch ($errorMsg) {
                        case 'PERMISSION_DENIED':
                            throw new NotAuthorizedException('You do not have permission to access or perform operations on this resource', NotAuthorizedException::PERMISSION_DENIED);
                        case 'PERMISSION_DENIED_FOR_DONATION_ITEMS':
                            throw new NotAuthorizedException('The payee have not been granted appropriate permissions to send items.category as DONATION', NotAuthorizedException::PERMISSION_DENIED_FOR_DONATION_ITEMS);
                        case 'MALFORMED_REQUEST':
                            throw new NotAuthorizedException('You have sent a request that PayPal server could not understand', NotAuthorizedException::MALFORMED_REQUEST);
                        default:
                            throw new NotAuthorizedException(sprintf('NotAuthorized unknown error : %s', $errorMsg), NotAuthorizedException::UNKNOWN);
                    }
                    // no break
                case 422:
                    switch ($errorMsg) {
                        case 'AMOUNT_MISMATCH':
                            throw new UnprocessableEntityException('Total amount mismatch with the breakdown', UnprocessableEntityException::AMOUNT_MISMATCH);
                        case 'BILLING_ADDRESS_INVALID':
                            throw new UnprocessableEntityException('Provided billing address is invalid', UnprocessableEntityException::BILLING_ADDRESS_INVALID);
                        case 'CANNOT_BE_NEGATIVE':
                            throw new UnprocessableEntityException('Currency must be greater than or equal to zero', UnprocessableEntityException::CANNOT_BE_NEGATIVE);
                        case 'CANNOT_BE_ZERO_OR_NEGATIVE':
                            throw new UnprocessableEntityException('Currency must be greater than zero', UnprocessableEntityException::CANNOT_BE_ZERO_OR_NEGATIVE);
                        case 'CARD_EXPIRED':
                            throw new UnprocessableEntityException('The payment card provided is expired', UnprocessableEntityException::CARD_EXPIRED);
                        case 'CITY_REQUIRED':
                            throw new UnprocessableEntityException('The specified country requires a city in address.admin_area_2', UnprocessableEntityException::CITY_REQUIRED);
                        case 'DECIMAL_PRECISION':
                            throw new UnprocessableEntityException('If the currency supports decimals, only two decimal places are supported', UnprocessableEntityException::DECIMAL_PRECISION);
                        case 'DONATION_ITEMS_NOT_SUPPORTED':
                            throw new UnprocessableEntityException('If purchase_unit has DONATION as the items.category, then the order can at most have one purchase_unit', UnprocessableEntityException::DONATION_ITEMS_NOT_SUPPORTED);
                        case 'DUPLICATE_REFERENCE_ID':
                            throw new UnprocessableEntityException('The reference_id must be unique', UnprocessableEntityException::DUPLICATE_REFERENCE_ID);
                        case 'INVALID_CURRENCY_CODE':
                            throw new UnprocessableEntityException('Currency code is invalid or is not currently supported', UnprocessableEntityException::INVALID_CURRENCY_CODE);
                        case 'INVALID_PAYER_ID':
                            throw new UnprocessableEntityException('The payer ID is not valid', UnprocessableEntityException::INVALID_PAYER_ID);
                        case 'ITEM_TOTAL_MISMATCH':
                            throw new UnprocessableEntityException('Should equal sum of unit_amount * quantity across all items for a given purchase_unit', UnprocessableEntityException::ITEM_TOTAL_MISMATCH);
                        case 'ITEM_TOTAL_REQUIRED':
                            throw new UnprocessableEntityException('If item details are specified, items.unit_amount, items.quantity and amount.breakdown.item_total are required', UnprocessableEntityException::ITEM_TOTAL_REQUIRED);
                        case 'MAX_VALUE_EXCEEDED':
                            throw new UnprocessableEntityException('Should be less than or equal to 9999999.99', UnprocessableEntityException::MAX_VALUE_EXCEEDED);
                        case 'MISSING_PICKUP_ADDRESS':
                            throw new UnprocessableEntityException('A pickup address (shipping.address) is required for the provided shipping.type', UnprocessableEntityException::MISSING_PICKUP_ADDRESS);
                        case 'MULTI_CURRENCY_ORDER':
                            throw new UnprocessableEntityException('Multiple differing values of currency_code are not supported', UnprocessableEntityException::MULTI_CURRENCY_ORDER);
                        case 'MULTIPLE_ITEM_CATEGORIES':
                            throw new UnprocessableEntityException('For a given purchase unit, items.category as DONATION cannot be combined with items with either PHYSICAL_GOODS or DIGITAL_GOODS', UnprocessableEntityException::MULTIPLE_ITEM_CATEGORIES);
                        case 'MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED':
                            throw new UnprocessableEntityException('Multiple shipping addresses are not supported', UnprocessableEntityException::MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED);
                        case 'MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED':
                            throw new UnprocessableEntityException('Different shipping.type are not supported across purchase units', UnprocessableEntityException::MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED);
                        case 'PAYEE_ACCOUNT_INVALID':
                            throw new UnprocessableEntityException('Mismatch between request payeeId and payeeEmail', UnprocessableEntityException::PAYEE_ACCOUNT_INVALID);
                        case 'PAYEE_ACCOUNT_LOCKED_OR_CLOSED':
                            throw new UnprocessableEntityException('The merchant account is locked or closed', UnprocessableEntityException::PAYEE_ACCOUNT_LOCKED_OR_CLOSED);
                        case 'PAYEE_ACCOUNT_RESTRICTED':
                            throw new UnprocessableEntityException('The merchant account is restricted', UnprocessableEntityException::PAYEE_ACCOUNT_RESTRICTED);
                        case 'REFERENCE_ID_REQUIRED':
                            throw new UnprocessableEntityException('The reference_id is required for each purchase_unit', UnprocessableEntityException::REFERENCE_ID_REQUIRED);
                        case 'PAYMENT_SOURCE_CANNOT_BE_USED':
                            throw new UnprocessableEntityException('The provided payment source cannot be used to pay for the order', UnprocessableEntityException::PAYMENT_SOURCE_CANNOT_BE_USED);
                        case 'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR':
                            throw new UnprocessableEntityException('The provided payment source is declined by the processor', UnprocessableEntityException::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR);
                        case 'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED':
                            throw new UnprocessableEntityException('The provided payment source is declined by the processor', UnprocessableEntityException::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED);
                        case 'POSTAL_CODE_REQUIRED':
                            throw new UnprocessableEntityException('The specified country requires a postal code', UnprocessableEntityException::POSTAL_CODE_REQUIRED);
                        case 'SHIPPING_ADDRESS_INVALID':
                            throw new UnprocessableEntityException('Provided shipping address is invalid', UnprocessableEntityException::SHIPPING_ADDRESS_INVALID);
                        case 'TAX_TOTAL_MISMATCH':
                            throw new UnprocessableEntityException('Should equal sum of tax * quantity across all items for a given purchase unit', UnprocessableEntityException::TAX_TOTAL_MISMATCH);
                        case 'TAX_TOTAL_REQUIRED':
                            throw new UnprocessableEntityException('If item details are specified, items.tax_total, items.quantity, and amount.breakdown.tax_total are required', UnprocessableEntityException::TAX_TOTAL_REQUIRED);
                        case 'UNSUPPORTED_INTENT':
                            throw new UnprocessableEntityException('The intent AUTHORIZE is not supported for multiple purchase units', UnprocessableEntityException::UNSUPPORTED_INTENT);
                        case 'UNSUPPORTED_PAYMENT_INSTRUCTION':
                            throw new UnprocessableEntityException('You must provide the payment instruction when you capture an authorized payment using intent AUTHORIZE', UnprocessableEntityException::UNSUPPORTED_PAYMENT_INSTRUCTION);
                        case 'SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT':
                            throw new UnprocessableEntityException('PayPal account is not setup to be able to support a shipping.type PICKUP_IN_PERSON', UnprocessableEntityException::SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT);
                        case 'UNSUPPORTED_SHIPPING_TYPE':
                            throw new UnprocessableEntityException('The provided shipping.type is only supported for application_context.shipping_preference SET_PROVIDED_ADDRESS or NO_SHIPPING', UnprocessableEntityException::UNSUPPORTED_SHIPPING_TYPE);
                        case 'SHIPPING_OPTION_NOT_SELECTED':
                            throw new UnprocessableEntityException('At least one of the shipping.option should be set to selected = true', UnprocessableEntityException::SHIPPING_OPTION_NOT_SELECTED);
                        case 'SHIPPING_OPTIONS_NOT_SUPPORTED':
                            throw new UnprocessableEntityException('Shipping options are not supported when application_context.shipping_preference is set to NO_SHIPPING or SET_PROVIDED_ADDRESS', UnprocessableEntityException::SHIPPING_OPTIONS_NOT_SUPPORTED);
                        case 'MULTIPLE_SHIPPING_OPTION_SELECTED':
                            throw new UnprocessableEntityException('Only one shipping.option can be set to selected = true', UnprocessableEntityException::MULTIPLE_SHIPPING_OPTION_SELECTED);
                        case 'PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH':
                            throw new UnprocessableEntityException('The amount provided in the preferred shipping option should match the amount provided in amount breakdown', UnprocessableEntityException::PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH);
                        case 'CARD_CLOSED':
                            throw new UnprocessableEntityException('The card is closed with the issuer', UnprocessableEntityException::CARD_CLOSED);
                        case 'ORDER_CANNOT_BE_SAVED':
                            throw new UnprocessableEntityException('The option to save an order is only available if the intent is AUTHORIZE and processing_instruction uses one of the ORDER_SAVED options', UnprocessableEntityException::ORDER_CANNOT_BE_SAVED);
                        case 'SAVE_ORDER_NOT_SUPPORTED':
                            throw new UnprocessableEntityException('PayPal account is setup in a way that does not allow it to be used for saving the order', UnprocessableEntityException::SAVE_ORDER_NOT_SUPPORTED);
                        case 'PUI_DUPLICATE_ORDER':
                            throw new UnprocessableEntityException('A Pay Upon Invoice order with the same payload has already been successfully processed in the last few seconds', UnprocessableEntityException::PUI_DUPLICATE_ORDER);
                        default:
                            throw new UnprocessableEntityException(sprintf('UnprocessableEntity unknown error : %s', $errorMsg), UnprocessableEntityException::UNKNOWN);
                    }
            }
        }
    }

    public function updateOrder(array $payload)
    {
        return $this->client->updateOrder($payload);
    }

    /**
     * @param string $orderId
     *
     * @return ResponseInterface
     */
    public function getOrder($orderId)
    {
        $payload = [
            'orderId' => $orderId,
        ];

        return $this->client->fetchOrder($payload);
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

        return $this->client->captureOrder($payload);
    }

    public function refundOrder(array $payload)
    {
        return $this->client->refundOrder($payload);
    }

    /**
     * @param string $merchantId
     *
     * @return ResponseInterface
     */
    public function getIdentityToken($merchantId)
    {
        $payload = [
            'return_payload' => true,
            'payee' => [
                'merchant_id' => $merchantId,
            ],
        ];

        try {
            return $this->client->generateClientToken($payload);
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
     * @param string $body
     *
     * @return string
     */
    private function getErrorMessage($body)
    {
        $body = json_decode($body, true);
        if (isset($body['details'][0]['issue']) && $body['details'][0]['issue']) {
            return $body['details'][0]['issue'];
        }
        if ($body['name']) {
            return $body['name'];
        }
        if ($body['error']) {
            return $body['error'];
        }

        return '';
    }
}
