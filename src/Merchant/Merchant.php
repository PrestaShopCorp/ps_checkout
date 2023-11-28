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

namespace PrestaShop\Module\PrestashopCheckout\Merchant;

use PrestaShop\Module\PrestashopCheckout\Country;
use PrestaShop\Module\PrestashopCheckout\Merchant\Exception\ShopException;
use PrestaShop\Module\PrestashopCheckout\Merchant\ValueObject\ShopId;

class Merchant
{
    /** @var int */
    private $id;

    /** @var Country */
    private $country;

    /** @var array */
    private $capabilities;

    /**
     * @param int $id
     * @param Country $country
     * @param array $capabilities
     *
     * @throws ShopException
     */
    public function __construct($id, $country, $capabilities)
    {
        $this->id = new ShopId($id);
        $this->country = $country;
        $this->capabilities = $capabilities;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return $this->capabilities;
    }
}
