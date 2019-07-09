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

class WebHookOrder
{
    const REFUND_STATE = 'PS_CHECKOUT_STATE_PARTIAL_REFUND';
    /**
     * Tell if refund is initiate by Paypal or Merchant
     *
     * @var string
     */
    private $initiateBy;

    /**
     * Amount value from Paypal
     *
     * @var float
     */
    private $amount;

    /**
     * Prestashop order id from Paypal Order ID
     *
     * @var int
     */
    private $orderId;

    /**
     * Currency ID from ISO Code
     *
     * @var int
     */
    private $currencyId;

    /**
     * __construct
     *
     * @param string $initiateBy
     * @param array $resource
     * @param int $orderId
     */
    public function __construct($initiateBy, $resource, $orderId)
    {
        $paypalOrderRepository = new PaypalOrderRepository();

        $this->initiateBy = (string) $initiateBy;
        $this->orderId = (int) $orderId;
        $this->amount = (float) $resource['amount']->value;
        $this->currencyId = (string) \Currency::getIdByIsoCode($resource['amount']->currency_code);
    }

    /**
     * Check if we can refund the order
     * Refund the order and update thresourcee status
     *
     * @return bool
     */
    public function updateOrder()
    {
        $order = new \Order($this->orderId);
        $amountAlreadyRefunded = $this->getOrderSlipAmount($order);
        $expectiveTotalAmountToRefund = $amountAlreadyRefunded + $this->amount;

        if ($order->total_paid < $expectiveTotalAmountToRefund) {
            (new WebHookNock())->setHeader(
                406,
                array(
                    'order' => 'Can\'t refund more than the order amount',
                )
            );

            return false;
        }

        $orderProductList = (array) $order->getProducts();

        if ((float) $order->total_paid !== $this->amount) {
            return (bool) $this->doPartialRefund($order, $orderProductList);
        }

        return (bool) $this->doTotalRefund($order, $orderProductList);
    }

    /**
     * Get Order slip already refunded value
     *
     * @param object $order
     *
     * @return float
     */
    private function getOrderSlipAmount(\Order $order)
    {
        $orderSlips = \OrderSlip::getOrdersSlip($order->id_customer, $this->orderId);
        $value = 0;

        foreach ($orderSlips as $slip) {
            $slipDetails = \OrderSlip::getOrdersSlipDetail($slip['id_order_slip']);
            foreach ($slipDetails as $detail) {
                $value += $detail['total_price_tax_incl'];
            }
        }

        return (float) $value;
    }

    /**
     * Prepare the datas to fully refund the order
     *
     * @param object $order
     * @param array $orderProductList
     *
     * @return bool
     */
    private function doTotalRefund(\Order $order, $orderProductList)
    {
        $shippingCost = $order->total_shipping;

        foreach ($orderProductList as $key => $value) {
            $orderProductList[$key]['quantity'] = $value['product_quantity'];
            $orderProductList[$key]['unit_price'] = $value['product_price'];
        }

        return $this->refundOrder($order, $orderProductList);
    }

    /**
     * Prepare the orderDetailList to do a partial refund on the order
     *
     * @param object $order
     * @param array $orderProductList
     *
     * @return bool
     */
    private function doPartialRefund(\Order $order, $orderProductList)
    {
        $orderDetailList = array();
        $refundPercent = $this->amount / $order->total_products_wt;

        foreach ($orderProductList as $key => $value) {
            $refundAmountDetail = (float) $value['total_price_tax_incl'] * $refundPercent;
            $quantityFloor = (float) $refundAmountDetail / $value['unit_price_tax_incl'];
            $quantityToRefund = ($quantityFloor < 1) ? 1 : floor($quantityFloor);

            $orderDetailList[$key]['id_order_detail'] = $value['id_order_detail'];
            $orderDetailList[$key]['quantity'] = $quantityToRefund;
            $orderDetailList[$key]['amount'] = $refundAmountDetail;
            $orderDetailList[$key]['unit_price'] = $orderDetailList[$key]['amount'] / $quantityToRefund;
        }

        return $this->refundOrder($order, $orderDetailList);
    }

    /**
     * Refund the order
     *
     * @param object $order
     * @param array $orderProductList
     *
     * @return bool
     */
    private function refundOrder(\Order $order, $orderProductList)
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
            (new WebHookNock())->setHeader(
                422,
                array(
                    'order' => 'unable to refund',
                )
            );

            return false;
        }

        $order = new \OrderHistory();
        $order->id_order = $this->orderId;

        $order->changeIdOrderState(
            \Configuration::get(self::REFUND_STATE),
            $this->orderId
        );

        if (true !== $order->save()) {
            (new WebHookNock())->setHeader(
                500,
                array(
                    'fatal' => 'unable to change the order state',
                )
            );

            return false;
        }

        return true;
    }
}
