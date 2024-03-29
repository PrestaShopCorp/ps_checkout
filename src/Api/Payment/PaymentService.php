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

use Exception;
use Http\Client\Exception\HttpException;
use PrestaShop\Module\PrestashopCheckout\DTO\Orders\CreatePayPalOrderRequestInterface;
use PrestaShop\Module\PrestashopCheckout\DTO\Orders\UpdatePayPalOrderRequestInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\InvalidRequestException;
use PrestaShop\Module\PrestashopCheckout\Exception\NotAuthorizedException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\UnprocessableEntityException;
use PrestaShop\Module\PrestashopCheckout\Http\CheckoutHttpClient;
use PrestaShop\Module\PrestashopCheckout\Http\CheckoutHttpClientInterface;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO\CreatePayPalOrderResponse;
use PrestaShop\Module\PrestashopCheckout\Serializer\ObjectSerializerInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

class PaymentService
{
    /**
     * @var CheckoutHttpClientInterface
     */
    private $client;
    /**
     * @var ObjectSerializerInterface
     */
    private $serializer;

    public function __construct(CheckoutHttpClientInterface $client, ObjectSerializerInterface $serializer)
    {
        $this->client = $client;
        $this->serializer = $serializer;
    }

    /**
     * @param CreatePayPalOrderRequestInterface $request
     *
     * @return CreatePayPalOrderResponse
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    public function createOrder(CreatePayPalOrderRequestInterface $request)
    {
        try {
            $response = $this->client->createOrder($this->serializer->serialize($request, JsonEncoder::FORMAT, true));

            return $this->serializer->deserialize($response->getBody()->getContents(), CreatePayPalOrderResponse::class, JsonEncoder::FORMAT);
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
                    // no break
                default:
                    throw new PsCheckoutException(sprintf('Unknown error : %s', $errorMsg), PsCheckoutException::UNKNOWN);
            }
        }
    }

    /**
     * @param UpdatePayPalOrderRequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    public function updateOrder(UpdatePayPalOrderRequestInterface $request)
    {
        try {
            return $this->client->updateOrder($this->serializer->serialize($request, JsonEncoder::FORMAT, true));
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $errorMsg = $this->getErrorMessage($response->getBody()->getContents());
            switch ($response->getStatusCode()) {
                case 400:
                    switch ($errorMsg) {
                        case 'FIELD_NOT_PATCHABLE':
                            throw new InvalidRequestException('Field cannot be patched', InvalidRequestException::FIELD_NOT_PATCHABLE);
                        case 'INVALID_ARRAY_MAX_ITEMS':
                            throw new InvalidRequestException('The number of items in an array parameter is too large', InvalidRequestException::INVALID_ARRAY_MAX_ITEMS);
                        case 'INVALID_PARAMETER_SYNTAX':
                            throw new InvalidRequestException('The value of a field does not conform to the expected format', InvalidRequestException::INVALID_PARAMETER_SYNTAX);
                        case 'INVALID_STRING_LENGTH':
                            throw new InvalidRequestException('The value of a field is either too short or too long', InvalidRequestException::INVALID_STRING_LENGTH);
                        case 'INVALID_PARAMETER_VALUE':
                            throw new InvalidRequestException('The value of a field is invalid', InvalidRequestException::INVALID_PARAMETER_VALUE);
                        case 'MISSING_REQUIRED_PARAMETER':
                            throw new InvalidRequestException('A required field or parameter is missing', InvalidRequestException::MISSING_REQUIRED_PARAMETER);
                        case 'AMOUNT_NOT_PATCHABLE':
                            throw new InvalidRequestException('The amount cannot be updated as the payer has chosen and approved a specific financing offer for a given amount', InvalidRequestException::AMOUNT_NOT_PATCHABLE);
                        case 'INVALID_PATCH_OPERATION':
                            throw new InvalidRequestException('The operation cannot be honored. Cannot add a already existing property nor remove a property that is not present', InvalidRequestException::INVALID_PATCH_OPERATION);
                        default:
                            throw new InvalidRequestException(sprintf('InvalidRequest unknown error : %s', $errorMsg), InvalidRequestException::UNKNOWN);
                    }
                    // no break
                case 401:
                    switch ($errorMsg) {
                        case 'PERMISSION_DENIED':
                            throw new NotAuthorizedException('You do not have permission to access or perform operations on this resource', NotAuthorizedException::PERMISSION_DENIED);
                        case 'PAYEE_ACCOUNT_NOT_SUPPORTED':
                            throw new NotAuthorizedException('Payee does not have an account', NotAuthorizedException::PAYEE_ACCOUNT_NOT_SUPPORTED);
                        case 'PAYEE_ACCOUNT_NOT_VERIFIED':
                            throw new NotAuthorizedException('Payee has not verified their account with PayPal', NotAuthorizedException::PAYEE_ACCOUNT_NOT_VERIFIED);
                        case 'PAYEE_NOT_CONSENTED':
                            throw new NotAuthorizedException('Payee does not have appropriate consent to allow the API caller to process this type of transaction on their behalf', NotAuthorizedException::PAYEE_NOT_CONSENTED);
                        default:
                            throw new NotAuthorizedException(sprintf('NotAuthorized unknown error : %s', $errorMsg), NotAuthorizedException::UNKNOWN);
                    }
                    // no break
                case 422:
                    switch ($errorMsg) {
                        case 'INVALID_JSON_POINTER_FORMAT':
                            throw new UnprocessableEntityException('Path should be a valid JSON Pointer that references a location within the request', UnprocessableEntityException::INVALID_JSON_POINTER_FORMAT);
                        case 'INVALID_PARAMETER':
                            throw new UnprocessableEntityException('Cannot be specified as part of the request', UnprocessableEntityException::INVALID_PARAMETER);
                        case 'NOT_PATCHABLE':
                            throw new UnprocessableEntityException('Cannot be patched', UnprocessableEntityException::NOT_PATCHABLE);
                        case 'UNSUPPORTED_PATCH_PARAMETER_VALUE':
                            throw new UnprocessableEntityException('The value specified for this field is not currently supported', UnprocessableEntityException::UNSUPPORTED_PATCH_PARAMETER_VALUE);
                        case 'PATCH_VALUE_REQUIRED':
                            throw new UnprocessableEntityException('Specify a value for the field being patched', UnprocessableEntityException::PATCH_VALUE_REQUIRED);
                        case 'PATCH_PATH_REQUIRED':
                            throw new UnprocessableEntityException('Specify a value for the field in which the operation needs to be performed', UnprocessableEntityException::PATCH_PATH_REQUIRED);
                        case 'REFERENCE_ID_NOT_FOUND':
                            throw new UnprocessableEntityException('Filter expression value is incorrect. Check the value of the reference_id', UnprocessableEntityException::REFERENCE_ID_NOT_FOUND);
                        case 'MULTI_CURRENCY_ORDER':
                            throw new UnprocessableEntityException('Multiple differing values of currency_code are not supported', UnprocessableEntityException::MULTI_CURRENCY_ORDER);
                        case 'SHIPPING_OPTION_NOT_SELECTED':
                            throw new UnprocessableEntityException('At least one of the shipping.option should be set to selected = true', UnprocessableEntityException::SHIPPING_OPTION_NOT_SELECTED);
                        case 'SHIPPING_OPTIONS_NOT_SUPPORTED':
                            throw new UnprocessableEntityException('Shipping options are not supported when application_context.shipping_preference is set to NO_SHIPPING or SET_PROVIDED_ADDRESS', UnprocessableEntityException::SHIPPING_OPTIONS_NOT_SUPPORTED);
                        case 'MULTIPLE_SHIPPING_OPTION_SELECTED':
                            throw new UnprocessableEntityException('Only one shipping.option can be set to selected = true', UnprocessableEntityException::MULTIPLE_SHIPPING_OPTION_SELECTED);
                        case 'ORDER_ALREADY_COMPLETED':
                            throw new UnprocessableEntityException('The order cannot be patched after it is completed', UnprocessableEntityException::ORDER_ALREADY_COMPLETED);
                        case 'PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH':
                            throw new UnprocessableEntityException('The amount provided in the preferred shipping option should match the amount provided in amount breakdown', UnprocessableEntityException::PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH);
                        default:
                            throw new UnprocessableEntityException(sprintf('UnprocessableEntity unknown error : %s', $errorMsg), UnprocessableEntityException::UNKNOWN);
                    }
                    // no break
                default:
                    throw new PsCheckoutException(sprintf('Unknown error : %s', $errorMsg), PsCheckoutException::UNKNOWN);
            }
        }
    }

    /**
     * @param string $orderId
     *
     * @return ResponseInterface
     *
     * @throws NotAuthorizedException|PsCheckoutException
     */
    public function getOrder($orderId)
    {
        $payload = [
            'orderId' => $orderId,
        ];

        try {
            return $this->client->fetchOrder($this->serializer->serialize($payload, JsonEncoder::FORMAT, true));
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $errorMsg = $this->getErrorMessage($response->getBody()->getContents());
            switch ($response->getStatusCode()) {
                case 401:
                    switch ($errorMsg) {
                        case 'PERMISSION_DENIED':
                            throw new NotAuthorizedException('You do not have permission to access or perform operations on this resource', NotAuthorizedException::PERMISSION_DENIED);
                        case 'invalid_token':
                            throw new NotAuthorizedException('Token signature verification failed', NotAuthorizedException::INVALID_TOKEN);
                        default:
                            throw new NotAuthorizedException(sprintf('NotAuthorized unknown error : %s', $errorMsg), NotAuthorizedException::UNKNOWN);
                    }
                    // no break
                default:
                    throw new PsCheckoutException(sprintf('Unknown error : %s', $errorMsg), PsCheckoutException::UNKNOWN);
            }
        }
    }

