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

namespace PrestaShop\Module\PrestashopCheckout\Database;

class TableManager
{
    const TABLE_ORDER_MATRICE = 'pscheckout_order_matrice';

    /**
     * Create table TABLE_ORDER_MATRICE
     *
     * @return bool
     */
    public function createTable()
    {
        $query = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . self::TABLE_ORDER_MATRICE . '` (
            `id_order_matrice` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_order_prestashop` tinyint(1) unsigned NOT NULL,
            `id_order_paypal` varchar(20) NOT NULL,
            PRIMARY KEY (`id_order_matrice`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;';

        if (\Db::getInstance()->execute($query) == false) {
            return false;
        }

        return true;
    }

    /**
     * Drop table TABLE_ORDER_MATRICE
     *
     * @return bool
     */
    public function dropTable()
    {
        $query = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . self::TABLE_ORDER_MATRICE . '`';

        if (\Db::getInstance()->execute($query) == false) {
            return false;
        }

        return true;
    }
}
