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

namespace PrestaShop\Module\PrestashopCheckout;

use Http\Client\Exception\HttpException;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;

class PayPalError
{
    /**
     * @var string
     */
    private $message;

    /**
     * @param string $message
     */
    public function __construct($message)
    {
        $this->message = $message;
    }

    /**
     * @param HttpException|null $previous
     *
     * @throws PayPalException
     */
    public function throwException(HttpException $previous = null)
    {
        switch ($this->message) {
            case 'ACTION_DOES_NOT_MATCH_INTENT':
                throw new PayPalException('Order was created with an intent to CAPTURE, to complete the transaction, call capture payment for order or create an order with an intent of AUTHORIZE.', PayPalException::ACTION_DOES_NOT_MATCH_INTENT, $previous);
            case 'AGREEMENT_ALREADY_CANCELLED':
                throw new PayPalException('The requested agreement is already cancelled, the specified agreement ID cannot be used for this transaction.', PayPalException::AGREEMENT_ALREADY_CANCELLED, $previous);
            case 'AMOUNT_CANNOT_BE_SPECIFIED':
                throw new PayPalException('An authorization amount can only be specified if an order was saved. Save the order and try again.', PayPalException::AMOUNT_CANNOT_BE_SPECIFIED, $previous);
            case 'AMOUNT_MISMATCH':
                throw new PayPalException('The amount specified does not match the breakdown : amount must equal item_total + tax_total + shipping + handling + insurance - shipping_discount - discount.', PayPalException::AMOUNT_MISMATCH, $previous);
            case 'AMOUNT_NOT_PATCHABLE':
                throw new PayPalException('The amount cannot be updated as the payer has chosen and approved a specific financing offer for a given amount. Create an order with the updated order amount and have the payer approve the new payment terms.', PayPalException::AMOUNT_NOT_PATCHABLE, $previous);
            case 'AUTH_CAPTURE_NOT_ENABLED':
                throw new PayPalException('The authorization and capture feature is not enabled for the merchant. Make sure that the recipient of the funds is a verified business account.', PayPalException::AUTH_CAPTURE_NOT_ENABLED, $previous);
            case 'AUTHENTICATION_FAILURE':
                throw new PayPalException('The account validations failed for the user.', PayPalException::AUTHENTICATION_FAILURE, $previous);
            case 'AUTHORIZATION_AMOUNT_EXCEEDED':
                throw new PayPalException('The currency of the authorization must match the currency of the order that the payer created and approved. Check the currency_code and try the request again.', PayPalException::AUTHORIZATION_AMOUNT_EXCEEDED, $previous);
            case 'BILLING_AGREEMENT_NOT_FOUND':
                throw new PayPalException('The requested Billing Agreement token was not found. Verify the token and try the request again.', PayPalException::BILLING_AGREEMENT_NOT_FOUND, $previous);
            case 'CANNOT_BE_NEGATIVE':
                throw new PayPalException('Must be greater than or equal to zero. Try the request again with a different value.', PayPalException::CANNOT_BE_NEGATIVE, $previous);
            case 'CANNOT_BE_ZERO_OR_NEGATIVE':
                throw new PayPalException('Must be greater than zero. Try the request again with a different value.', PayPalException::CANNOT_BE_ZERO_OR_NEGATIVE, $previous);
            case 'CARD_TYPE_NOT_SUPPORTED':
                throw new PayPalException('Processing of this card type is not supported. Use another card type.', PayPalException::CARD_TYPE_NOT_SUPPORTED, $previous);
            case 'INVALID_SECURITY_CODE_LENGTH':
                throw new PayPalException('The security_code length is invalid for the specified card type.', PayPalException::INVALID_SECURITY_CODE_LENGTH, $previous);
            case 'CITY_REQUIRED':
                throw new PayPalException('The specified country requires a city (address.admin_area_2). Specify a city and try the request again.', PayPalException::CITY_REQUIRED, $previous);
            case 'COMPLIANCE_VIOLATION':
                throw new PayPalException('Transaction cannot be processed due to a possible compliance violation. To get more information about the transaction, call Customer Support.', PayPalException::COMPLIANCE_VIOLATION, $previous);
            case 'CONSENT_NEEDED':
                throw new PayPalException('Authorization failed due to insufficient permissions. To continue with this transaction, the payer must provide consent.', PayPalException::CONSENT_NEEDED, $previous);
            case 'CURRENCY_NOT_SUPPORTED_FOR_COUNTRY':
                throw new PayPalException('Currency code not supported for direct card payments in this country.', PayPalException::CURRENCY_NOT_SUPPORTED_FOR_COUNTRY, $previous);
            case 'CURRENCY_NOT_SUPPORTED_FOR_CARD_TYPE':
                throw new PayPalException('The currency code is not supported for direct card payments for this card type.', PayPalException::CURRENCY_NOT_SUPPORTED_FOR_CARD_TYPE, $previous);
            case 'DECIMAL_PRECISION':
                throw new PayPalException('The value of the field should not be more than two decimal places. Verify the number of decimal places and try the request again.', PayPalException::DECIMAL_PRECISION, $previous);
            case 'DOMESTIC_TRANSACTION_REQUIRED':
                throw new PayPalException('This transaction requires the payee and payer to be resident in the same country. To create this payment, a domestic transaction is required.', PayPalException::DOMESTIC_TRANSACTION_REQUIRED, $previous);
            case 'DUPLICATE_INVOICE_ID':
                throw new PayPalException('Duplicate Invoice ID detected. To avoid a duplicate transaction, verify that the invoice ID is unique for each transaction.', PayPalException::DUPLICATE_INVOICE_ID, $previous);
            case 'DUPLICATE_REQUEST_ID':
                throw new PayPalException('The value of PayPal-Request-Id header has already been used. Specify a different value and try the request again.', PayPalException::DUPLICATE_REQUEST_ID, $previous);
            case 'FIELD_NOT_PATCHABLE':
                throw new PayPalException('Field cannot be patched. You cannot update this field.', PayPalException::FIELD_NOT_PATCHABLE, $previous);
            case 'INSTRUMENT_DECLINED':
                throw new PayPalException('The funding instrument presented was either declined by the processor or bank. The specified funding instrument cannot be used for this payment.', PayPalException::INSTRUMENT_DECLINED, $previous);
            case 'INTERNAL_SERVER_ERROR':
                throw new PayPalException('An internal server error has occurred. Retry the request later.', PayPalException::INTERNAL_SERVER_ERROR, $previous);
            case 'INTERNAL_SERVICE_ERROR':
                throw new PayPalException('An internal service error has occurred.', PayPalException::INTERNAL_SERVICE_ERROR, $previous);
            case 'INVALID_ACCOUNT_STATUS':
                throw new PayPalException('Account validations failed for the user. To continue with this transaction, the payer must provide consent.', PayPalException::INVALID_ACCOUNT_STATUS, $previous);
            case 'INVALID_ARRAY_MAX_ITEMS':
                throw new PayPalException('The number of items in an array parameter is too large.', PayPalException::INVALID_ARRAY_MAX_ITEMS, $previous);
            case 'INVALID_ARRAY_MIN_ITEMS':
                throw new PayPalException('The number of items in an array parameter is too small.', PayPalException::INVALID_ARRAY_MIN_ITEMS, $previous);
            case 'INVALID_COUNTRY_CODE':
                throw new PayPalException('Country code is invalid.', PayPalException::INVALID_COUNTRY_CODE, $previous);
            case 'INVALID_CURRENCY_CODE':
                throw new PayPalException('Currency code is invalid or is not currently supported.', PayPalException::INVALID_CURRENCY_CODE, $previous);
            case 'INVALID_JSON_POINTER_FORMAT':
                throw new PayPalException('Path should be a valid JavaScript Object Notation (JSON) Pointer that references a location within the request where the operation is performed. The path is not valid.', PayPalException::INVALID_JSON_POINTER_FORMAT, $previous);
            case 'INVALID_PARAMETER_SYNTAX':
                throw new PayPalException('The value of a field does not conform to the expected format. Verify that the pattern is supported and try the request again.', PayPalException::INVALID_PARAMETER_SYNTAX, $previous);
            case 'INVALID_PARAMETER_VALUE':
                throw new PayPalException('The value of a field is invalid. Verify the parameter value and try the request again.', PayPalException::INVALID_PARAMETER_VALUE, $previous);
            case 'INVALID_PARAMETER':
                throw new PayPalException('Cannot be specified as part of the request. Check that the API supports this parameter and try the request again.', PayPalException::INVALID_PARAMETER, $previous);
            case 'INVALID_PATCH_OPERATION':
                throw new PayPalException('Request is not well-formed, syntactically incorrect, or violates schema. The operation cannot be honored. You cannot add a property that is already present. Instead, use replace. You cannot remove a property that is not present. Instead, use add. You cannot replace a property that is not present. Instead, use add.', PayPalException::INVALID_PATCH_OPERATION, $previous);
            case 'INVALID_PAYER_ID':
                throw new PayPalException('The payer ID is not valid. Verify the payer ID and try the request again.', PayPalException::INVALID_PAYER_ID, $previous);
            case 'INVALID_RESOURCE_ID':
                throw new PayPalException('Specified resource ID does not exist. Verify the resource ID and try the request again.', PayPalException::INVALID_RESOURCE_ID, $previous);
            case 'INVALID_STRING_LENGTH':
                throw new PayPalException('The value of a field is either too short or too long. Verify the minimum and maximum values and try the request again.', PayPalException::INVALID_STRING_LENGTH, $previous);
            case 'ITEM_TOTAL_MISMATCH':
                throw new PayPalException('Verify the corresponding values and try the request again. The item total should equal the sum of (unit_amount * quantity) across all items for a purchase_unit.', PayPalException::ITEM_TOTAL_MISMATCH, $previous);
            case 'ITEM_TOTAL_REQUIRED':
                throw new PayPalException('If item details are specified (items.unit_amount and items.quantity) corresponding amount.breakdown.item_total is required. The amount.breakdown.item_total value was not found.', PayPalException::ITEM_TOTAL_REQUIRED, $previous);
            case 'MAX_AUTHORIZATION_COUNT_EXCEEDED':
                throw new PayPalException('The maximum number of authorizations that are allowed for the order was reached. To increase your limit, contact Customer Support.', PayPalException::MAX_AUTHORIZATION_COUNT_EXCEEDED, $previous);
            case 'MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED':
                throw new PayPalException('You have exceeded the maximum number of payment attempts. To review the maximum number of payment attempts allowed and retry this transaction, call Customer Support.', PayPalException::MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED, $previous);
            case 'MAX_VALUE_EXCEEDED':
                throw new PayPalException('Should be less than or equal to 9999999.99 ; try the request again with a different value.', PayPalException::MAX_VALUE_EXCEEDED, $previous);
            case 'MISSING_REQUIRED_PARAMETER':
                throw new PayPalException('A required field or parameter is missing. Verify that you have specified all required parameters and try the request again.', PayPalException::MISSING_REQUIRED_PARAMETER, $previous);
            case 'MISSING_SHIPPING_ADDRESS':
                throw new PayPalException('The shipping address is required when shipping_preference=SET_PROVIDED_ADDRESS. Verify that you have provided the shipping address and try the request again.', PayPalException::MISSING_SHIPPING_ADDRESS, $previous);
            case 'MULTI_CURRENCY_ORDER':
                throw new PayPalException('Multiple differing values of currency_code are not supported. The entire order request must have the same currency code.', PayPalException::MULTI_CURRENCY_ORDER, $previous);
            case 'MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED':
                throw new PayPalException('Multiple shipping addresses are not supported. Try the request again with the same shipping_address.', PayPalException::MULTIPLE_SHIPPING_ADDRESS_NOT_SUPPORTED, $previous);
            case 'MULTIPLE_SHIPPING_OPTION_SELECTED':
                throw new PayPalException('Only one shipping.option can be set to selected = true.', PayPalException::MULTIPLE_SHIPPING_OPTION_SELECTED, $previous);
            case 'INVALID_PICKUP_ADDRESS':
                throw new PayPalException('Invalid shipping address. If the \'shipping_option.type\' is set as \'PICKUP\' then the \'shipping_detail.name.full_name\' should start with \'S2S\' meaning Ship To Store. Example: \'S2S My Store\'.', PayPalException::INVALID_PICKUP_ADDRESS, $previous);
            case 'NOT_AUTHORIZED':
                throw new PayPalException('Authorization failed due to insufficient permissions. To check that your application has sufficient permissions, log in to the PayPal Developer Portal.', PayPalException::NOT_AUTHORIZED, $previous);
            case 'NOT_ENABLED_FOR_CARD_PROCESSING':
                throw new PayPalException('The request fails. The API Caller account is not setup to be able to process card payments. Please contact PayPal customer support.', PayPalException::NOT_ENABLED_FOR_CARD_PROCESSING, $previous);
            case 'NOT_PATCHABLE':
                throw new PayPalException('Cannot be patched. You cannot update this field.', PayPalException::NOT_PATCHABLE, $previous);
            case 'NOT_SUPPORTED':
                throw new PayPalException('This field is not currently supported. Specify only supported parameters and try the request again.', PayPalException::NOT_SUPPORTED, $previous);
            case 'ORDER_ALREADY_AUTHORIZED':
                throw new PayPalException('Order already authorized. If intent=AUTHORIZE only one authorization per order is allowed. The order was already authorized and you can create only one authorization for an order.', PayPalException::ORDER_ALREADY_AUTHORIZED, $previous);
            case 'ORDER_ALREADY_CAPTURED':
                throw new PayPalException('Order already captured. If intent=CAPTURE only one capture per order is allowed. The order was already captured and you can capture only one payment for an order.', PayPalException::ORDER_ALREADY_CAPTURED, $previous);
            case 'ORDER_ALREADY_COMPLETED':
                throw new PayPalException('The order cannot be patched after it is completed.', PayPalException::ORDER_ALREADY_COMPLETED, $previous);
            case 'ORDER_CANNOT_BE_SAVED':
                throw new PayPalException('The option to save an order is only available if the intent is AUTHORIZE and the processing_instruction is ORDER_SAVED_EXPLICITLY. Change the intent to AUTHORIZE and the processing_instruction to ORDER_SAVED_EXPLICITLY and try the request again.', PayPalException::ORDER_CANNOT_BE_SAVED, $previous);
            case 'ORDER_COMPLETED_OR_VOIDED':
                throw new PayPalException('Order is voided or completed and hence cannot be authorized.', PayPalException::ORDER_COMPLETED_OR_VOIDED, $previous);
            case 'ORDER_EXPIRED':
                throw new PayPalException('Order is expired and hence cannot be authorized. Please contact Customer Support if you need to increase your order validity period.', PayPalException::ORDER_EXPIRED, $previous);
            case 'ORDER_NOT_APPROVED':
                throw new PayPalException('Payer has not yet approved the Order for payment. The payer has not yet approved payment for the order. Redirect the payer to the rel:approve URL that was returned in the HATEOAS links in the create order response or provide a valid payment_source in the request.', PayPalException::ORDER_NOT_APPROVED, $previous);
            case 'ORDER_NOT_SAVED':
                throw new PayPalException('Please save the order or alternately, If you do not intend to save the order, PATCH the order to update the value of processing_instruction to NO_INSTRUCTION.', PayPalException::ORDER_NOT_SAVED, $previous);
            case 'ORDER_PREVIOUSLY_VOIDED':
                throw new PayPalException('This order has been previously voided and cannot be voided again. Verify the order id and try again.', PayPalException::ORDER_PREVIOUSLY_VOIDED, $previous);
            case 'PARAMETER_VALUE_NOT_SUPPORTED':
                throw new PayPalException('The value specified for this field is not currently supported. The specified parameter value is not supported.', PayPalException::PARAMETER_VALUE_NOT_SUPPORTED, $previous);
            case 'PATCH_PATH_REQUIRED':
                throw new PayPalException('Specify a path for the field for which the operation needs to be performed. To complete the operation for this field, specify a path for the field.', PayPalException::PATCH_PATH_REQUIRED, $previous);
            case 'PATCH_VALUE_REQUIRED':
                throw new PayPalException('Please specify a value to for the field that is being patched.', PayPalException::PATCH_VALUE_REQUIRED, $previous);
            case 'PAYEE_ACCOUNT_INVALID':
                throw new PayPalException('Payee account specified is invalid. Please check the payee.email_address or payee.merchant_id specified and try again. Ensure that either payee.merchant_id or payee.email_address is specified. Specify either payee.merchant_id or payee.email_address.', PayPalException::PAYEE_ACCOUNT_INVALID, $previous);
            case 'PAYEE_ACCOUNT_LOCKED_OR_CLOSED':
                throw new PayPalException('Payee account is locked or closed. To get more information about the status of the account, call Customer Support.', PayPalException::PAYEE_ACCOUNT_LOCKED_OR_CLOSED, $previous);
            case 'PAYEE_ACCOUNT_RESTRICTED':
                throw new PayPalException('The merchant account is restricted. To get more information about the status of the account, call Customer Support.', PayPalException::PAYEE_ACCOUNT_RESTRICTED, $previous);
            case 'PAYEE_BLOCKED_TRANSACTION':
                throw new PayPalException('The fraud settings for this seller are such that this payment cannot be executed. Verify the fraud settings. Then, retry the transaction.', PayPalException::PAYEE_BLOCKED_TRANSACTION, $previous);
            case 'PAYER_ACCOUNT_LOCKED_OR_CLOSED':
                throw new PayPalException('Payer account is locked or closed. To get more information about the status of the account, call Customer Support.', PayPalException::PAYER_ACCOUNT_LOCKED_OR_CLOSED, $previous);
            case 'PAYER_ACCOUNT_RESTRICTED':
                throw new PayPalException('Payer account is restricted. To get more information about the status of the account, call Customer Support.', PayPalException::PAYER_ACCOUNT_RESTRICTED, $previous);
            case 'PAYER_CANNOT_PAY':
                throw new PayPalException('Payer cannot pay for this transaction. Please contact the payer to find other ways to pay for this transaction.', PayPalException::PAYER_CANNOT_PAY, $previous);
            case 'PAYER_CONSENT_REQUIRED':
                throw new PayPalException('The payer has not provided appropriate consent to proceed with this transaction. To proceed with the transaction, you must get payer consent.', PayPalException::PAYER_CONSENT_REQUIRED, $previous);
            case 'PAYER_COUNTRY_NOT_SUPPORTED':
                throw new PayPalException('Payer Country is not supported. The Payer country is not supported. Redirect the payer to select another funding source.', PayPalException::PAYER_COUNTRY_NOT_SUPPORTED, $previous);
            case 'PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING':
                throw new PayPalException('The API Caller account is not setup to be able to process card payments. Please contact PayPal customer support.', PayPalException::PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING, $previous);
            case 'PAYMENT_INSTRUCTION_REQUIRED':
                throw new PayPalException('You must provide the payment instruction when you capture an authorized payment for intent=AUTHORIZE. For details, see Capture authorization. For intent=CAPTURE, send the payment instruction when you create the order.', PayPalException::PAYMENT_INSTRUCTION_REQUIRED, $previous);
            case 'PERMISSION_DENIED':
                throw new PayPalException('You do not have permission to access or perform operations on this resource. If you make API calls on behalf of a merchant or payee, ensure that you have been granted appropriate permissions to continue with this request.', PayPalException::PERMISSION_DENIED, $previous);
            case 'POSTAL_CODE_REQUIRED':
                throw new PayPalException('The specified country requires a postal code. Specify a postal code and try the request again.', PayPalException::POSTAL_CODE_REQUIRED, $previous);
            case 'PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH':
                throw new PayPalException('The amount provided in the preferred shipping option should match the amount provided in amount breakdown.', PayPalException::PREFERRED_SHIPPING_OPTION_AMOUNT_MISMATCH, $previous);
            case 'REDIRECT_PAYER_FOR_ALTERNATE_FUNDING':
                throw new PayPalException('Transaction failed. Redirect the payer to select another funding source.', PayPalException::REDIRECT_PAYER_FOR_ALTERNATE_FUNDING, $previous);
            case 'REFERENCE_ID_NOT_FOUND':
                throw new PayPalException('Filter expression value is incorrect. Check the value of the reference_id and try the request again.', PayPalException::REFERENCE_ID_NOT_FOUND, $previous);
            case 'REFERENCE_ID_REQUIRED':
                throw new PayPalException('\'reference_id\' is required for each \'purchase_unit\' if multiple \'purchase_unit\' are provided. Provide a unique value for reference_id for each purchase_unit and try the request again.', PayPalException::REFERENCE_ID_REQUIRED, $previous);
            case 'DUPLICATE_REFERENCE_ID':
                throw new PayPalException('reference_id must be unique if multiple purchase_unit are provided. Provide a unique value for reference_id for each purchase_unit and try the request again.', PayPalException::DUPLICATE_REFERENCE_ID, $previous);
            case 'SHIPPING_ADDRESS_INVALID':
                throw new PayPalException('Provided shipping address is invalid.', PayPalException::SHIPPING_ADDRESS_INVALID, $previous);
            case 'SHIPPING_OPTION_NOT_SELECTED':
                throw new PayPalException('At least one of the shipping.option values must be selected = true.', PayPalException::SHIPPING_OPTION_NOT_SELECTED, $previous);
            case 'SHIPPING_OPTIONS_NOT_SUPPORTED':
                throw new PayPalException('Shipping options are not supported when application_context.shipping_preference is set as NO_SHIPPING or SET_PROVIDED_ADDRESS.', PayPalException::SHIPPING_OPTIONS_NOT_SUPPORTED, $previous);
            case 'TAX_TOTAL_MISMATCH':
                throw new PayPalException('Should equal sum of (tax * quantity) across all items for a given purchase_unit. The tax total must equal the sum of (tax * quantity) across all items for a purchase_unit.', PayPalException::TAX_TOTAL_MISMATCH, $previous);
            case 'TAX_TOTAL_REQUIRED':
                throw new PayPalException('If item details are specified (items.tax_total and items.quantity), the corresponding amount.breakdown.tax_total is required. The amount.breakdown.tax_total is a required field.', PayPalException::TAX_TOTAL_REQUIRED, $previous);
            case 'TRANSACTION_AMOUNT_EXCEEDS_MONTHLY_MAX_LIMIT':
                throw new PayPalException('The transaction amount exceeds monthly maximum limit. To review the monthly transaction limits and retry this transaction, call Customer Support.', PayPalException::TRANSACTION_AMOUNT_EXCEEDS_MONTHLY_MAX_LIMIT, $previous);
            case 'TRANSACTION_BLOCKED_BY_PAYEE':
                throw new PayPalException('The transaction was blocked by the payee’s Fraud Protection settings.', PayPalException::TRANSACTION_BLOCKED_BY_PAYEE, $previous);
            case 'TRANSACTION_LIMIT_EXCEEDED':
                throw new PayPalException('Total payment amount exceeded transaction limit. To review the transaction limit and retry this transaction, call Customer Support.', PayPalException::TRANSACTION_LIMIT_EXCEEDED, $previous);
            case 'TRANSACTION_RECEIVING_LIMIT_EXCEEDED':
                throw new PayPalException('The transaction exceeds the payee\'s receiving limit. To review the transaction limit and retry this transaction, call Customer Support.', PayPalException::TRANSACTION_RECEIVING_LIMIT_EXCEEDED, $previous);
            case 'TRANSACTION_REFUSED':
                throw new PayPalException('The transaction was refused. Verify the transaction and try the request again.', PayPalException::TRANSACTION_REFUSED, $previous);
            case 'UNSUPPORTED_INTENT':
                throw new PayPalException('intent=AUTHORIZE is not supported for multiple purchase units. Only intent=CAPTURE is supported.', PayPalException::UNSUPPORTED_INTENT, $previous);
            case 'UNSUPPORTED_PATCH_PARAMETER_VALUE':
                throw new PayPalException('The value specified for this field is not currently supported. Try the request again with a different value.', PayPalException::UNSUPPORTED_PATCH_PARAMETER_VALUE, $previous);
            case 'UNSUPPORTED_PAYMENT_INSTRUCTION':
                throw new PayPalException('Only supported when the intent=CAPTURE. If intent is AUTHORIZE, you must provide a payment_instruction when you capture payment for the authorization.', PayPalException::UNSUPPORTED_PAYMENT_INSTRUCTION, $previous);
            case 'PAYEE_ACCOUNT_NOT_SUPPORTED':
                throw new PayPalException('Payee does not have an account with PayPal. Your current setup requires the \'payee\' to have a verified account with PayPal before you can process transactions on their behalf.', PayPalException::PAYEE_ACCOUNT_NOT_SUPPORTED, $previous);
            case 'PAYEE_ACCOUNT_NOT_VERIFIED':
                throw new PayPalException('Payee has not verified their account with PayPal. Your current setup requires the \'payee\' to have an account with PayPal before you can process transactions on their behalf.', PayPalException::PAYEE_ACCOUNT_NOT_VERIFIED, $previous);
            case 'PAYEE_NOT_CONSENTED':
                throw new PayPalException('Payee does not have appropriate consent to allow the API caller to process this type of transaction on their behalf. Your current setup requires the \'payee\' to provide a consent before this transaction can be processed successfully.', PayPalException::PAYEE_NOT_CONSENTED, $previous);
            case 'AUTH_CAPTURE_CURRENCY_MISMATCH':
                throw new PayPalException('Currency of capture must be the same as currency of authorization. Verify the currency of the capture and try the request again.', PayPalException::AUTH_CAPTURE_CURRENCY_MISMATCH, $previous);
            case 'AUTHORIZATION_ALREADY_CAPTURED':
                throw new PayPalException('Authorization has already been captured. If final_capture is set to to true, additional captures are not possible against the authorization.', PayPalException::AUTHORIZATION_ALREADY_CAPTURED, $previous);
            case 'AUTHORIZATION_DENIED':
                throw new PayPalException('A denied authorization cannot be captured. You cannot capture a denied authorization.', PayPalException::AUTHORIZATION_DENIED, $previous);
            case 'AUTHORIZATION_EXPIRED':
                throw new PayPalException('An expired authorization cannot be captured. You cannot capture an expired authorization.', PayPalException::AUTHORIZATION_EXPIRED, $previous);
            case 'AUTHORIZATION_VOIDED':
                throw new PayPalException('A voided authorization cannot be captured or reauthorized. You cannot capture or reauthorize a voided authorization.', PayPalException::AUTHORIZATION_VOIDED, $previous);
            case 'CANNOT_BE_VOIDED':
                throw new PayPalException('A reauthorization cannot be voided. Please void the original parent authorization. You cannot void a reauthorized payment. You must void the original parent authorized payment.', PayPalException::CANNOT_BE_VOIDED, $previous);
            case 'REFUND_NOT_PERMITTED_DUE_TO_CHARGEBACK':
                throw new PayPalException('Refunds not allowed on this capture due to a chargeback on the card or bank. Please contact the payee to resolve the chargeback.', PayPalException::REFUND_NOT_PERMITTED_DUE_TO_CHARGEBACK, $previous);
            case 'CAPTURE_DISPUTED_PARTIAL_REFUND_NOT_ALLOWED':
                throw new PayPalException('Refund for an amount less than the remaining transaction amount cannot be processed at this time because of an open dispute on the capture. Please visit the PayPal Resolution Center to view the details.', PayPalException::CAPTURE_DISPUTED_PARTIAL_REFUND_NOT_ALLOWED, $previous);
            case 'CAPTURE_FULLY_REFUNDED':
                throw new PayPalException('The capture has already been fully refunded. You cannot capture additional refunds against this capture.', PayPalException::CAPTURE_FULLY_REFUNDED, $previous);
            case 'DECIMALS_NOT_SUPPORTED':
                throw new PayPalException('Currency does not support decimals.', PayPalException::DECIMALS_NOT_SUPPORTED, $previous);
            case 'INVALID_PAYEE_ACCOUNT':
                throw new PayPalException('Payee account is invalid. Verify the payee account information and try the request again.', PayPalException::INVALID_PAYEE_ACCOUNT, $previous);
            case 'INVALID_PLATFORM_FEES_AMOUNT':
                throw new PayPalException('The platform_fees amount cannot be greater than the capture amount. Verify the platform_fees amount and try the request again.', PayPalException::INVALID_PLATFORM_FEES_AMOUNT, $previous);
            case 'INVALID_STRING_MAX_LENGTH':
                throw new PayPalException('The value of a field is too long. The parameter string is too long.', PayPalException::INVALID_STRING_MAX_LENGTH, $previous);
            case 'MAX_CAPTURE_AMOUNT_EXCEEDED':
                throw new PayPalException('Capture amount exceeds allowable limit. Please contact customer service or your account manager to request the change to your overage limit. The default overage limit is 115%, which allows the sum of all captures to be up to 115% of the authorization amount. Specify a different amount and try the request again. Alternately, contact Customer Support to increase your limits.', PayPalException::MAX_CAPTURE_AMOUNT_EXCEEDED, $previous);
            case 'MAX_CAPTURE_COUNT_EXCEEDED':
                throw new PayPalException('Maximum number of allowable captures has been reached. No additional captures are possible for this authorization. Please contact customer service or your account manager to change the number of captures that be made for a given authorization. You cannot make additional captures.', PayPalException::MAX_CAPTURE_COUNT_EXCEEDED, $previous);
            case 'MAX_NUMBER_OF_REFUNDS_EXCEEDED':
                throw new PayPalException('You have exceeded the number of refunds that can be processed per capture. Please contact customer support or your account manager to review the number of refunds that can be processed per capture.', PayPalException::MAX_NUMBER_OF_REFUNDS_EXCEEDED, $previous);
            case 'PARTIAL_REFUND_NOT_ALLOWED':
                throw new PayPalException('You cannot do a refund for an amount less than the original capture amount. Specify an amount equal to the capture amount or omit the amount object from the request. Then, try the request again.', PayPalException::PARTIAL_REFUND_NOT_ALLOWED, $previous);
            case 'PENDING_CAPTURE':
                throw new PayPalException('Cannot initiate a refund as the capture is pending. Capture is typically pending when the payer has funded the transaction by using an e-check or bank account.', PayPalException::PENDING_CAPTURE, $previous);
            case 'PERMISSION_NOT_GRANTED':
                throw new PayPalException('Payee of the authorization has not granted permission to perform capture on the authorization. To make API calls on behalf of a merchant, ensure that you have sufficient permissions to capture the authorization.', PayPalException::PERMISSION_NOT_GRANTED, $previous);
            case 'PREVIOUSLY_CAPTURED':
                throw new PayPalException('Authorization has been previously captured and hence cannot be voided. This authorized payment was already captured. You cannot capture it again.', PayPalException::PREVIOUSLY_CAPTURED, $previous);
            case 'PREVIOUSLY_VOIDED':
                throw new PayPalException('Authorization has been previously voided and hence cannot be voided again. This authorized payment was already voided. You cannot void it again.', PayPalException::PREVIOUSLY_VOIDED, $previous);
            case 'REFUND_AMOUNT_EXCEEDED':
                throw new PayPalException('The refund amount must be less than or equal to the capture amount that has not yet been refunded. Verify the refund amount and try the request again.', PayPalException::REFUND_AMOUNT_EXCEEDED, $previous);
            case 'REFUND_CAPTURE_CURRENCY_MISMATCH':
                throw new PayPalException('Refund must be in the same currency as the capture. Verify the currency of the refund and try the request again.', PayPalException::REFUND_CAPTURE_CURRENCY_MISMATCH, $previous);
            case 'REFUND_FAILED_INSUFFICIENT_FUNDS':
                throw new PayPalException('Capture could not be refunded due to insufficient funds. Verify that either you have sufficient funds in your PayPal account or the bank account that is linked to your PayPal account is verified and has sufficient funds.', PayPalException::REFUND_FAILED_INSUFFICIENT_FUNDS, $previous);
            case 'REFUND_NOT_ALLOWED':
                throw new PayPalException('Full refund refused - partial refund has already been done on this payment. You cannot refund this capture.', PayPalException::REFUND_NOT_ALLOWED, $previous);
            case 'REFUND_TIME_LIMIT_EXCEEDED':
                throw new PayPalException('You are over the time limit to perform a refund on this capture. The refund cannot be issued at this time.', PayPalException::REFUND_TIME_LIMIT_EXCEEDED, $previous);
            case 'NO_EXTERNAL_FUNDING_DETAILS_FOUND':
                throw new PayPalException('External funding details not found.', PayPalException::NO_EXTERNAL_FUNDING_DETAILS_FOUND, $previous);
            case 'PAYMENT_DENIED':
                throw new PayPalException('Payment denied.', PayPalException::PAYMENT_DENIED, $previous);
            case 'CARD_BRAND_NOT_SUPPORTED':
                throw new PayPalException('Processing of this card brand is not supported. Use another type of card.', PayPalException::CARD_BRAND_NOT_SUPPORTED, $previous);
            case 'RESOURCE_NOT_FOUND':
                throw new PayPalException('The specified resource does not exist.', PayPalException::RESOURCE_NOT_FOUND, $previous);
            case 'PAYMENT_SOURCE_CANNOT_BE_USED':
                throw new PayPalException('The provided payment source cannot be used to pay for the order. Please try again with a different payment source by creating a new order.', PayPalException::PAYMENT_SOURCE_CANNOT_BE_USED, $previous);
            case 'PAYPAL_REQUEST_ID_REQUIRED':
                throw new PayPalException('A PayPal-Request-Id is required if you are trying to process payment for an Order. Please specify a PayPal-Request-Id or Create the Order without a payment_source specified.', PayPalException::PAYPAL_REQUEST_ID_REQUIRED, $previous);
            case 'MALFORMED_REQUEST_JSON':
                throw new PayPalException('The request JSON is not well formed.', PayPalException::MALFORMED_REQUEST_JSON, $previous);
            case 'PERMISSION_DENIED_FOR_DONATION_ITEMS':
                throw new PayPalException('The API caller or payee have not been granted appropriate permissions to send items.category as DONATION. Speak to your account manager if you want to process these type of items.', PayPalException::PERMISSION_DENIED_FOR_DONATION_ITEMS, $previous);
            case 'MALFORMED_REQUEST':
                throw new PayPalException('You\'ve sent a request that our server could not understand.', PayPalException::MALFORMED_REQUEST, $previous);
            case 'BILLING_ADDRESS_INVALID':
                throw new PayPalException('Provided billing address is invalid.', PayPalException::BILLING_ADDRESS_INVALID, $previous);
            case 'CARD_EXPIRED':
                throw new PayPalException('The payment card provided is expired.', PayPalException::CARD_EXPIRED, $previous);
            case 'DONATION_ITEMS_NOT_SUPPORTED':
                throw new PayPalException('If "purchase_unit" has "DONATION" as the "items.category" then the Order can at most have one purchase_unit. Multiple purchase_units are not supported if either of them have at least one items with category as "DONATION".', PayPalException::DONATION_ITEMS_NOT_SUPPORTED, $previous);
            case 'MISSING_PICKUP_ADDRESS':
                throw new PayPalException('A pickup address (shipping.address) is required for the provided shipping.type. Possible error location: /purchase_units/0/shipping/type', PayPalException::MISSING_PICKUP_ADDRESS, $previous);
            case 'MULTIPLE_ITEM_CATEGORIES':
                throw new PayPalException('For a given purchase unit, the items.category could be either PHYSICAL_GOODS and\/or DIGITAL_GOODS or just DONATION. items.category as DONATION cannot be combined with items with either PHYSICAL_GOODS or DIGITAL_GOODS.', PayPalException::MULTIPLE_ITEM_CATEGORIES, $previous);
            case 'MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED':
                throw new PayPalException('Different shipping.type are not supported across purchase units.', PayPalException::MULTIPLE_SHIPPING_TYPE_NOT_SUPPORTED, $previous);
            case 'PAYMENT_SOURCE_DECLINED_BY_PROCESSOR':
                throw new PayPalException('The provided payment source is declined by the processor. Please try again with a different payment source by creating a new order.', PayPalException::PAYMENT_SOURCE_DECLINED_BY_PROCESSOR, $previous);
            case 'PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED':
                throw new PayPalException('The provided payment source is declined by the processor. Please try again with a different payment source by creating a new order.', PayPalException::PAYMENT_SOURCE_INFO_CANNOT_BE_VERIFIED, $previous);
            case 'SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT':
                throw new PayPalException('The API Caller account is not setup to be able to support a shipping.type=PICKUP_IN_PERSON. This feature is only supported for PayPal Commerce Platform for Platforms and Marketplaces.', PayPalException::SHIPPING_TYPE_NOT_SUPPORTED_FOR_CLIENT, $previous);
            case 'UNSUPPORTED_SHIPPING_TYPE':
                throw new PayPalException('The provided shipping.type is only supported for application_context.shipping_preference=SET_PROVIDED_ADDRESS or NO_SHIPPING. Possible error location: /purchase_units/0/shipping/type.', PayPalException::UNSUPPORTED_SHIPPING_TYPE, $previous);
            case 'CARD_CLOSED':
                throw new PayPalException('The card is closed with the issuer.', PayPalException::CARD_CLOSED, $previous);
            case 'SAVE_ORDER_NOT_SUPPORTED':
                throw new PayPalException('The API caller account is setup in a way that does not allow it to be used for saving the order. This functionality is not available for PayPal Commerce Platform for Platforms & Marketplaces.', PayPalException::SAVE_ORDER_NOT_SUPPORTED, $previous);
            case 'PUI_DUPLICATE_ORDER':
                throw new PayPalException('A Pay Upon Invoice (Rechnungskauf) order with the same payload has already been successfully processed in the last few seconds. To process a new order, please try again in a few seconds.', PayPalException::PUI_DUPLICATE_ORDER, $previous);
            case 'CANNOT_PROCESS_REFUNDS':
                throw new PayPalException('We can\'t process any refund at this moment due to technical reasons. Please try again later.', PayPalException::CANNOT_PROCESS_REFUNDS, $previous);
            case 'INVALID_REFUND_AMOUNT':
                throw new PayPalException('The refund amount is invalid. Please check the refund amount and try again.', PayPalException::INVALID_REFUND_AMOUNT, $previous);
            default:
                throw new PayPalException($this->message, PayPalException::UNKNOWN, $previous);
        }
    }
}