    /**
     * @param array{funding_source: string, order_id: string, merchant_id: string} $data
     *
     * @return ResponseInterface
     *
     * @throws InvalidRequestException|NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
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

        try {
            return $this->client->captureOrder($this->serializer->serialize($payload, JsonEncoder::FORMAT, true));
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $errorMsg = $this->getErrorMessage($response->getBody()->getContents());
            switch ($response->getStatusCode()) {
                case 400:
                    switch ($errorMsg) {
                        case 'INVALID_PARAMETER_VALUE':
                            throw new InvalidRequestException('The value of a field is invalid', InvalidRequestException::INVALID_PARAMETER_VALUE);
                        case 'MISSING_REQUIRED_PARAMETER':
                            throw new InvalidRequestException('A required field or parameter is missing', InvalidRequestException::MISSING_REQUIRED_PARAMETER);
                        case 'INVALID_STRING_LENGTH':
                            throw new InvalidRequestException('The value of a field is either too short or too long', InvalidRequestException::INVALID_STRING_LENGTH);
                        default:
                            throw new InvalidRequestException(sprintf('InvalidRequest unknown error : %s', $errorMsg), InvalidRequestException::UNKNOWN);
                    }
                    // no break
                case 401:
                    switch ($errorMsg) {
                        case 'CONSENT_NEEDED':
                            throw new NotAuthorizedException('Payee consent needed', NotAuthorizedException::CONSENT_NEEDED);
                        case 'PERMISSION_DENIED':
                            throw new NotAuthorizedException('You do not have permission to access or perform operations on this resource', NotAuthorizedException::PERMISSION_DENIED);
                        case 'PERMISSION_DENIED_FOR_DONATION_ITEMS':
                            throw new NotAuthorizedException('The payee have not been granted appropriate permissions to send items.category as DONATION', NotAuthorizedException::PERMISSION_DENIED_FOR_DONATION_ITEMS);
                        default:
                            throw new NotAuthorizedException(sprintf('NotAuthorized unknown error : %s', $errorMsg), NotAuthorizedException::UNKNOWN);
                    }
                    // no break
                case 422:
                    switch ($errorMsg) {
                        case 'AGREEMENT_ALREADY_CANCELLED':
                            throw new UnprocessableEntityException('The requested agreement is already canceled', UnprocessableEntityException::AGREEMENT_ALREADY_CANCELLED);
                        case 'BILLING_AGREEMENT_NOT_FOUND':
                            throw new UnprocessableEntityException('The requested billing agreement token was not found', UnprocessableEntityException::BILLING_AGREEMENT_NOT_FOUND);
                        case 'CARD_EXPIRED':
                            throw new UnprocessableEntityException('The payment card provided is expired', UnprocessableEntityException::CARD_EXPIRED);
                        case 'COMPLIANCE_VIOLATION':
                            throw new UnprocessableEntityException('Transaction is declined due to compliance violation', UnprocessableEntityException::COMPLIANCE_VIOLATION);
                        case 'DOMESTIC_TRANSACTION_REQUIRED':
                            throw new UnprocessableEntityException('This transaction requires the payee and payer to be resident in the same country', UnprocessableEntityException::DOMESTIC_TRANSACTION_REQUIRED);
                        case 'DUPLICATE_INVOICE_ID':
                            throw new UnprocessableEntityException('Duplicate invoice ID detected', UnprocessableEntityException::DUPLICATE_INVOICE_ID);
                        case 'INSTRUMENT_DECLINED':
                            throw new UnprocessableEntityException('The instrument presented was either declined by the processor or bank or it cannot be used for this payment', UnprocessableEntityException::INSTRUMENT_DECLINED);
                        case 'ORDER_NOT_APPROVED':
                            throw new UnprocessableEntityException('Payer has not yet approved the Order for payment', UnprocessableEntityException::ORDER_NOT_APPROVED);
                        case 'MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED':
                            throw new UnprocessableEntityException('The maximum number of payment attempts has been exceeded', UnprocessableEntityException::MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED);
                        case 'PAYEE_BLOCKED_TRANSACTION':
                            throw new UnprocessableEntityException('The fraud settings for this seller are such that this payment cannot be executed', UnprocessableEntityException::PAYEE_BLOCKED_TRANSACTION);
                        case 'PAYER_ACCOUNT_LOCKED_OR_CLOSED':
                            throw new UnprocessableEntityException('The payer account cannot be used for this transaction', UnprocessableEntityException::PAYER_ACCOUNT_LOCKED_OR_CLOSED);
                        case 'PAYER_ACCOUNT_RESTRICTED':
                            throw new UnprocessableEntityException('The payer account is restricted', UnprocessableEntityException::PAYER_ACCOUNT_RESTRICTED);
                        case 'PAYER_CANNOT_PAY':
                            throw new UnprocessableEntityException('The payer cannot pay for this transaction', UnprocessableEntityException::PAYER_CANNOT_PAY);
                        case 'TRANSACTION_LIMIT_EXCEEDED':
                            throw new UnprocessableEntityException('Total payment amount exceeded transaction limit', UnprocessableEntityException::TRANSACTION_LIMIT_EXCEEDED);
                        case 'TRANSACTION_RECEIVING_LIMIT_EXCEEDED':
                            throw new UnprocessableEntityException('The transaction exceeds the receiver’s receiving limit', UnprocessableEntityException::TRANSACTION_RECEIVING_LIMIT_EXCEEDED);
                        case 'TRANSACTION_REFUSED':
                            throw new UnprocessableEntityException('The request was refused', UnprocessableEntityException::TRANSACTION_REFUSED);
                        case 'REDIRECT_PAYER_FOR_ALTERNATE_FUNDING':
                            throw new UnprocessableEntityException('Transaction failed. Redirect the payer to select another funding source', UnprocessableEntityException::REDIRECT_PAYER_FOR_ALTERNATE_FUNDING);
                        case 'ORDER_ALREADY_CAPTURED':
                            throw new UnprocessableEntityException('Order already captured', UnprocessableEntityException::ORDER_ALREADY_CAPTURED);
                        case 'TRANSACTION_BLOCKED_BY_PAYEE':
                            throw new UnprocessableEntityException('Transaction blocked by payee’s fraud protection settings', UnprocessableEntityException::TRANSACTION_BLOCKED_BY_PAYEE);
                        case 'AUTH_CAPTURE_NOT_ENABLED':
                            throw new UnprocessableEntityException('Authorization and capture feature is not enabled for the merchant', UnprocessableEntityException::AUTH_CAPTURE_NOT_ENABLED);
                        case 'NOT_ENABLED_FOR_CARD_PROCESSING':
                            throw new UnprocessableEntityException('The API caller account is not setup to be able to process card payments', UnprocessableEntityException::NOT_ENABLED_FOR_CARD_PROCESSING);
                        case 'PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING':
                            throw new UnprocessableEntityException('Payee account is not setup to be able to process card payments', UnprocessableEntityException::PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING);
                        case 'INVALID_PICKUP_ADDRESS':
                            throw new UnprocessableEntityException('If the shipping_option.type is set to PICKUP, then the shipping_detail.name.full_name should start with S2S', UnprocessableEntityException::INVALID_PICKUP_ADDRESS);
                        case 'SHIPPING_ADDRESS_INVALID':
                            throw new UnprocessableEntityException('Provided shipping address is invalid', UnprocessableEntityException::SHIPPING_ADDRESS_INVALID);
                        case 'CARD_CLOSED':
                            throw new UnprocessableEntityException('The card is closed with the issuer', UnprocessableEntityException::CARD_CLOSED);
                        default:
                            throw new UnprocessableEntityException(sprintf('UnprocessableEntity unknown error : %s', $errorMsg), UnprocessableEntityException::UNKNOWN);
                    }
                    // no break
                default:
                    throw new PsCheckoutException(sprintf('Unknown error : %s', $errorMsg), PsCheckoutException::UNKNOWN);
            }
        }
    }

    /**
     * @throws NotAuthorizedException|UnprocessableEntityException|PsCheckoutException
     */
    public function refundOrder(array $payload)
    {
        try {
            return $this->client->refundOrder($this->serializer->serialize($payload, JsonEncoder::FORMAT, true));
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $errorMsg = $this->getErrorMessage($response->getBody()->getContents());
            switch ($response->getStatusCode()) {
                case 401:
                    switch ($errorMsg) {
                        case 'invalid_token':
                            throw new NotAuthorizedException('Token signature verification failed', NotAuthorizedException::INVALID_TOKEN);
                        default:
                            throw new NotAuthorizedException(sprintf('NotAuthorized unknown error : %s', $errorMsg), NotAuthorizedException::UNKNOWN);
                    }
                    // no break
                case 422:
                    switch ($errorMsg) {
                        case 'CANNOT_PROCESS_REFUNDS':
                            throw new UnprocessableEntityException('Current invoice state does not support refunds', UnprocessableEntityException::CANNOT_PROCESS_REFUNDS);
                        case 'INVALID_REFUND_AMOUNT':
                            throw new UnprocessableEntityException('Recorded refunds cannot exceed recorded payments', UnprocessableEntityException::INVALID_REFUND_AMOUNT);
                        default:
                            throw new UnprocessableEntityException(sprintf('UnprocessableEntity unknown error : %s', $errorMsg), UnprocessableEntityException::UNKNOWN);
                    }
                    // no break
                default:
                    throw new PsCheckoutException(sprintf('Unknown error : %s', $errorMsg), PsCheckoutException::UNKNOWN);
            }
        }
    }

    /**
     * @param string $merchantId
     *
     * @return ResponseInterface
     *
     * @throws Exception
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
            return $this->client->generateClientToken($this->serializer->serialize($payload, JsonEncoder::FORMAT, true));
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $errorMsg = $this->getErrorMessage($response->getBody()->getContents());
            // Il y a rien dans la doc PayPal pour les erreurs sur IdentityToken
            throw new Exception('Temp exception');
        } catch (Exception $exception) {
            throw $exception;
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
        if (isset($body['details'][0]['issue'])) {
            return $body['details'][0]['issue'];
        }
        if (isset($body['name'])) {
            return $body['name'];
        }
        if (isset($body['error'])) {
            return $body['error'];
        }

        return '';
    }
}
