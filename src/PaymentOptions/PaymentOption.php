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

namespace PrestaShop\Module\PrestashopCheckout\PaymentOptions;

/**
 * Class PaymentOption
 *
 * We keep the countries ids in the configuration
 */
class PaymentOption
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var int
     */
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
     * @param bool $enabled
     */
    public function __construct($name, $position, $logo = '', $enabled = true)
    {
        $this->name = $name;
        $this->position = $position;
        $this->logo = $logo;
        $this->countries = [];
        $this->enabled = $enabled;
    }

    /**
     * @param array $idCountries
     */
    public function setCountries($idCountries)
    {
        $this->countries = $idCountries;
    }

    /**
     * Use this function to instanciate the countries by isoCode
     *
     * @param array $countriesIsoCodes
     */
    public function setCountriesByIsoCode($countriesIsoCodes)
    {
        $this->countries = [];
        foreach ($countriesIsoCodes as $countryIsoCode) {
            $this->countries[] = \Country::getByIso($countryIsoCode);
        }
    }

    /**
     * Use this function to instanciate the countries by name
     *
     * @param array $countriesNames
     */
    public function setCountriesByName($countriesNames)
    {
        $this->countries = [];
        foreach ($countriesNames as $countryName) {
            $this->countries[] = \Country::getIdByName(null, $countryName);
        }
    }

    /**
     * Get the payment order as json to save it in the config or to send it to front
     *
     * @param bool false $toDisplay is used to send countries as id or as name
     *
     * @return array
     */
    public function toArray($toDisplay = false)
    {
        return [
            'name' => $this->name,
            'position' => $this->position,
            'logo' => $this->logo,
            'countries' => $toDisplay ? $this->getCountriesAsName() : $this->countries,
            'enabled' => $this->enabled,
        ];
    }

    /**
     * return the countries as name to display them
     *
     * @return array
     */
    private function getCountriesAsName()
    {
        $countries = [];
        foreach ($this->countries as $countryId) {
            $countries[] = \Country::getNameById(\Context::getContext()->language->id, $countryId);
        }

        return $countries;
    }
}
