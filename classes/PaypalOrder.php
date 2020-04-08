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

/**
 * Allow to instantiate a paypal order
 */
class PaypalOrder
{
    /**
     * @var array
     */
    private $order;

    /**
     * @param string $id
     */
    public function __construct($id)
    {
        $this->loadOrder($id);
    }

    /**
     * Load paypal order data
     */
    private function loadOrder($id)
    {
        $response = (new Order(\Context::getContext()->link))->fetch($id);

        if (false === $response['status']) {
            return;
        }

        $this->setOrder($response['body']);
    }

    /**
     * Getter the intent of an order (CAPTURE or AUTHORIZE)
     *
     * @return string intent of the order
     */
    public function getOrderIntent()
    {
        return $this->order['intent'];
    }

    /**
     * getter for the order
     *
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * setter for order
     *
     * @param array $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return bool
     */
    public function isLoaded()
    {
        return false === empty($this->order);
    }

    /**
     * Returns PayPal Order Id
     *
     * @return string|null
     */
    public function getId()
    {
        return isset($this->order['id']) ? $this->order['id'] : null;
    }

    /**
     * Returns PayPal Order intent
     *
     * @return string|null
     */
    public function getIntent()
    {
        return isset($this->order['intent']) ? $this->order['intent'] : null;
    }

    /**
     * Returns PayPal Order status
     *
     * @return string|null
     */
    public function getStatus()
    {
        return isset($this->order['status']) ? $this->order['status'] : null;
    }

    /**
     * @return array
     */
    public function getTransactions()
    {
        if (empty($this->order['purchase_units'])) {
            return [];
        }

        $transactions = [];

        foreach ($this->order['purchase_units'] as $purchase) {
            if (empty($purchase['payments'])) {
                continue;
            }

            $totalRefunded = 0;

            if (!empty($purchase['payments']['refunds'])) {
                foreach ($purchase['payments']['refunds'] as $refund) {
                    $totalRefunded += $refund['amount']['value'];
                    $transactions[] = [
                        'type' => 'refund',
                        'id' => $refund['id'],
                        'status' => $refund['status'],
                        'amount' => $refund['amount']['value'],
                        'currency' => $refund['amount']['currency_code'],
                        'date' => (new \DateTime($refund['create_time']))->format('Y-m-d H:i:s'),
                        'isRefundable' => false,
                        'maxAmountRefundable' => 0,
                    ];
                }
            }

            if (!empty($purchase['payments']['captures'])) {
                foreach ($purchase['payments']['captures'] as $payment) {
                    $maxAmountRefundable = $payment['amount']['value'] - $totalRefunded;
                    $transactions[] = [
                        'type' => 'capture',
                        'id' => $payment['id'],
                        'status' => $payment['status'],
                        'amount' => $payment['amount']['value'],
                        'currency' => $payment['amount']['currency_code'],
                        'date' => (new \DateTime($payment['create_time']))->format('Y-m-d H:i:s'),
                        'isRefundable' => in_array($payment['status'], ['COMPLETED', 'PARTIALLY_REFUNDED']),
                        'maxAmountRefundable' => $maxAmountRefundable > 0 ? $maxAmountRefundable : 0,
                    ];
                }
            }
        }

        return $transactions;
    }
}
