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

class WebHookOrder
{
    /**
     * Tell if refund has been initiated by Paypal or Merchant
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
     * @var string
     */
    private $currencyId;

    /**
     * Paypal Transaction Id from Resource
     *
     * @var string
     */
    private $paypalTransactionId;

    /**
     * @param string $initiateBy
     * @param array $resource
     * @param int $orderId
     */
    public function __construct($initiateBy, $resource, $orderId)
    {
        $this->initiateBy = (string) $initiateBy;
        $this->orderId = (int) $orderId;
        $this->amount = (float) $resource['amount']->value;
        $this->paypalTransactionId = (string) $resource['id'];
        $this->currencyId = (string) \Currency::getIdByIsoCode($resource['amount']->currency_code);
    }

    /**
     * Check if we can refund the order, then
     * refund the order and update the resource status
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function updateOrder()
    {
        $order = new \Order($this->orderId);

        //@todo Quickfix checking if this transaction is already saved
        /** @var \OrderPayment[] $orderPayments */
        $orderPayments = $order->getOrderPaymentCollection();
        foreach ($orderPayments as $orderPayment) {
            if ($orderPayment->transaction_id === $this->paypalTransactionId) {
                throw new PsCheckoutException(sprintf('This PayPal transaction is already saved : %s', $this->paypalTransactionId), PsCheckoutException::PRESTASHOP_REFUND_ALREADY_SAVED);
            }
        }

        $amountAlreadyRefunded = $this->getOrderSlipAmount($order);
        $expectiveTotalAmountToRefund = $amountAlreadyRefunded + $this->amount;

        if ($order->total_paid < $expectiveTotalAmountToRefund) {
            throw new PsCheckoutException(sprintf('Cannot refund more than the order amount %s < %s', $order->total_paid, $expectiveTotalAmountToRefund), PsCheckoutException::PRESTASHOP_REFUND_TOTAL_AMOUNT_REACHED);
        }

        $orderProductList = (array) $order->getProducts();

        $refund = new Refund(true, $this->amount);

        if ($order->total_paid != $this->amount) {
            return (bool) $refund->doPartialRefund($order, $orderProductList, $this->paypalTransactionId);
        }

        return (bool) $refund->doTotalRefund($order, $orderProductList, $this->paypalTransactionId);
    }

    /**
     * Get Order slip already refunded value
     *
     * @param \Order $order
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
}
