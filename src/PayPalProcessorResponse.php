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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class PayPalProcessorResponse
{
    /**
     * @var string
     */
    private $cardBrand;

    /**
     * @var string
     */
    private $cardType;

    /**
     * @var string
     */
    private $codeAvs;

    /**
     * @var string
     */
    private $codeCvv;

    /**
     * @var string
     */
    private $codeResponse;

    /**
     * @param string $cardBrand
     * @param string $cardType
     * @param string $codeAvs
     * @param string $codeCvv
     * @param string $codeResponse
     */
    public function __construct($cardBrand, $cardType, $codeAvs, $codeCvv, $codeResponse)
    {
        $this->cardBrand = $cardBrand;
        $this->cardType = $cardType;
        $this->codeAvs = $codeAvs;
        $this->codeCvv = $codeCvv;
        $this->codeResponse = $codeResponse;
    }

    /**
     * @throws PsCheckoutException
     */
    public function throwException()
    {
        switch ($this->cardBrand) {
            case 'VISA':
                $message = sprintf(
                    'Card brand: %s Type: %s AVS : %s CVV : %s RESPONSE : %s',
                    $this->cardBrand,
                    $this->cardType,
                    $this->getAvsCommon(),
                    $this->getCvvCommon(),
                    $this->getResponseVisa()
                );
                break;
            case 'MASTERCARD':
                $message = sprintf(
                    'Card brand: %s Type: %s AVS : %s CVV : %s RESPONSE : %s',
                    $this->cardBrand,
                    $this->cardType,
                    $this->getAvsCommon(),
                    $this->getCvvCommon(),
                    $this->getResponseMasterCard()
                );
                break;
            case 'DISCOVER':
                $message = sprintf(
                    'Card brand: %s Type: %s AVS : %s CVV : %s',
                    $this->cardBrand,
                    $this->cardType,
                    $this->getAvsCommon(),
                    $this->getCvvCommon()
                );
                break;
            case 'AMEX':
                $message = sprintf(
                    'Card brand: %s Type: %s AVS : %s CVV : %s',
                    $this->cardBrand,
                    $this->cardType,
                    $this->getAvsAmex(),
                    $this->getCvvCommon()
                );
                break;
            case 'MAESTRO':
                $message = sprintf(
                    'Card brand: %s Type: %s AVS : %s CVV : %s',
                    $this->cardBrand,
                    $this->cardType,
                    $this->getAvsMaestro(),
                    $this->getCvvMaestro()
                );
                break;
            default:
                $message = sprintf(
                    'Card brand: %s Type: %s AVS : %s CVV : %s RESPONSE : %s',
                    $this->cardBrand,
                    $this->cardType,
                    $this->codeAvs,
                    $this->codeCvv,
                    $this->codeResponse
                );
        }

        throw new PsCheckoutException($message, PsCheckoutException::PAYPAL_PAYMENT_CARD_ERROR);
    }

    /**
     * AVS response codes for VISA, MASTERCARD, DISCOVER, AMEX
     *
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
                return 'Error';
        }
    }

    /**
     * AVS response codes for MAESTRO
     *
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
     * AVS response codes for AMEX
     *
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
                return 'Error';
        }
    }

    /**
     * CVV2 response codes for VISA, MASTERCARD, DISCOVER, AMEX
     *
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
                return 'Error';
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
                return 'Error';
        }
    }

    /**
     * Response codes for MASTERCARD
     *
     * @return string
     */
    private function getResponseMasterCard()
    {
        switch ($this->codeResponse) {
            case '01':
                // Obtain new account information before next billing cycle.
                return 'Expired Card Account upgrade, or Portfolio Sale Conversion.';
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
                return 'Error';
        }
    }

    /**
     * Response codes for VISA
     *
     * @return string
     */
    private function getResponseVisa()
    {
        switch ($this->codeResponse) {
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
                return 'Error';
        }
    }
}
