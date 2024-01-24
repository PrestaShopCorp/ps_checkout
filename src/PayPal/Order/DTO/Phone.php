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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class Phone
{
    /**
     * @var string
     */
    private $country_code;
    /**
     * @var string
     */
    private $national_number;
    /**
     * @var string
     */
    private $extension_number;

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param string $country_code
     *
     * @return void
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
    }

    /**
     * @return string
     */
    public function getNationalNumber()
    {
        return $this->national_number;
    }

    /**
     * @param string $national_number
     *
     * @return void
     */
    public function setNationalNumber($national_number)
    {
        $this->national_number = $national_number;
    }

    /**
     * @return string
     */
    public function getExtensionNumber()
    {
        return $this->extension_number;
    }

    /**
     * @param string $extension_number
     *
     * @return void
     */
    public function setExtensionNumber($extension_number)
    {
        $this->extension_number = $extension_number;
    }
}
