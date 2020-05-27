<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout;

class HostedFieldsErrors
{
    /**
     * @var \Module
     */
    private $module = null;

    /**
     * @param \Module $module
     */
    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Return a list of errors code when a credit card is invalid with
     * the associated message
     *
     * @return string
     */
    public function getHostedFieldsErrors()
    {
        $errors = [
            'INVALID_STRING_LENGTH' => $this->module->l('The card number, the expiry date or the CVV is invalid. Please verify all three and try again.', 'hostedfieldserrors'),
            'INVALID_EXPIRATION_YEAR' => $this->module->l('Expiration year must be between now and 2099', 'hostedfieldserrors'),
            'TRANSACTION_NOT_SUPPORTED' => $this->module->l('This transaction is currently not supported. Please contact customer service or your account manager for more information.', 'hostedfieldserrors'),
            'ORDER_CANNOT_BE_SAVED' => $this->module->l('The option to save an order is only available if the `intent` is AUTHORIZE and `processing_instruction` is ORDER_SAVED_EXPLICITLY. Please change the `intent` to AUTHORIZE, `processing_instruction` to ORDER_SAVED_EXPLICITLY and try again.', 'hostedfieldserrors'),
            'CURRENCY_NOT_SUPPORTED_FOR_COUNTRY' => $this->module->l('That currency is not supported with that payment option. Please try with another currency or contact customer support.', 'hostedfieldserrors'),
            'PAYMENT_INSTRUCTION_NOT_ALLOWED' => $this->module->l('The client is not allowed to use `payment_instruction` object.', 'hostedfieldserrors'),
            'MISSING_SHIPPING_ADDRESS' => $this->module->l('The shipping address is required. Please check that shipping address fields are completed and try again.', 'hostedfieldserrors'),
            'COMPLIANCE_VIOLATION' => $this->module->l('The transaction is declined due to a compliance violation.', 'hostedfieldserrors'),
            'MISSING_REQUIRED_PARAMETER' => $this->module->l('A required parameter is missing. Please check that every personnal and delivery details are filled and try again.', 'hostedfieldserrors'),
            'INVALID_PARAMETER_VALUE' => $this->module->l('The value of a field is invalid.', 'hostedfieldserrors'),
            'PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING' => $this->module->l('Payee account is not setup to be able to process card payments. Please contact PayPal customer support.', 'hostedfieldserrors'),
            'REFERENCE_ID_NOT_FOUND' => $this->module->l('Order does not have a corresponding matching reference_id. Please provide a valid reference_id and try again.', 'hostedfieldserrors'),
            'THREEDS_PARAMS_EMPTY' => $this->module->l('Threeds Secure Parameters are empty.', 'hostedfieldserrors'),
            'NOT_ENABLED_FOR_CARD_PROCESSING' => $this->module->l('The API Caller account is not setup to be able to process card payments. Please contact PayPal customer support.', 'hostedfieldserrors'),
            'CARD_BRAND_NOT_SUPPORTED' => $this->module->l('Processing of this card brand is not supported. Use another type of card.', 'hostedfieldserrors'),
            'MULTIPLE_PURCHASE_UNITS_NOT_SUPPORTED' => $this->module->l('The option to save an order is only available if the order has a single purchase unit.  Please create an order with a single purchase unit and try again.', 'hostedfieldserrors'),
            'ORDER_NOT_SAVED' => $this->module->l('Please save the order by calling v2/orders/{order_id}/save or alternately, If you don\'t intend to save the order, PATCH the order to update the value of `processing_instruction` to NO_INSTRUCTION.', 'hostedfieldserrors'),
            'CONTINGENCY' => $this->module->l('The customer must resolve the contingency before the payment can be processed.', 'hostedfieldserrors'),
            'PAYEE_ACCOUNT_RESTRICTED' => $this->module->l('The merchant account is restricted. Please try later or contact customer service to process your order.', 'hostedfieldserrors'),
            'AMOUNT_CANNOT_BE_SPECIFIED' => $this->module->l('An authorization amount can only be specified if an Order has been saved by calling /v2/checkout/orders/{order_id}/save. Please save the order and try again.', 'hostedfieldserrors'),
            'ACTION_DOES_NOT_MATCH_INTENT' => $this->module->l('The order was created with an intent of `AUTHORIZE`. To complete authorization, use `/v2/checkout/orders/{order_id}/authorize`. Or, alternately create an order with an intent of `CAPTURE`.', 'hostedfieldserrors'),
            'INVALID_PARAMETER_SYNTAX' => $this->module->l('The parameter value does not conform to the expected `YYYY-MM` format.', 'hostedfieldserrors'),
            'ORDER_ALREADY_CAPTURED' => $this->module->l('Order already captured. Only one capture per order is allowed. Please contact customer service to check that your order has successfully been validated.', 'hostedfieldserrors'),
            'ORDER_ALREADY_SAVED' => $this->module->l('Order has previously been saved. Please contact customer service to check that your order has successfully been validated. ', 'hostedfieldserrors'),
            'AMOUNT_NOT_PATCHABLE' => $this->module->l('The amount cannot be updated as the `payer` has chosen and approved a specific financing offer for a given amount. Please Create a new Order with the updated Order amount and have the `payer` approve the new payment terms. ', 'hostedfieldserrors'),
            'CARD_EXPIRED' => $this->module->l('The card is expired. Please use another card.', 'hostedfieldserrors'),
            'TRANSACTION_BLOCKED_BY_PAYEE' => $this->module->l('That transaction has been blocked by Fraud Protection rules. Please try another card or contact customer service to process your order or change their Fraud Protection rules.', 'hostedfieldserrors'),
            'UNSUPPORTED_PAYMENT_INSTRUCTION' => $this->module->l('This payment instruction is supported only when `intent=CAPTURE`. If intent is `AUTHORIZE`, you must provide the payment instruction when the authorization is captured. For details, see Capture authorization.', 'hostedfieldserrors'),
            'ORDER_NOT_APPROVED' => $this->module->l('Payer has not yet approved the Order for payment. Please redirect the payer to the `rel`:`approve` url returned as part of the HATEOAS links within the Create Order call or provide a valid payment_source in the request.', 'hostedfieldserrors'),
            'INVALID_ARRAY_MIN_ITEMS' => $this->module->l('The number of items in an array parameter is too small.', 'hostedfieldserrors'),
            'BILLING_AGREEMENT_CANCELED' => $this->module->l('The requested agreement is already canceled. This error occurs when the agreement for an authorized payment or captured payment is already canceled.', 'hostedfieldserrors'),
            'CARD_TYPE_NOT_SUPPORTED' => $this->module->l('Processing of this card type is not supported. Use another type of card.', 'hostedfieldserrors'),
            'ORDER_PREVIOUSLY_VOIDED' => $this->module->l('This order has been previously voided and cannot be voided again. Verify the order id and try again.', 'hostedfieldserrors'),
            'AUTHORIZATION_CURRENCY_MISMATCH' => $this->module->l('The currency of the authorization should be same as that in which the Order was created and approved by the Payer. Please check the `currency_code` and try again.', 'hostedfieldserrors'),
            'INVALID_ORDER_INTENT' => $this->module->l('Invalid order intent.', 'hostedfieldserrors'),
            'VALIDATION_ERROR' => $this->module->l('Invalid card number. Please verify the card number and try again.', 'hostedfieldserrors'),
            'INVALID_RESOURCE_ID' => $this->module->l('Specified resource ID does not exist. Please check the resource ID and try again.', 'hostedfieldserrors'),
            'PAYMENT_NOT_APPROVED' => $this->module->l('The customer has not approved payment.', 'hostedfieldserrors'),
            'CURRENCY_NOT_SUPPORTED_FOR_CARD_TYPE' => $this->module->l('Currency code not supported for direct card payments using this card type. Please refer https://developer.paypal.com/docs/integration/direct/rest/currency-codes/ for list of supported currency codes.', 'hostedfieldserrors'),
            'AUTHORIZATION_AMOUNT_LIMIT_EXCEEDED' => $this->module->l('Authorization amount exceeds allowable limit. Please provide the authorization amount within allowable limit and try again.', 'hostedfieldserrors'),
            'ORDER_ALREADY_AUTHORIZED' => $this->module->l('Order already authorized. Only one authorization per order is allowed. Please contact customer service to check that your order has successfully been validated.', 'hostedfieldserrors'),
            '3DS_ERROR' => $this->module->l('3D Secure validation failed, please try again or try another payment method.', 'hostedfieldserrors'),
            '3DS_SKIPPED_BY_BUYER' => $this->module->l('You skipped 3D Secure validation, please try again or try another payment method.', 'hostedfieldserrors'),
            '3DS_FAILURE' => $this->module->l('3D Secure processing validation error, please try again or try another payment method.', 'hostedfieldserrors'),
            'UNKNOWN' => $this->module->l('Card processing payment error, please try again or try another payment method.', 'hostedfieldserrors'),
        ];

        return json_encode($errors);
    }
}
