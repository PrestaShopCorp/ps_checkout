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

namespace PrestaShop\Module\PrestashopCheckout\Repository;

/**
 * Class OrderRepository used to interact with the DB order table
 */
class OrderRepository
{
    /**
     * Get last 1000 pending checkout orders
     *
     * @param int $shopId
     * @param array $idStates
     *
     * @return array|bool|\mysqli_result|\PDOStatement|resource
     *
     * @throws \PrestaShopDatabaseException
     */
    public function findByStates($shopId, array $idStates)
    {
        $orders = \Db::getInstance()->executeS('
            SELECT o.id_order, o.id_currency, o.current_state, o.total_paid, o.date_add, c.id_customer, c.firstname, c.lastname
            FROM `' . _DB_PREFIX_ . 'orders` o
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (o.id_customer = c.id_customer)
            WHERE o.module = "ps_checkout"
            AND o.id_shop = ' . (int) $shopId . '
            AND o.current_state IN (' . implode(',', array_keys($idStates)) . ')
            ORDER BY o.date_add DESC
            LIMIT 1000
        ');

        if (empty($orders)) {
            return [];
        }

        return $orders;
    }

    /**
     * Returns total orders
     *
     * @param int $shopId
     *
     * @return int
     */
    public function count($shopId)
    {
        return (int) \Db::getInstance()->getValue('
            SELECT COUNT(id_order)
            FROM `' . _DB_PREFIX_ . 'orders`
            WHERE module = "ps_checkout"
            AND id_shop = ' . (int) $shopId
        );
    }
}
