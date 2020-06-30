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

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

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
    const REFUND_STATE = 'PS_CHECKOUT_STATE_PARTIAL_REFUND';

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $paypalOrderId;

    /**
     * @var bool
     */
    private $refundFromWebhook;

    /**
     * @var string
     */
    private $currencyCode;

    /**
     * @param bool $refundFromWebhook
     * @param float $amount
     * @param string $paypalOrderId
     * @param string $currencyCode
     */
    public function __construct($refundFromWebhook, $amount, $paypalOrderId = null, $currencyCode = null)
    {
        $this->setAmount($amount);
        $this->setPaypalOrderId($paypalOrderId);
        $this->setCurrencyCode($currencyCode);
        $this->setRefundFromWebhook($refundFromWebhook);
    }

    /**
     * Refund order
     *
     * @return array api response
     */
    public function refundPaypalOrder()
    {
        // API call here
        $payload = $this->getPayload();

        //@todo Quick fix before refactoring
        if (empty($payload)) {
            return [
                'error' => true,
                'messages' => [
                    'Unable to retrieve payload or unable to retrieve capture id of this order.',
                ],
            ];
        }

        // API call here
        $response = (new Order(\Context::getContext()->link))->refund($payload);

        if (422 === $response['httpCode']) {
            return $this->handleCallbackErrors($response['body']['message']);
        }

        return $response;
    }

    /**
     * Return the capture ID for the paypal order
     *
     * @return string|bool capture ID or false
     */
    public function getCaptureId()
    {
        // API call here
        $response = (new PaypalOrder($this->getPaypalOrderId()))->getOrder();

        if (false === $response['status']) {
            return false;
        }

        //@todo Quick fix before refactoring
        $purchaseUnits = isset($response['purchase_units']) ? current($response['purchase_units']) : null;
        //@todo Quick fix before refactoring
        $capture = isset($purchaseUnits['payments']['captures']) ? current($purchaseUnits['payments']['captures']) : null;
        $captureId = isset($capture['id']) ? $capture['id'] : null;

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
        // API call here
        $captureId = $this->getCaptureId();

        //@todo Quick fix before refactoring
        if (false === $captureId) {
            return [];
        }

        $payload = [
            'orderId' => $this->getPaypalOrderId(),
            'captureId' => $captureId,
            'payee' => [
                'merchant_id' => (new PaypalAccountRepository())->getMerchantId(),
            ],
            'amount' => [
                'currency_code' => $this->getCurrencyCode(),
                'value' => $this->getAmount(),
            ],
            'note_to_payer' => 'Refund by '
                . \Configuration::get(
                'PS_SHOP_NAME',
                    null,
                    null,
                    (int) \Context::getContext()->shop->id
                ),
        ];

        return $payload;
    }

    /**
     * Prepare the data to fully refund the order
     *
     * @param \Order $order
     * @param array $orderProductList
     * @param string $transactionId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function doTotalRefund(\Order $order, $orderProductList, $transactionId)
    {
        foreach ($orderProductList as $key => $value) {
            $orderProductList[$key]['quantity'] = $value['product_quantity'];
            $orderProductList[$key]['unit_price'] = $value['unit_price_tax_incl'];
        }

        $refundOrderStateId = (int) \Configuration::get('PS_OS_REFUND');

        return $this->refundPrestashopOrder($order, $orderProductList, $refundOrderStateId, $transactionId);
    }

    /**
     * Prepare the orderDetailList to do a partial refund on the order
     *
     * @param \Order $order
     * @param array $orderProductList
     * @param string $transactionId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function doPartialRefund(\Order $order, $orderProductList, $transactionId)
    {
        $orderDetailList = [];
        $refundPercent = $this->getAmount() / $order->total_products_wt;

        foreach ($orderProductList as $key => $value) {
            if ($this->refundProductLimitReached($value)) {
                throw new PsCheckoutException('Can\'t refund more products than possible', PsCheckoutException::PRESTASHOP_REFUND_TOTAL_AMOUNT_REACHED);
            }

            $refundAmountDetail = (float) $value['total_price_tax_incl'] * $refundPercent;
            $quantityFloor = (float) $refundAmountDetail / $value['unit_price_tax_incl'];
            $quantityToRefund = ($quantityFloor < 1) ? 1 : floor($quantityFloor);

            $orderDetailList[$key]['id_order_detail'] = $value['id_order_detail'];
            $orderDetailList[$key]['quantity'] = $quantityToRefund;
            $orderDetailList[$key]['amount'] = $refundAmountDetail;
            $orderDetailList[$key]['unit_price'] = $orderDetailList[$key]['amount'] / $quantityToRefund;
        }

        return $this->refundPrestashopOrder(
            $order,
            $orderDetailList,
            (int) \Configuration::getGlobalValue(self::REFUND_STATE),
            $transactionId
        );
    }

    /**
     * Check if the limit has been reached. Must set header HTTP if reached
     *
     * @param array $productOrder
     *
     * @return bool
     */
    private function refundProductLimitReached(array $productOrder)
    {
        if ($productOrder['product_quantity'] > $productOrder['product_quantity_refunded']) {
            return false;
        }

        return true;
    }

    /**
     * Refund the order
     *
     * @param \Order $order
     * @param array $orderProductList
     * @param int $orderStateId
     * @param string $transactionId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    private function refundPrestashopOrder(\Order $order, $orderProductList, $orderStateId, $transactionId)
    {
        $refundVoucher = 0;
        $refundShipping = 0;
        $refundAddTax = false;
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

        // @todo Add a new negative OrderPayment is wrong !
        $this->addOrderPayment($order, $transactionId);

        // If refund from Prestashop, do not change Order History
        if (false === $this->refundFromWebhook) {
            return true;
        }

        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = $order->id;
        $orderHistory->changeIdOrderState(
            $orderStateId,
            $order->id
        );

        return $orderHistory->addWithemail();
    }

    /**
     * Add an order payment in order to keep a history of transactions
     *
     * @param \Order $order
     * @param string $paypalTransactionId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function addOrderPayment(\Order $order, $paypalTransactionId)
    {
        //@todo Quickfix to prevent duplicate OrderPayment
        /** @var \OrderPayment[] $orderPayments */
        $orderPayments = $order->getOrderPaymentCollection();
        foreach ($orderPayments as $orderPayment) {
            if ($orderPayment->transaction_id === $paypalTransactionId) {
                throw new PsCheckoutException(sprintf('This PayPal transaction is already saved : %s', $orderPayment->transaction_id), PsCheckoutException::PRESTASHOP_REFUND_ALREADY_SAVED);
            }
        }

        //@todo this is not correct to add a negative OrderPayment, it cause a Warning in Order page in BO
        //Maybe its done to save paypalTransactionId
        return $order->addOrderPayment(
            -$this->getAmount(),
            'PrestaShop Checkout',
            $paypalTransactionId
        );
    }

    /**
     * Handle different errors that can be thrown by paypal
     *
     * @param string $responseErrors Errors returned by paypal(PSL).
     *                               In case of multiple error, errors are delimited with semicolon
     *
     * @return array List of error messages
     */
    public function handleCallbackErrors($responseErrors)
    {
        $responseErrors = explode(';', $responseErrors);

        $errors = [
            'error' => true,
            'messages' => [],
        ];

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
     *
     * @throws \PrestaShopDatabaseException
     * @throws \PrestaShopException
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
     * Get the last order slip created (the one which we want to cancel)
     *
     * @param int $orderId
     *
     * @return object OrderSlip
     *
     * @throws \PrestaShopException
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
     *
     * @throws \PrestaShopDatabaseException
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
     * @param string $id
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
     * setter to set where the refund comes from
     *
     * @param bool $refundFromWebhook
     */
    public function setRefundFromWebhook($refundFromWebhook)
    {
        $this->refundFromWebhook = $refundFromWebhook;
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
