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

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

use Db;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;
use PsCheckoutCart;
use Psr\SimpleCache\CacheInterface;

class PayPalOrderProvider
{
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var Order
     */
    private $orderApi;

    /**
     * @param CacheInterface $cache
     */
    public function __construct(CacheInterface $cache, Order $orderApi)
    {
        $this->cache = $cache;
        $this->orderApi = $orderApi;
    }

    /**
     * @param string $id PayPal Order Id
     *
     * @return array|false
     */
    public function getById($id)
    {
        if (empty($id)) {
            return false;
        }

        if ($this->cache->has($id)) {
            return $this->cache->get($id);
        }

        $orderPayPal = $this->fetchOrder($id);

        if (!$orderPayPal->isLoaded()) {
            return false;
        }

        $data = $orderPayPal->getOrder();

        $this->cache->set($id, $data);

        return $data;
    }

    /**
     * @param string $id
     *
     * @return PaypalOrder
     */
    public function fetchOrder($id)
    {
        $payPalOrder = new PaypalOrder();

        $response = $this->orderApi->fetch($id);

        if (false === $response['status'] && isset($response['body']['message']) && $response['body']['message'] === 'INVALID_RESOURCE_ID') {
            Db::getInstance()->delete(PsCheckoutCart::$definition['table'], 'paypal_order = "' . pSQL($id) . '"');
        }

        if (true === $response['status'] && !empty($response['body'])) {
            $payPalOrder->setOrder($response['body']);
        }

        return $payPalOrder;
    }
}
