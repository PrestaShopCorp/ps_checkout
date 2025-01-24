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

class PayPalProcessorResponse
{
    const PROCESSOR_RESPONSE_CODE = [
        '1000' => 'PARTIAL_AUTHORIZATION',
        '1300' => 'INVALID_DATA_FORMAT',
        '1310' => 'INVALID_AMOUNT',
        '1312' => 'INVALID_TRANSACTION_CARD_ISSUER_ACQUIRER',
        '1317' => 'INVALID_CAPTURE_DATE',
        '1320' => 'INVALID_CURRENCY_CODE',
        '1330' => 'INVALID_ACCOUNT',
        '1335' => 'INVALID_ACCOUNT_RECURRING',
        '1340' => 'INVALID_TERMINAL',
        '1350' => 'INVALID_MERCHANT',
        '1352' => 'RESTRICTED_OR_INACTIVE_ACCOUNT',
        '1360' => 'BAD_PROCESSING_CODE',
        '1370' => 'INVALID_MCC',
        '1380' => 'INVALID_EXPIRATION',
        '1382' => 'INVALID_CARD_VERIFICATION_VALUE',
        '1384' => 'INVALID_LIFE_CYCLE_OF_TRANSACTION',
        '1390' => 'INVALID_ORDER',
        '1393' => 'TRANSACTION_CANNOT_BE_COMPLETED',
        '5100' => 'GENERIC_DECLINE',
        '5110' => 'CVV2_FAILURE',
        '5120' => 'INSUFFICIENT_FUNDS',
        '5130' => 'INVALID_PIN',
        '5135' => 'DECLINED_PIN_TRY_EXCEEDED',
        '5140' => 'CARD_CLOSED',
        '5150' => 'PICKUP_CARD_SPECIAL_CONDITIONS',
        '5160' => 'UNAUTHORIZED_USER',
        '5170' => 'AVS_FAILURE',
        '5180' => 'INVALID_OR_RESTRICTED_CARD',
        '5190' => 'SOFT_AVS',
        '5200' => 'DUPLICATE_TRANSACTION',
        '5210' => 'INVALID_TRANSACTION',
        '5400' => 'EXPIRED_CARD',
        '5500' => 'INCORRECT_PIN_REENTER',
        '5650' => 'DECLINED_SCA_REQUIRED',
        '5700' => 'TRANSACTION_NOT_PERMITTED',
        '5710' => 'TX_ATTEMPTS_EXCEED_LIMIT',
        '5800' => 'REVERSAL_REJECTED',
        '5900' => 'INVALID_ISSUE',
        '5910' => 'ISSUER_NOT_AVAILABLE_NOT_RETRIABLE',
        '5920' => 'ISSUER_NOT_AVAILABLE_RETRIABLE',
        '5930' => 'CARD_NOT_ACTIVATED',
        '5950' => 'DECLINED_DUE_TO_UPDATED_ACCOUNT',
        '6300' => 'ACCOUNT_NOT_ON_FILE',
        '7600' => 'APPROVED_NON_CAPTURE',
        '7700' => 'ERROR_3DS',
        '7710' => 'AUTHENTICATION_FAILED',
        '7800' => 'BIN_ERROR',
        '7900' => 'PIN_ERROR',
        '8000' => 'PROCESSOR_SYSTEM_ERROR',
        '8010' => 'HOST_KEY_ERROR',
        '8020' => 'CONFIGURATION_ERROR',
        '8030' => 'UNSUPPORTED_OPERATION',
        '8100' => 'FATAL_COMMUNICATION_ERROR',
        '8110' => 'RETRIABLE_COMMUNICATION_ERROR',
        '8220' => 'SYSTEM_UNAVAILABLE',
        '9100' => 'DECLINED_PLEASE_RETRY',
        '9500' => 'SUSPECTED_FRAUD',
        '9510' => 'SECURITY_VIOLATION',
        '9520' => 'LOST_OR_STOLEN',
        '9530' => 'HOLD_CALL_CENTER',
        '9540' => 'REFUSED_CARD',
        '9600' => 'UNRECOGNIZED_RESPONSE_CODE',
        '0000' => 'APPROVED',
        '00N7' => 'CVV2_FAILURE_POSSIBLE_RETRY_WITH_CVV',
        '0100' => 'REFERRAL',
        '0390' => 'ACCOUNT_NOT_FOUND',
        '0500' => 'DO_NOT_HONOR',
        '0580' => 'UNAUTHORIZED_TRANSACTION',
        '0800' => 'BAD_RESPONSE_REVERSAL_REQUIRED',
        '0880' => 'CRYPTOGRAPHIC_FAILURE',
        '0890' => 'UNACCEPTABLE_PIN',
        '0960' => 'SYSTEM_MALFUNCTION',
        '0R00' => 'CANCELLED_PAYMENT',
        '10BR' => 'ISSUER_REJECTED',
        'PCNR' => 'CONTINGENCIES_NOT_RESOLVED',
        'PCVV' => 'CVV_FAILURE',
        'PP06' => 'ACCOUNT_CLOSED',
        'PPRN' => 'REATTEMPT_NOT_PERMITTED',
        'PPAD' => 'BILLING_ADDRESS',
        'PPAB' => 'ACCOUNT_BLOCKED_BY_ISSUER',
        'PPAE' => 'AMEX_DISABLED',
        'PPAG' => 'ADULT_GAMING_UNSUPPORTED',
        'PPAI' => 'AMOUNT_INCOMPATIBLE',
        'PPAR' => 'AUTH_RESULT',
        'PPAU' => 'MCC_CODE',
        'PPAV' => 'ARC_AVS',
        'PPAX' => 'AMOUNT_EXCEEDED',
        'PPBG' => 'BAD_GAMING',
        'PPC2' => 'ARC_CVV',
        'PPCE' => 'CE_REGISTRATION_INCOMPLETE',
        'PPCO' => 'COUNTRY',
        'PPCR' => 'CREDIT_ERROR',
        'PPCT' => 'CARD_TYPE_UNSUPPORTED',
        'PPCU' => 'CURRENCY_USED_INVALID',
        'PPD3' => 'SECURE_ERROR_3DS',
        'PPDC' => 'DCC_UNSUPPORTED',
        'PPDI' => 'DINERS_REJECT',
        'PPDV' => 'AUTH_MESSAGE',
        'PPDT' => 'DECLINE_THRESHOLD_BREACH',
        'PPEF' => 'EXPIRED_FUNDING_INSTRUMENT',
        'PPEL' => 'EXCEEDS_FREQUENCY_LIMIT',
        'PPER' => 'INTERNAL_SYSTEM_ERROR',
        'PPEX' => 'EXPIRY_DATE',
        'PPFE' => 'FUNDING_SOURCE_ALREADY_EXISTS',
        'PPFI' => 'INVALID_FUNDING_INSTRUMENT',
        'PPFR' => 'RESTRICTED_FUNDING_INSTRUMENT',
        'PPFV' => 'FIELD_VALIDATION_FAILED',
        'PPGR' => 'GAMING_REFUND_ERROR',
        'PPH1' => 'H1_ERROR',
        'PPIF' => 'IDEMPOTENCY_FAILURE',
        'PPII' => 'INVALID_INPUT_FAILURE',
        'PPIM' => 'ID_MISMATCH',
        'PPIT' => 'INVALID_TRACE_ID',
        'PPLR' => 'LATE_REVERSAL',
        'PPLS' => 'LARGE_STATUS_CODE',
        'PPMB' => 'MISSING_BUSINESS_RULE_OR_DATA',
        'PPMC' => 'BLOCKED_Mastercard',
        'PPMD' => 'PPMD',
        'PPNC' => 'NOT_SUPPORTED_NRC',
        'PPNL' => 'EXCEEDS_NETWORK_FREQUENCY_LIMIT',
        'PPNM' => 'NO_MID_FOUND',
        'PPNT' => 'NETWORK_ERROR',
        'PPPH' => 'NO_PHONE_FOR_DCC_TRANSACTION',
        'PPPI' => 'INVALID_PRODUCT',
        'PPPM' => 'INVALID_PAYMENT_METHOD',
        'PPQC' => 'QUASI_CASH_UNSUPPORTED',
        'PPRE' => 'UNSUPPORT_REFUND_ON_PENDING_BC',
        'PPRF' => 'INVALID_PARENT_TRANSACTION_STATUS',
        'PPRR' => 'MERCHANT_NOT_REGISTERED',
        'PPS0' => 'BANKAUTH_ROW_MISMATCH',
        'PPS1' => 'BANKAUTH_ROW_SETTLED',
        'PPS2' => 'BANKAUTH_ROW_VOIDED',
        'PPS3' => 'BANKAUTH_EXPIRED',
        'PPS4' => 'CURRENCY_MISMATCH',
        'PPS5' => 'CREDITCARD_MISMATCH',
        'PPS6' => 'AMOUNT_MISMATCH',
        'PPSC' => 'ARC_SCORE',
        'PPSD' => 'STATUS_DESCRIPTION',
        'PPSE' => 'AMEX_DENIED',
        'PPTE' => 'VERIFICATION_TOKEN_EXPIRED',
        'PPTF' => 'INVALID_TRACE_REFERENCE',
        'PPTI' => 'INVALID_TRANSACTION_ID',
        'PPTR' => 'VERIFICATION_TOKEN_REVOKED',
        'PPTT' => 'TRANSACTION_TYPE_UNSUPPORTED',
        'PPTV' => 'INVALID_VERIFICATION_TOKEN',
        'PPUA' => 'USER_NOT_AUTHORIZED',
        'PPUC' => 'CURRENCY_CODE_UNSUPPORTED',
        'PPUE' => 'UNSUPPORT_ENTITY',
        'PPUI' => 'UNSUPPORT_INSTALLMENT',
        'PPUP' => 'UNSUPPORT_POS_FLAG',
        'PPUR' => 'UNSUPPORTED_REVERSAL',
        'PPVC' => 'VALIDATE_CURRENCY',
        'PPVE' => 'VALIDATION_ERROR',
        'PPVT' => 'VIRTUAL_TERMINAL_UNSUPPORTED',
    ];

