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

use PsCheckout\Core\FundingSource\Repository\FundingSourceRepositoryInterface;
use PsCheckout\Core\Settings\Configuration\FundingSourceConfig;

class FundingSourceRepository implements FundingSourceRepositoryInterface
{
    /**
     * @var \Db
     */
    private $db;

    const TABLE_NAME = 'pscheckout_funding_source';

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function getOneBy(array $keyValueCriteria)
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(self::TABLE_NAME);

        foreach ($keyValueCriteria as $key => $value) {
            $query->where("$key = '" . pSQL($value) . "'");
        }

        $result = $this->db->getRow($query);

        return $result ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllForSpecificShop(int $shopId): array
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(self::TABLE_NAME)
            ->where('id_shop = ' . (int) $shopId)
            ->orderBy('position ASC');

        $result = $this->db->executeS($query);

        return $result ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAllActiveForSpecificShop(int $shopId): array
    {
        $query = new \DbQuery();
        $query->select('*')
            ->from(self::TABLE_NAME)
            ->where('active = 1')
            ->where('id_shop = ' . (int) $shopId)
            ->orderBy('position ASC');

        $result = $this->db->executeS($query);

        return $result ?: [];
    }

    /**
     * {@inheritdoc}
     */
    public function upsert(array $data, int $shopId): bool
    {
        if ($this->getOneBy(['name' => $data['name'], 'id_shop' => (int) $shopId])) {
            return (bool) $this->db->update(
                self::TABLE_NAME,
                [
                    'position' => (int) $data['position'],
                    'active' => (int) $data['isEnabled'],
                ],
                '`name` = "' . pSQL($data['name']) . '" AND `id_shop` = ' . (int) $shopId
            );
        }

        return (bool) $this->db->insert(
            self::TABLE_NAME,
            [
                'name' => pSQL($data['name']),
                'position' => (int) $data['position'],
                'active' => (int) $data['isEnabled'],
                'id_shop' => (int) $shopId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function populateWithDefaultValues(int $shopId)
    {
        $positionCounter = 0;

        foreach (FundingSourceConfig::FUNDING_SOURCES as $name) {
            $query = new \DbQuery();
            $query->select('*')
                ->from(self::TABLE_NAME)
                ->where('name = \'' . pSQL($name) . '\'')
                ->where('id_shop = ' . $shopId);

            // Prepare the data to insert or update
            $data = [
                'name' => pSQL($name),
                'active' => (int) !in_array($name, ['google_pay', 'apple_pay']),
                'position' => ++$positionCounter,
                'id_shop' => (int) $shopId,
            ];

            if ($this->db->getRow($query)) {
                \Db::getInstance()->update(self::TABLE_NAME, $data, 'name = \'' . pSQL($name) . '\' AND id_shop = ' . (int) $shopId);
            } else {
                \Db::getInstance()->insert(self::TABLE_NAME, $data);
            }
        }
    }
}
