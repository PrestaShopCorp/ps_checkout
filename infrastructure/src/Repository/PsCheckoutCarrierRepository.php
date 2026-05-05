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

class PsCheckoutCarrierRepository
{
    const TABLE_NAME = 'pscheckout_carrier';

    const TYPE_SHIPPING = 'SHIPPING';

    const TYPE_PICKUP = 'PICKUP';

    /**
     * @var \Db
     */
    private $db;

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    /**
     * Returns the carrier type, or null if the carrier is explicitly disabled for PayPal shipping options.
     * Returns TYPE_SHIPPING by default for carriers not yet configured.
     */
    public function getTypeByCarrierId(int $carrierId): ?string
    {
        $query = new \DbQuery();
        $query->select('p.type, p.disabled')
            ->from('carrier', 'c')
            ->leftJoin(self::TABLE_NAME, 'p', 'c.id_reference = p.id_reference')
            ->where('c.id_carrier = ' . (int) $carrierId);

        $row = $this->db->getRow($query);

        if ($row === false || $row === null) {
            return self::TYPE_SHIPPING;
        }

        if (isset($row['disabled']) && $row['disabled']) {
            return null;
        }

        return $row['type'] ?: self::TYPE_SHIPPING;
    }

    /**
     * @return array<int, array{id_reference: string, name: string, external_module_name: string, type: string|null, disabled: string}>
     */
    public function getAll(): array
    {
        $query = new \DbQuery();
        $query->select('c.id_reference, c.name, c.external_module_name, p.type, p.disabled')
            ->from('carrier', 'c')
            ->leftJoin(self::TABLE_NAME, 'p', 'c.id_reference = p.id_reference')
            ->orderBy('c.id_reference ASC')
            ->where('c.active = 1')
            ->where('c.deleted = 0');

        return $this->db->executeS($query) ?: [];
    }

    public function upsert(int $idReference, string $type, bool $disabled = false): bool
    {
        $query = new \DbQuery();
        $query->select('id_reference')
            ->from(self::TABLE_NAME)
            ->where('id_reference = ' . (int) $idReference);

        if ($this->db->getValue($query)) {
            return (bool) $this->db->update(
                self::TABLE_NAME,
                [
                    'type' => pSQL($type),
                    'disabled' => (int) $disabled,
                ],
                'id_reference = ' . (int) $idReference
            );
        }

        return (bool) $this->db->insert(
            self::TABLE_NAME,
            [
                'id_reference' => (int) $idReference,
                'type' => pSQL($type),
                'disabled' => (int) $disabled,
            ]
        );
    }
}
