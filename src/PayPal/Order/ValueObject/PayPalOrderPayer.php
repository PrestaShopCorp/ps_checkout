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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject;

class PayPalOrderPayer
{
    /**
     * @var string
     */
    private $firstName;
    /**
     * @var string
     */
    private $lastName;
    /**
     * @var string
     */
    private $email;
    /**
     * @var string
     */
    private $address1;
    /**
     * @var string
     */
    private $address2;
    /**
     * @var string
     */
    private $adminArea1;
    /**
     * @var string
     */
    private $adminArea2;
    /**
     * @var string
     */
    private $countryCode;
    /**
     * @var string
     */
    private $postalCode;

    /**
     * @param string $firstName
     * @param string $lastName
     * @param string $email
     * @param string $address1
     * @param string $address2
     * @param string $adminArea1
     * @param string $adminArea2
     * @param string $countryCode
     * @param string $postalCode
     */
    public function __construct($firstName, $lastName, $email, $address1, $address2, $adminArea1, $adminArea2, $countryCode, $postalCode)
    {
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = $email;
        $this->address1 = $address1;
        $this->address2 = $address2;
        $this->adminArea1 = $adminArea1;
        $this->adminArea2 = $adminArea2;
        $this->countryCode = $countryCode;
        $this->postalCode = $postalCode;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * @param string $address1
     */
    public function setAddress1($address1)
    {
        $this->address1 = $address1;
    }

    /**
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * @param string $address2
     */
    public function setAddress2($address2)
    {
        $this->address2 = $address2;
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

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'name' => [
                'given_name' => $this->firstName,
                'surname' => $this->lastName,
            ],
            'email_address' => $this->email,
            'address' => [
                'address_line_1' => $this->address1,
                'address_line_2' => $this->address2,
                'admin_area_1' => $this->adminArea1,
                'admin_area_2' => $this->adminArea2,
                'country_code' => $this->countryCode,
                'postal_code' => $this->postalCode,
            ],
        ];
    }
}
