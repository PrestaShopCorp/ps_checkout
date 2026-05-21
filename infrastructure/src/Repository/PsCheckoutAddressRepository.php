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

namespace PsCheckout\Infrastructure\Repository;

use DbQuery;

class PsCheckoutAddressRepository implements PsCheckoutAddressRepositoryInterface
{
    /**
     * @var \Db
     */
    private $db;

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function getAddressIdByChecksumAndCustomer(string $checksum, int $idCustomer): int
    {
        if ($idCustomer === 0) {
            return 0;
        }

        $query = new DbQuery();
        $query->select('pa.id_address');
        $query->from('pscheckout_address', 'pa');
        $query->innerJoin('address', 'a', 'a.id_address = pa.id_address AND a.deleted = 0');
        $query->where('pa.checksum = \'' . pSQL($checksum) . '\'');
        $query->where('pa.id_customer = ' . $idCustomer);

        return (int) $this->db->getValue($query);
    }

    /**
     * {@inheritDoc}
     */
    public function saveAddress(int $idAddress, int $idCustomer, string $checksum): bool
    {
        if ($idAddress === 0 || $idCustomer === 0) {
            return false;
        }

        return (bool) $this->db->insert(
            'pscheckout_address',
            [
                'id_address' => $idAddress,
                'id_customer' => $idCustomer,
                'checksum' => pSQL($checksum),
            ],
            false,
            true,
            \Db::REPLACE
        );
    }
}
