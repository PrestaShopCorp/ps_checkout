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
     *
     * @param string $id PayPal Order identifier
     */
    private function loadOrder($id)
    {
        $response = (new Order(\Context::getContext()->link))->fetch($id);

        if (false === $response['status'] && isset($response['body']['message']) && $response['body']['message'] === 'INVALID_RESOURCE_ID') {
            \Db::getInstance()->delete(\PsCheckoutCart::$definition['table'], 'paypal_order = "' . pSQL($id) . '"');
        }

        if (true === $response['status'] && !empty($response['body'])) {
            $this->setOrder($response['body']);
        }
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
}
