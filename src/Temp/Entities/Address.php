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

namespace PrestaShop\Module\PrestashopCheckout\Temp\Entities;

class Address
{
    /**
     * The first line of the address, such as number and street.
     *
     * @var string
     */
    private $addressLine1;

    /**
     * The second line of the address, for example, a suite or apartment number.
     *
     * @var string
     */
    private $addressLine2;

    /**
     * The highest-level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision.
     *
     * @var string
     */
    private $adminArea1;

    /**
     * A city, town, or village.
     *
     * @var string
     */
    private $adminArea2;

    /**
     * The ISO 3166-1 code that identifies the country or region
     *
     * @var string
     */
    private $countryCode;

    /**
     * The postal code, which is the ZIP code or equivalent.
     *
     * @var string
     */
    private $postalCode;

    /**
     * @see https://developer.paypal.com/docs/api/orders/v2/#definition-address_portable
     *
     * @param string $countryCode
     */
    public function __construct($countryCode)
    {
        $this->setCountryCode($countryCode);
    }

    /**
     * @return string
     */
    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    /**
     * @param string $addressLine1
     */
    public function setAddressLine1($addressLine1)
    {
        $this->addressLine1 = $addressLine1;
    }

    /**
     * @return string
     */
    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    /**
     * @param string $addressLine2
     */
    public function setAddressLine2($addressLine2)
    {
        $this->addressLine2 = $addressLine2;
    }

    /**
     * @return string
     */
    public function getAdminArea1()
    {
        return $this->adminArea1;
    }

    /**
     * @param string $adminArea1
     */
    public function setAdminArea1($adminArea1)
    {
        $this->adminArea1 = $adminArea1;
    }

    /**
     * @return string
     */
    public function getAdminArea2()
    {
        return $this->adminArea2;
    }

    /**
     * @param string $adminArea2
     */
    public function setAdminArea2($adminArea2)
    {
        $this->adminArea2 = $adminArea2;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * @param string $countryCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
    }

    public function toArray()
    {
        $data = [
            'country_code' => $this->countryCode,
        ];

        if (!empty($this->getAddressLine1())) {
            $data['address_line_1'] = $this->getAddressLine1();
        }

        if (!empty($this->getAddressLine2())) {
            $data['address_line_2'] = $this->getAddressLine2();
        }

        if (!empty($this->getAdminArea1())) {
            $data['admin_area_1'] = $this->getAdminArea1();
        }

        if (!empty($this->getAdminArea2())) {
            $data['admin_area_2'] = $this->getAdminArea2();
        }

        if (!empty($this->getPostalCode())) {
            $data['postal_code'] = $this->getPostalCode();
        }

        return array_filter($data);
    }
}
