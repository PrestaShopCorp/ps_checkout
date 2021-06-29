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

namespace PrestaShop\Module\PrestashopCheckout\FundingSource;

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;

class FundingSourceConfigurationRepository
{
    /**
     * @var \Db
     */
    private $db;

    /**
     * @var PrestaShopContext
     */
    private $context;

    /**
     * @var array In memory cache of funding sources
     */
    private $fundingSources;

    /**
     * @param PrestaShopContext $context
     */
    public function __construct(PrestaShopContext $context)
    {
        $this->db = \Db::getInstance();
        $this->context = $context;
    }

    /**
     * @param string $name
     *
     * @return array|null
     */
    public function get($name)
    {
        $fundingSources = $this->getAll();

        if (null === $fundingSources) {
            return null;
        }

        foreach ($fundingSources as $fundingSource) {
            if ($fundingSource['name'] === $name) {
                return $fundingSource;
            }
        }

        return null;
    }

    /**
     * @return array|null
     */
    public function getAll()
    {
        if (null !== $this->fundingSources) {
            return $this->fundingSources;
        }

        $data = $this->db->executeS('
            SELECT `name`, `active`, `position`
            FROM `' . _DB_PREFIX_ . 'pscheckout_funding_source`
            WHERE `id_shop` = ' . (int) $this->context->getShopId()
        );

        if (!empty($data)) {
            $this->fundingSources = $data;
        }

        return $this->fundingSources;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function save($data)
    {
        if ($this->get($data['name'])) {
            return (bool) $this->db->update(
                'pscheckout_funding_source',
                [
                    'position' => (int) $data['position'],
                    'active' => (int) $data['isEnabled'],
                ],
                '`name` = "' . pSQL($data['name']) . '" AND `id_shop` = ' . (int) $this->context->getShopId()
            );
        }

        return (bool) $this->db->insert(
            'pscheckout_funding_source',
            [
                'name' => pSQL($data['name']),
                'position' => (int) $data['position'],
                'active' => (int) $data['isEnabled'],
                'id_shop' => (int) $this->context->getShopId(),
            ]
        );
    }
}
