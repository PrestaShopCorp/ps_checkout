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
 * Class PaymentOption used for model of payment option
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

    /**
     * @var string
     */
    private $logo;

    /**
     * @var bool
     */
    private $enabled;

    /**
     * @var array
     */
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
     * @return array
     */
    public function getCountries()
    {
        return $this->countries;
    }

    /**
     * return the countries as name to display them
     *
     * @return array
     */
    public function getCountriesAsName()
    {
        $countries = [];
        foreach ($this->countries as $countryId) {
            $countries[] = \Country::getNameById(\Context::getContext()->language->id, $countryId);
        }

        return $countries;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }
}
