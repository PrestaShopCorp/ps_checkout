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

/**
 * Makes a matrice between Prestashop Order and Paypal Order
 */
class OrderMatrice extends \ObjectModel
{
    public $id_order_prestashop;
    public $id_order_paypal;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table' => 'pscheckout_order_matrice',
        'primary' => 'id_order_matrice',
        'fields' => [
            'id_order_prestashop' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'id_order_paypal' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],
        ],
    ];

    /**
     * Get PrestaShop Orders associated to PayPal Order
     *
     * @param string $orderPayPal
     *
     * @return array
     */
    public function getPrestaShopOrdersByPayPalOrder($orderPayPal)
    {
        $orderIds = \Db::getInstance()->executeS('
            SELECT `id_order_prestashop`
            FROM `' . _DB_PREFIX_ . 'pscheckout_order_matrice`
            WHERE `id_order_paypal` = "' . pSQL($orderPayPal) . '"'
        );

        if (empty($orderIds)) {
            return [];
        }

        return $orderIds;
    }

    /**
     * Get the Paypal Order Id from the Prestashop Order Id
     *
     * @param int $orderId
     *
     * @return string|false
     */
    public function getOrderPaypalFromPrestashop($orderId)
    {
        return \Db::getInstance()->getValue('
            SELECT `id_order_paypal`
            FROM `' . _DB_PREFIX_ . 'pscheckout_order_matrice`
            WHERE `id_order_prestashop` = ' . (int) $orderId
        );
    }

    /**
     * Check if this order has multiple entries associated due to bug before 1.2.11
     *
     * @param int $orderId
     *
     * @return bool
     */
    public static function hasInconsistencies($orderId)
    {
        // Before 1.2.11 id_order_prestashop field was limited to 255
        if ((int) $orderId !== 255) {
            return false;
        }

        // If more than one order found, there are inconsistencies for this order
        $total = (int) \Db::getInstance()->getValue('
            SELECT COUNT(*)
            FROM `' . _DB_PREFIX_ . 'pscheckout_order_matrice`
            WHERE `id_order_prestashop` = ' . (int) $orderId
        );

        return $total > 1;
    }
}
