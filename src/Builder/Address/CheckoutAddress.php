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

namespace PrestaShop\Module\PrestashopCheckout\Builder\Address;

class CheckoutAddress
{
    /**
     * @var string First Name
     */
    public $firstname;
    /**
     * @var string Last Name
     */
    public $lastname;
    /**
     * @var string Street
     */
    public $address1;
    /**
     * @var string Street
     */
    public $address2;
    /**
     * @var string Postal Code
     */
    public $postcode;
    /**
     * @var string City
     */
    public $city;
    /**
     * @var string Country Id
     */
    public $id_country;
    /**
     * @var string Phone
     */
    public $phone;
    /**
     * @var string State Id
     */
    public $id_state;

    /**
     * @param $firstname
     * @param $lastname
     * @param $address1
     * @param $address2
     * @param $postcode
     * @param $city
     * @param $id_country
     * @param $phone
     * @param $id_state
     */
    public function __construct($firstname,
                                $lastname,
                                $address1,
                                $address2,
                                $postcode,
                                $city,
                                $id_country,
                                $phone,
                                $id_state)
    {
        $this->firstname = $this->formatAddressLine($firstname);
        $this->lastname = $this->formatAddressLine($lastname);
        $this->address1 = $this->formatAddressLine($address1);
        $this->address2 = $this->formatAddressLine($address2);
        $this->postcode = $this->formatAddressLine($postcode);
        $this->city = $this->formatAddressLine($city);
        $this->id_country = $this->formatAddressLine($id_country);
        $this->phone = $this->formatAddressLine($phone);
        $this->id_state = $this->formatAddressLine($id_state);
    }

    /**
     * @param $adressLine
     *
     * @return string
     */
    public function formatAddressLine($adressLine)
    {
        return trim($adressLine);
    }
}
