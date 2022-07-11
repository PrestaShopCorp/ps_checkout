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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order;

class PaypalOrderDataProvider
{
    /**
     * @var array
     */
    private $orderData;

    /**
     * @param array $order
     */
    public function __construct(array $order)
    {
        $this->orderData = $order;
    }

    /**
     * @return string
     */
    public function getTransactionId()
    {
        return isset($this->orderData['purchase_units'][0]['payments']['captures'][0]['id'])
            ? $this->orderData['purchase_units'][0]['payments']['captures'][0]['id']
            : '';
    }

    /**
     * @see https://developer.paypal.com/api/limited-release/orders/v2/#definition-capture_status
     *
     * @return string
     */
    public function getTransactionStatus()
    {
        return isset($this->orderData['purchase_units'][0]['payments']['captures'][0]['status'])
            ? $this->orderData['purchase_units'][0]['payments']['captures'][0]['status']
            : '';
    }

    /**
     * @see https://developer.paypal.com/api/limited-release/orders/v2/#definition-order_status
     *
     * @return string
     */
    public function getOrderStatus()
    {
        return isset($this->orderData['status'])
            ? $this->orderData['status']
            : '';
    }

    /**
     * @return string
     */
    public function getApprovalLink()
    {
        if (!empty($this->orderData['links'])) {
            foreach ($this->orderData['links'] as $link) {
                if ('approve' === $link['rel']) {
                    return $link['href'];
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getPayActionLink()
    {
        if (!empty($this->orderData['links'])) {
            foreach ($this->orderData['links'] as $link) {
                if ('payer-action' === $link['rel']) {
                    return $link['href'];
                }
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getOrderId()
    {
        return isset($this->orderData['id'])
            ? $this->orderData['id']
            : '';
    }

    /**
     * @return string
     */
    public function getTotalAmount()
    {
        return isset($this->orderData['purchase_units'][0]['payments']['captures'][0]['amount']['value'])
            ? $this->orderData['purchase_units'][0]['payments']['captures'][0]['amount']['value']
            : '';
    }

    /**
     * @return string
     */
    public function getCurrencyCode()
    {
        return isset($this->orderData['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'])
            ? $this->orderData['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code']
            : '';
    }
}
