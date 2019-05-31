<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Api\Maasland;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;

/**
 * Handle the refund of a paypal order
 */
class Refund
{
    const REFUND_AMOUNT_EXCEEDED = 'REFUND_AMOUNT_EXCEEDED';
    const REFUND_CAPTURE_CURRENCY_MISMATCH = 'REFUND_CAPTURE_CURRENCY_MISMATCH';
    const REFUND_FAILED_INSUFFICIENT_FUNDS = 'REFUND_FAILED_INSUFFICIENT_FUNDS';
    const REFUND_NOT_ALLOWED = 'REFUND_NOT_ALLOWED';
    const REFUND_TIME_LIMIT_EXCEEDED = 'REFUND_TIME_LIMIT_EXCEEDED';

    public $paypalOrderId = null;
    public $currencyCode = null;
    public $amount = null;

    public function __construct($paypalOrderId, $currencyCode, $amount)
    {
        $this->paypalOrderId = $paypalOrderId;
        $this->currencyCode = $currencyCode;
        $this->amount = $amount;
    }

    /**
     * Refund order
     *
     * @param float $amount value to refund
     * @param string $currenctCode
     *
     * @return bool
     */
    public function refundOrder()
    {
        $refund = (new Maasland(\Context::getContext()->link))->refundOrder($this->getPayload());

        if (isset($refund->statusCode) && $refund->statusCode === 422) {
            return $this->handleCallbackErrors($refund->error);
        }

        return $refund;
    }

    /**
     * Return the capture ID for the paypal order
     *
     * @return string|bool capture ID or false
     */
    public function getCaptureId()
    {
        $paypalOrder = (new PaypalOrder($this->paypalOrderId))->getOrder();

        if (null === $paypalOrder) {
            return false;
        }

        $purchaseUnits = current($paypalOrder['purchase_units']);
        $capture = current($purchaseUnits['payments']['captures']);
        $captureId = $capture['id'];

        if (null === $captureId) {
            return false;
        }

        return $captureId;
    }

    /**
     * Generate the Payload waited by paypal to make a refund
     *
     * @return array payload
     */
    public function getPayload()
    {
        $payload = [
            'orderId' => $this->paypalOrderId,
            'captureId' => $this->getCaptureId(),
            'payee' =>
            [
                'merchant_id' => \Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT')
            ],
            'amount' =>
            [
                'currency_code' => $this->currencyCode,
                'value' => $this->amount
            ],
            'note_to_payer' => 'Refund by '.\Configuration::get('PS_SHOP_NAME')
        ];

        return $payload;
    }

    /**
     * Handle the differents error that can be thrown by paypal
     *
     * @param string $responseErrors Errors returned by paypal(PSL).
     * In case of multiple error, errors are delimited with semicolon
     *
     * @return array List of error meassages
     */
    public function handleCallbackErrors($responseErrors)
    {
        $responseErrors = explode(';', $responseErrors);

        $errors = array(
            'error' => true,
            'messages' => []
        );

        foreach ($responseErrors as $error) {
            switch ($error) {
                case self::REFUND_AMOUNT_EXCEEDED:
                    $errors['messages'][] = 'The refund amount must be less than or equal to the capture amount that has not yet been refunded. Verify the refund amount and try the request again.';
                    break;
                case self::REFUND_CAPTURE_CURRENCY_MISMATCH:
                    $errors['messages'][] = 'Refund must be in the same currency as the capture. Verify the currency of the refund and try the request again.';
                    break;
                case self::REFUND_FAILED_INSUFFICIENT_FUNDS:
                    $errors['messages'][] = 'Capture could not be refunded due to insufficient funds. Verify that either you have sufficient funds in your PayPal account or the bank account that is linked to your PayPal account is verified and has sufficient funds.';
                    break;
                case self::REFUND_NOT_ALLOWED:
                    $errors['messages'][] = 'Full refund refused - partial refund has already been done on this payment. You cannot refund this capture.';
                    break;
                case self::REFUND_TIME_LIMIT_EXCEEDED:
                    $errors['messages'][] = 'You are over the time limit to perform a refund on this capture. The refund cannot be issued at this time.';
                    break;
                default:
                    $errors['messages'][] = sprintf('An error occured during the refund. Cannot process the refund. (%s)', $error);
                    break;
            }
        }

        return $errors;
    }

    /**
     * Cancel the refund in prestashop if the refund cannot be processed from paypal
     *
     * @return boolean
     */
    public function cancelPsRefund()
    {
        // TODO: REVERT ORDER SLIP
    }
}
