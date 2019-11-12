<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Entity;

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
     * Save current object to database (add or update).
     *
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     */
    public function add($autoDate = true, $nullValues = false)
    {
        if (true === $this->alreadyExist()) {
            return false;
        }

        return parent::add($autoDate, $nullValues);
    }

    /**
     * Check if the Prestashop or Paypal Order Id already Exist to prevent duplicate ID entry
     *
     * @return bool
     */
    private function alreadyExist()
    {
        $wherePrestashopIdExist = '1';
        $wherePaypalIdExist = '1';

        if (null !== $this->id_order_prestashop) {
            $wherePrestashopIdExist = 'pom.id_order_prestashop = "' . (int) $this->id_order_prestashop . '"';
        }

        if (null !== $this->id_order_paypal) {
            $wherePaypalIdExist = 'pom.id_order_paypal = "' . pSQL($this->id_order_paypal) . '"';
        }

        $query = 'SELECT id_order_matrice
                FROM `' . _DB_PREFIX_ . 'pscheckout_order_matrice` pom
                WHERE ' . $wherePrestashopIdExist . ' OR ' . $wherePaypalIdExist;

        return (bool) \Db::getInstance()->getValue($query);
    }

    /**
     * Get the Prestashop Order Id from Paypal Order Id
     *
     * @param string $orderPaypal
     *
     * @return int
     */
    public function getOrderPrestashopFromPaypal($orderPaypal)
    {
        $query = 'SELECT id_order_prestashop
                FROM `' . _DB_PREFIX_ . 'pscheckout_order_matrice` pom
                WHERE pom.id_order_paypal = "' . pSQL($orderPaypal) . '"';

        return (int) \Db::getInstance()->getValue($query);
    }

    /**
     * Get the Paypal Order Id from the Prestashop Order Id
     *
     * @param int $orderPrestashop
     *
     * @return string|false
     */
    public function getOrderPaypalFromPrestashop($orderPrestashop)
    {
        $query = 'SELECT id_order_paypal
                FROM `' . _DB_PREFIX_ . 'pscheckout_order_matrice` pom
                WHERE pom.id_order_prestashop = "' . (int) $orderPrestashop . '"';

        return \Db::getInstance()->getValue($query);
    }
}