    /**
     * @var string|null
     */
    private $cardBrand;

    /**
     * @var string|null
     */
    private $cardType;

    /**
     * @var string|null
     */
    private $codeAvs;

    /**
     * @var string|null
     */
    private $codeCvv;

    /**
     * @var string|null
     */
    private $paymentAdviceCode;

    /**
     * @var string|null
     */
    private $responseCode;

    /**
     * @param string|null $cardBrand
     * @param string|null $cardType
     * @param string|null $codeAvs
     * @param string|null $codeCvv
     * @param string|null $paymentAdviceCode
     * @param string|null $responseCode
     */
    public function __construct($cardBrand, $cardType, $codeAvs, $codeCvv, $paymentAdviceCode, $responseCode)
    {
        $this->cardBrand = $cardBrand;
        $this->cardType = $cardType;
        $this->codeAvs = $codeAvs;
        $this->codeCvv = $codeCvv;
        $this->paymentAdviceCode = $paymentAdviceCode;
        $this->responseCode = $responseCode;
    }

    /**
     * @return string|null
     */
    public function getCardBrand()
    {
        return $this->cardBrand;
    }

    /**
     * @return string|null
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * @return string|null
     */
    public function getAvsCode()
    {
        return $this->codeAvs;
    }

    /**
     * @return string|null
     */
    public function getAvsCodeDescription()
    {
        switch ($this->cardBrand) {
            case 'VISA':
            case 'MASTERCARD':
            case 'DISCOVER':
            return $this->getAvsCommon();
            case 'AMEX':
                return $this->getAvsAmex();
            case 'MAESTRO':
                return $this->getAvsMaestro();
            default:
                return $this->codeAvs;
        }
    }

