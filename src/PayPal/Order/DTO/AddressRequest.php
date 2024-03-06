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

class AddressRequest
{
    /**
     * @var string
     */
    private $address_line_1;

    /**
     * @var string
     */
    private $address_line_2;

    /**
     * @var string
     */
    private $address_line_3;

    /**
     * @var string
     */
    private $admin_area_1;

    /**
     * @var string
     */
    private $admin_area_2;

    /**
     * @var string
     */
    private $admin_area_3;

    /**
     * @var string
     */
    private $admin_area_4;

    /**
     * @var string
     */
    private $postal_code;

    /**
     * @var string
     */
    private $country_code;

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->address_line_1;
    }

    /**
     * @param string $address_line_1
     *
     * @return self
     */
    public function setAddressLine1($address_line_1)
    {
        $this->address_line_1 = $address_line_1;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->address_line_2;
    }

    /**
     * @param string $address_line_2
     *
     * @return self
     */
    public function setAddressLine2($address_line_2)
    {
        $this->address_line_2 = $address_line_2;

        return $this;
    }

    /**
     * @return string
     */
    public function getAddressLine3()
    {
        return $this->address_line_3;
    }

    /**
     * @param string $address_line_3
     *
     * @return self
     */
    public function setAddressLine3($address_line_3)
    {
        $this->address_line_3 = $address_line_3;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminArea1()
    {
        return $this->admin_area_1;
    }

    /**
     * @param string $admin_area_1
     *
     * @return self
     */
    public function setAdminArea1($admin_area_1)
    {
        $this->admin_area_1 = $admin_area_1;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminArea2()
    {
        return $this->admin_area_2;
    }

    /**
     * @param string $admin_area_2
     *
     * @return self
     */
    public function setAdminArea2($admin_area_2)
    {
        $this->admin_area_2 = $admin_area_2;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminArea3()
    {
        return $this->admin_area_3;
    }

    /**
     * @param string $admin_area_3
     *
     * @return self
     */
    public function setAdminArea3($admin_area_3)
    {
        $this->admin_area_3 = $admin_area_3;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdminArea4()
    {
        return $this->admin_area_4;
    }

    /**
     * @param string $admin_area_4
     *
     * @return self
     */
    public function setAdminArea4($admin_area_4)
    {
        $this->admin_area_4 = $admin_area_4;

        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param string $postal_code
     *
     * @return self
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;

        return $this;
    }

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
     * @return self
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;

        return $this;
    }
}
