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

class AddressCheckSumRepository
{
    public function addAddressChecksum($id_customer, $alias, $checksum)
    {
        $sql = ' INSERT INTO ' . _DB_PREFIX_ . 'checkout_address
				(`checksum`, `alias`, `id_customer`)
				VALUES ("' . pSQL($checksum) . '","' . pSQL($alias) . '",' . (int) $id_customer . ')';

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }
    }

    public function retrieveAddressChecksum($id_customer)
    {
        return Db::getInstance()->getRow(' SELECT `checksum` FROM ' . _DB_PREFIX_ . 'checkout_address
				WHERE `id_customer` = ' . (int) $id_customer);
    }
}