    /**
     * @return string|null
     */
    public function getCvvCode()
    {
        return $this->codeCvv;
    }

    /**
     * @return string|null
     */
    public function getCvvCodeDescription()
    {
        switch ($this->cardBrand) {
            case 'VISA':
            case 'MASTERCARD':
            case 'DISCOVER':
            case 'AMEX':
                return $this->getCvvCommon();
            case 'MAESTRO':
                return $this->getCvvMaestro();
            default:
                return $this->codeCvv;
        }
    }

    /**
     * @return string|null
     */
    public function getPaymentAdviceCode()
    {
        return $this->paymentAdviceCode;
    }

    /**
     * @return string|null
     */
    public function getPaymentAdviceCodeDescription()
    {
        switch ($this->cardBrand) {
            case 'VISA':
                return $this->getResponseVisa();
            case 'MASTERCARD':
                return $this->getResponseMasterCard();
            default:
                return $this->paymentAdviceCode;
        }
    }

    /**
     * @return string|null
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @return string|null
     */
    public function getResponseCodeDescription()
    {
        $responseCode = self::PROCESSOR_RESPONSE_CODE;

        return isset($responseCode[$this->responseCode]) ? $responseCode[$this->responseCode] : $this->responseCode;
    }

    /**
     * @return string
     */
    private function getAvsCommon()
    {
        switch ($this->codeAvs) {
            case 'A':
                return 'Address - Address only (no ZIP code)';
            case 'B':
                return 'International "A" - Address only (no ZIP code)';
            case 'C':
                return 'International "N" - The transaction is declined';
            case 'D':
                return 'International "X" - Address and Postal Code';
            case 'E':
                return 'Not allowed for MOTO (Internet/Phone) transactions - The transaction is declined';
            case 'F':
                return 'UK-specific "X" - Address and Postal Code';
            case 'G':
                return 'Global Unavailable';
            case 'I':
                return 'International Unavailable';
            case 'M':
                return 'Address - Address and Postal Code';
            case 'N':
                return 'No - The transaction is declined';
            case 'P':
                return 'Postal (International "Z") - Postal Code only (no Address)';
            case 'R':
                return 'Retry';
            case 'S':
                return 'Service not Supported';
            case 'U':
                return 'Unavailable';
            case 'W':
                return 'Whole ZIP - Nine-digit ZIP code (no Address)';
            case 'X':
                return 'Exact match - Address and nine-digit ZIP code';
            case 'Y':
                return 'Yes - Address and five-digit ZIP';
            case 'Z':
                return 'ZIP - Five-digit ZIP code (no Address)';
            default:
                return 'Error ' . var_export($this->codeAvs, true);
        }
    }

