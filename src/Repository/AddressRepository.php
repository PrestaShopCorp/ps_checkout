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

use Db;
use DbQuery;

class AddressRepository
{
    public function addChecksum($id_customer, $alias, $checksum)
    {
        $sql = ' INSERT INTO ' . _DB_PREFIX_ . 'checkout_address
				(`checksum`, `alias`, `id_customer`)
				VALUES ("' . pSQL($checksum) . '","' . pSQL($alias) . '",' . (int)$id_customer . ')';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        return true;
    }

    public function retrieveChecksum($checksum)
    {
        return Db::getInstance()->getRow(' SELECT `checksum` FROM ' . _DB_PREFIX_ . 'checkout_address
				WHERE `checksum` = ' . (int)$checksum);
    }

    public function retrieveCheckoutAdressAlias($checksum)
    {
        $row = Db::getInstance()->getRow(' SELECT `alias` FROM ' . _DB_PREFIX_ . 'checkout_address
				WHERE `checksum` = ' . (int)$checksum);

        return $row['alias'];
    }

    /**
     * Check if address already exist, if yes return the id_address
     *
     * @param string $alias
     * @param int $id_customer
     *
     * @return int
     */
    public function addressAlreadyExist($alias, $id_customer)
    {
        $query = new DbQuery();
        $query->select('id_address');
        $query->from('address');
        $query->where('alias = \'' . pSQL($alias) . '\'');
        $query->where('id_customer = ' . (int)$id_customer);
        $query->where('deleted = 0');

        return (int)Db::getInstance()->getValue($query);
    }

    public function getNewIncstance()
    {
        return "Tokas";
    }
}
