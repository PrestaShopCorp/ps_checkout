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

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

/**
 * Payload object - use to construct some complex payload
 */
class Payload
{
    /**
     * Payload content
     *
     * @var array
     */
    private $items = [];

    /**
     * Setter for items
     * Use array_replace_recursive in order to merge the new
     * content with the previous one.
     *
     * @param array $array
     */
    public function addAndMergeItems(array $array)
    {
        $this->items = array_replace_recursive($this->items, $array);
    }

    /**
     * Get payload content as json
     *
     * @return string
     */
    public function getJson()
    {
        return json_encode($this->items);
    }

    /**
     * Get payload content as array
     *
     * @return array
     */
    public function getArray()
    {
        return $this->items;
    }
}