    /**
     * @return string
     */
    private function getAvsMaestro()
    {
        switch ($this->codeAvs) {
            case '0':
                return 'All the address information matched.';
            case '1':
                return 'None of the address information matched - The transaction is declined';
            case '2':
                return 'Part of the address information matched. - Partial';
            case '3':
                return 'The merchant did not provide AVS information. Not processed.';
            case '4':
            case 'U':
                return 'Address not checked, or acquirer had no response. Service not available.';
            default:
                return 'No AVS response was obtained. Default value of field.';
        }
    }

    /**
     * @return string
     */
    private function getAvsAmex()
    {
        switch ($this->codeAvs) {
            case 'A':
                return 'Card holder address only correct.';
            case 'D':
                return 'Card holder name incorrect, postal code matches.';
            case 'E':
                return 'Card holder name incorrect, address and postal code match.';
            case 'F':
                return 'Card holder name incorrect, address matches.';
            case 'K':
                return 'Card holder name matches.';
            case 'L':
                return 'Card holder name and postal code match.';
            case 'M':
                return 'Card holder name, address and postal code match.';
            case 'N':
                return 'No, card holder address and postal code are both incorrect.';
            case 'O':
                return 'Card holder name and address match.';
            case 'R':
                return 'System unavailable; retry.';
            case 'S':
                return 'Service not supported.';
            case 'U':
                return 'Information unavailable.';
            case 'W':
                return 'No, card holder name, address and postal code are all incorrect.';
            case 'Y':
                return 'Yes, card holder address and postal code are both correct.';
            case 'Z':
                return 'Card holder postal code only correct.';
            default:
                return 'Error ' . var_export($this->codeAvs, true);
        }
    }

    /**
     * @return string
     */
    private function getCvvCommon()
    {
        switch ($this->codeCvv) {
            case 'E':
                return 'Error - Unrecognized or Unknown response';
            case 'I':
                return 'Invalid or Null';
            case 'M':
                return 'Match CVV2 / CSC';
            case 'N':
                return 'No match';
            case 'P':
                return 'Not processed';
            case 'S':
                return 'Service not supported';
            case 'U':
                return 'Unknown - Issuer is not certified';
            case 'X':
                return 'No response';
            default:
                return 'Error ' . var_export($this->codeCvv, true);
        }
    }

    /**
     * CVV2 response codes for MAESTRO
     *
     * @return string
     */
    private function getCvvMaestro()
    {
        switch ($this->codeCvv) {
            case '0':
                return 'Matched CVV2';
            case '1':
                return 'No match';
            case '2':
                return 'The merchant has not implemented CVV2 code handling';
            case '3':
                return 'Merchant has indicated that CVV2 is not present on card';
            case '4':
            case 'X':
                return 'Service not available';
            default:
                return $this->codeCvv;
        }
    }

    /**
     * Response codes for MASTERCARD
     *
     * @return string
     */
    private function getResponseMasterCard()
    {
        switch ($this->paymentAdviceCode) {
            case '01':
                // Obtain new account information before next billing cycle.
                return 'Expired Card.';
            case '02':
                // Obtain another type of payment from customer.
                return 'Over Credit Limit, or insufficient funds. Retry the transaction 72 hours later.';
            case '03':
                // Obtain another type of payment from customer.
                return 'Account Closed Fraudulent.';
            case '21':
                // Stop recurring payment requests.
                return 'Card holder has been unsuccessful at canceling recurring payment through merchant.';
            default:
                return $this->paymentAdviceCode;
        }
    }

    /**
     * Response codes for VISA
     *
     * @return string
     */
    private function getResponseVisa()
    {
        switch ($this->paymentAdviceCode) {
            case '02':
                // The merchant must NOT resubmit the same transaction. The merchant can continue the billing process in the subsequent billing period.
                return 'Card holder wants to stop only one specific payment in the recurring payment relationship.';
            case '03':
                // Stop recurring payment requests.
                return 'Card holder wants to stop all recurring payment transactions for a specific merchant.';
            case '21':
                // Stop recurring payment requests.
                return 'All recurring payments have been canceled for the card number requested.';
            default:
                return $this->paymentAdviceCode;
        }
    }
}
