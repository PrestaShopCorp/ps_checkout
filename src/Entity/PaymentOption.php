<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Entity;

/**
 * Class PaymentOption
 */
class PaymentOption
{
    private $name;

    private $position;

    private $logo;

    private $enabled;

    private $countries;

    /**
     * PaymentOption constructor.
     *
     * @param string $name
     * @param int $position
     * @param string $logo
     * @param array $countries
     * @param bool $enabled
     */
    public function __construct($name, $position, $countries = array(), $logo = '', $enabled = true)
    {
        $this->name = $name;
        $this->position = $position;
        $this->logo = $logo;
        $this->countries = $countries;
        $this->enabled = $enabled;
    }

    /**
     * Get the payment order as json to save it in the config
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => $this->name,
            'position' => $this->position,
            'logo' => $this->logo,
            'countries' => $this->getCountries(),
            'enabled' => $this->enabled,
        ];
    }

    /**
     * return the countries with their traductions to save in base
     *
     * @return array
     */
    public function getCountries()
    {
        $countries = [];
        foreach ( $this->countries as $isoCode) {
            $countries[] = \Country::getNameById(\Context::getContext()->language->id, \Country::getByIso($isoCode));
        }
        return $countries;
    }
}
