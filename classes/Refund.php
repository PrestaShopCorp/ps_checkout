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

    const REFUND_STATE = 'PS_CHECKOUT_STATE_PARTIAL_REFUND'; // TODO: make a class to get the different state

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $paypalOrderId;

    /**
     * @var string
     */
    private $currencyCode;

    public function __construct($amount, $paypalOrderId = null, $currencyCode = null)
    {
        $this->setAmount($amount);
        $this->setPaypalOrderId($paypalOrderId);
        $this->setCurrencyCode($currencyCode);
    }

    /**
     * Refund order
     *
     * @param float $amount value to refund
     * @param string $currenctCode
     *
     * @return bool
     */
    public function refundPaypalOrder()
    {
        $refund = (new Maasland(\Context::getContext()->link))->refundOrder($this->getPayload());

        if (isset($refund->statusCode) && $refund->statusCode === 422) {
            return $this->handleCallbackErrors($refund->message);
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
        $paypalOrder = (new PaypalOrder($this->getPaypalOrderId()))->getOrder();

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
            'orderId' => $this->getPaypalOrderId(),
            'captureId' => $this->getCaptureId(),
            'payee' => [
                'merchant_id' => \Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT'),
            ],
            'amount' => [
                'currency_code' => $this->getCurrencyCode(),
                'value' => $this->getAmount(),
            ],
            'note_to_payer' => 'Refund by ' . \Configuration::get('PS_SHOP_NAME'),
        ];

        return $payload;
    }

    /**
     * Prepare the datas to fully refund the order
     *
     * @param object $order
     * @param array $orderProductList
     *
     * @return bool
     */
    public function doTotalRefund(\Order $order, $orderProductList)
    {
        foreach ($orderProductList as $key => $value) {
            $orderProductList[$key]['quantity'] = $value['product_quantity'];
            $orderProductList[$key]['unit_price'] = $value['product_price'];
        }

        return $this->refundPrestashopOrder($order, $orderProductList);
    }

    /**
     * Prepare the orderDetailList to do a partial refund on the order
     *
     * @param object $order
     * @param array $orderProductList
     *
     * @return bool
     */
    public function doPartialRefund(\Order $order, $orderProductList)
    {
        $orderDetailList = array();
        $refundPercent = $this->getAmount() / $order->total_products_wt;

        foreach ($orderProductList as $key => $value) {
            $refundAmountDetail = $value['price'] * $refundPercent;
            $quantityFloor = floor($refundAmountDetail / $value['price']);
            $quantityToRefund = ($quantityFloor == 0) ? 1 : $quantityFloor;

            $orderDetailList[$key]['id_order_detail'] = $value['id_order_detail'];
            $orderDetailList[$key]['quantity'] = $quantityToRefund;
            $orderDetailList[$key]['amount'] = $refundAmountDetail;
            $orderDetailList[$key]['unit_price'] = $orderDetailList[$key]['amount'] / $quantityToRefund;
        }

        return $this->refundPrestashopOrder($order, $orderDetailList);
    }

    /**
     * Refund the order
     *
     * @param object $order
     * @param array $orderProductList
     *
     * @return bool
     */
    private function refundPrestashopOrder(\Order $order, $orderProductList)
    {
        $refundVoucher = 0;
        $refundShipping = 0;
        $refundAddTax = true;
        $refundVoucherChoosen = false;

        // If all products have already been refunded, that catch
        try {
            $refundOrder = (bool) \OrderSlip::create(
                $order,
                $orderProductList,
                $refundShipping,
                $refundVoucher,
                $refundVoucherChoosen,
                $refundAddTax
            );
        } catch (\Exception $e) {
            $refundOrder = false;
        }

        if (true !== $refundOrder) {
            return false;
        }

        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = $order->id;

        $orderHistory->changeIdOrderState(
            \Configuration::get(self::REFUND_STATE),
            $order->id
        );

        return $orderHistory->save();
    }

    /**
     * Handle the differents error that can be thrown by paypal
     *
     * @param string $responseErrors Errors returned by paypal(PSL).
     *                               In case of multiple error, errors are delimited with semicolon
     *
     * @return array List of error meassages
     */
    public function handleCallbackErrors($responseErrors)
    {
        $responseErrors = explode(';', $responseErrors);

        $errors = array(
            'error' => true,
            'messages' => [],
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
     * @param int $orderId
     *
     * @return bool
     */
    public function cancelPsRefund($orderId)
    {
        $orderSlip = $this->getOrderSlip($orderId);

        $orderSlipDetails = $this->getOrderSlipDetail($orderSlip->id);

        // foreach order slip detail - revert the quantity refunded in the order detail
        if (!empty($orderSlipDetails)) {
            foreach ($orderSlipDetails as $orderSlipDetail) {
                $orderDetail = new \OrderDetail($orderSlipDetail['id_order_detail']);
                $orderDetail->product_quantity_refunded = $orderDetail->product_quantity_refunded - $orderSlipDetail['product_quantity'];
                $orderDetail->save();

                // delete the order slip detail
                \Db::getInstance()->delete('order_slip_detail', 'id_order_detail = ' . (int) $orderSlipDetail['id_order_detail']);
            }
        }

        return $orderSlip->delete();
    }

    /**
     * Get the last order slip ceated (the one which we want to cancel)
     *
     * @param int $orderId
     *
     * @return object OrderSlip
     */
    private function getOrderSlip($orderId)
    {
        $orderSlip = new \PrestaShopCollection('OrderSlip');
        $orderSlip->where('id_order', '=', $orderId);
        $orderSlip->orderBy('date_add', 'desc');

        return $orderSlip->getFirst();
    }

    /**
     * Retrieve all order slip detail for the given order slip
     *
     * @param int $orderSlipId
     *
     * @return array list of order slip detail associated to the order slip
     */
    private function getOrderSlipDetail($orderSlipId)
    {
        $sql = new \DbQuery();
        $sql->select('id_order_detail, product_quantity');
        $sql->from('order_slip_detail', 'od');
        $sql->where('od.id_order_slip = ' . (int) $orderSlipId);

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * setter for the amount
     *
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * setter for the paypal order id
     *
     * @param string $paypalOrderId
     */
    public function setPaypalOrderId($id)
    {
        $this->paypalOrderId = $id;
    }

    /**
     * setter for the currency code
     *
     * @param string $isoCode
     */
    public function setCurrencyCode($isoCode)
    {
        $this->currencyCode = $isoCode;
    }

    /**
     * getter for the amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * getter for the paypalOrderId
     */
    public function getPaypalOrderId()
    {
        return $this->paypalOrderId;
    }

    /**
     * getter for the currencyCode
     */
    public function getCurrencyCode()
    {
        return $this->currencyCode;
    }
}
