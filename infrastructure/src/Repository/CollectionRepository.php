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

use PrestaShopCollection;

abstract class CollectionRepository implements ReadOnlyCollectionRepositoryInterface
{
    /**
     * @var string
     */
    private $fullyClassifiedClassName;

    public function __construct($fullyClassifiedClassName)
    {
        $this->fullyClassifiedClassName = $fullyClassifiedClassName;
    }

    /**
     * {@inheritDoc}
     */
    public function getOneBy(array $keyValueCriteria, $langId = null)
    {
        $psCollection = new \PrestaShopCollection($this->fullyClassifiedClassName, $langId);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        $first = $psCollection->getFirst();

        return $first ?: null;
    }

    /** {@inheritDoc} */
    public function getAllBy(array $keyValueCriteria, $langId = null): array
    {
        $psCollection = new PrestaShopCollection($this->fullyClassifiedClassName, $langId);

        foreach ($keyValueCriteria as $field => $value) {
            $psCollection = $psCollection->where($field, '=', $value);
        }

        return $psCollection->getResults() ?? [];
    }
}
