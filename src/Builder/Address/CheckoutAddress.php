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
     * @param array $payload
     */
    public function __construct($payload)
    {
        $this->firstname = $this->formatAddressLine($payload['order']['payer']['name']['given_name']);
        $this->lastname = $this->formatAddressLine($payload['order']['payer']['name']['surname']);
        $this->address1 = $this->formatAddressLine($payload['order']['shipping']['address']['address_line_1']);
        $this->address2 = $this->formatAddressLine(false === empty($this->payload['order']['shipping']['address']['address_line_2'])
            ? $this->payload['order']['shipping']['address']['address_line_2'] : '');
        $this->postcode = $payload['order']['shipping']['address']['postal_code'];
        $this->city = $this->formatAddressLine($payload['order']['shipping']['address']['admin_area_2']);
        $this->id_country = $payload['order']['shipping']['address']['country_code'];
        $this->phone = (false === empty($payload['order']['payer']['phone'])
            ? $payload['order']['payer']['phone']['phone_number']['national_number'] : '');
        $this->id_state = $this->formatAddressLine(false === empty($payload['order']['shipping']['address']['admin_area_1'])
            ? $payload['order']['shipping']['address']['admin_area_1'] : '');
    }

    /**
     * @param string $adressLine
     *
     * @return string
     */
    public function formatAddressLine($adressLine)
    {
        $adressLine = trim($adressLine);

        return ucfirst($adressLine);
    }

    public function getField($name)
    {
        return $this->$name;
    }
}
