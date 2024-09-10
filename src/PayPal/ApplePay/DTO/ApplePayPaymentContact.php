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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\ApplePay\DTO;

class ApplePayPaymentContact
{
    /**
     * @var string
     */
    private $phoneNumber;
    /**
     * @var string
     */
    private $emailAddress;
    /**
     * @var string
     */
    private $givenName;
    /**
     * @var string
     */
    private $familyName;
    /**
     * @var string
     */
    private $phoneticGivenName;
    /**
     * @var string
     */
    private $phoneticFamilyName;
    /**
     * @var array
     */
    private $addressLines = [];
    /**
     * @var string
     */
    private $subLocality;
    /**
     * @var string
     */
    private $locality;
    /**
     * @var string
     */
    private $postalCode;
    /**
     * @var string
     */
    private $subAdministrativeArea;
    /**
     * @var string
     */
    private $administrativeArea;
    /**
     * @var string
     */
    private $country;
    /**
     * @var string
     */
    private $countryCode;

    /**
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param string $phoneNumber
     *
     * @return $this
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     *
     * @return $this
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * @return string
     */
    public function getGivenName()
    {
        return $this->givenName;
    }

    /**
     * @param string $givenName
     *
     * @return $this
     */
    public function setGivenName($givenName)
    {
        $this->givenName = $givenName;

        return $this;
    }

    /**
     * @return string
     */
    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @param string $familyName
     *
     * @return $this
     */
    public function setFamilyName($familyName)
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneticGivenName()
    {
        return $this->phoneticGivenName;
    }

    /**
     * @param string $phoneticGivenName
     *
     * @return $this
     */
    public function setPhoneticGivenName($phoneticGivenName)
    {
        $this->phoneticGivenName = $phoneticGivenName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPhoneticFamilyName()
    {
        return $this->phoneticFamilyName;
    }

    /**
     * @param string $phoneticFamilyName
     *
     * @return $this
     */
    public function setPhoneticFamilyName($phoneticFamilyName)
    {
        $this->phoneticFamilyName = $phoneticFamilyName;

        return $this;
    }

    /**
     * @return array
     */
    public function getAddressLines()
    {
        return $this->addressLines;
    }

    /**
     * @param array $addressLines
     *
     * @return $this
     */
    public function setAddressLines(array $addressLines)
    {
        $this->addressLines = $addressLines;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubLocality()
    {
        return $this->subLocality;
    }

    /**
     * @param string $subLocality
     *
     * @return $this
     */
    public function setSubLocality($subLocality)
    {
        $this->subLocality = $subLocality;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocality()
    {
        return $this->locality;
    }

    /**
     * @param string $locality
     *
     * @return $this
     */
    public function setLocality($locality)
    {
        $this->locality = $locality;

        return $this;
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
     *
     * @return $this
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubAdministrativeArea()
    {
        return $this->subAdministrativeArea;
    }

    /**
     * @param string $subAdministrativeArea
     *
     * @return $this
     */
    public function setSubAdministrativeArea($subAdministrativeArea)
    {
        $this->subAdministrativeArea = $subAdministrativeArea;

        return $this;
    }

    /**
     * @return string
     */
    public function getAdministrativeArea()
    {
        return $this->administrativeArea;
    }

    /**
     * @param string $administrativeArea
     *
     * @return $this
     */
    public function setAdministrativeArea($administrativeArea)
    {
        $this->administrativeArea = $administrativeArea;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
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
     *
     * @return $this
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'phoneNumber' => $this->phoneNumber,
            'emailAddress' => $this->emailAddress,
            'givenName' => $this->givenName,
            'familyName' => $this->familyName,
            'phoneticGivenName' => $this->phoneticGivenName,
            'phoneticFamilyName' => $this->phoneticFamilyName,
            'addressLines' => $this->addressLines,
            'subLocality' => $this->subLocality,
            'locality' => $this->locality,
            'postalCode' => $this->postalCode,
            'subAdministrativeArea' => $this->subAdministrativeArea,
            'administrativeArea' => $this->administrativeArea,
            'country' => $this->country,
            'countryCode' => $this->countryCode,
        ]);
    }
}
