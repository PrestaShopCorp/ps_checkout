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

/**
 * Retrieve paypal order data from database
 */
class PaypalOrderRepository
{
    /**
     * Return PrestaShop order id for the given Paypal order ID
     *
     * @param int $paypalOrderId Order ID paypal
     *
     * @return int Order ID prestashop
     */
    public function getPsOrderIdByPaypalOrderId($paypalOrderId)
    {
        $orderPayments = new PrestaShopCollection('OrderPayment');
        $orderPayments->where('transaction_id', '=', $paypalOrderId);

        if (true === is_array($orderPayments)) {
            $orderPayment = current($orderPayments);
            $orderReference = $orderPayment->order_reference;
        } else {
            $orderReference = $orderPayments->order_reference;
        }

        $order = new PrestaShopCollection('Order');
        $order->where('reference', '=', $orderReference);

        return $order->id;
    }

    /**
     * Return Paypal order ID for the given PrestaShop order ID
     *
     * @param int $psOrderId Order ID prestashop
     *
     * @return string Order ID Paypal
     */
    public function getPaypalOrderIdByPsOrderId($psOrderId)
    {
        $orderPayment = \OrderPayment::getByOrderId($psOrderId);

        if (true === empty($orderPayment)) {
            return false;
        }

        if (true === is_array($orderPayment)) {
            $orderPayment = current($orderPayment);
        }

        return $orderPayment->transaction_id;
    }
}
