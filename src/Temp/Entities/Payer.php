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

class Payer
{
    /** @var Address */
    private $address;

    /** @var string */
    private $birthDate;

    /** @var string */
    private $emailAddress;

    /** @var PayerName */
    private $name;

    /** @var string */
    private $payerId;

    /** @var PhoneWithType */
    private $phone;

    /** @var PayerTaxInfo */
    private $taxInfo;

    /**
     * @link https://developer.paypal.com/docs/api/orders/v2/#definition-payer
     *
     * @param string $emailAddress
     * @param string $payerId
     */
    public function __construct($emailAddress, $payerId)
    {
        $this->setEmailAddress($emailAddress);
        $this->setPayerId($payerId);
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param Address $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param string $birthDate
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
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
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return PayerName
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param PayerName $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getPayerId()
    {
        return $this->payerId;
    }

    /**
     * @param string $payerId
     */
    public function setPayerId($payerId)
    {
        $this->payerId = $payerId;
    }

    /**
     * @return PhoneWithType
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param PhoneWithType $phone
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return PayerTaxInfo
     */
    public function getTaxInfo()
    {
        return $this->taxInfo;
    }

    /**
     * @param PayerTaxInfo $taxInfo
     */
    public function setTaxInfo($taxInfo)
    {
        $this->taxInfo = $taxInfo;
    }

    /** return sha1 of the object */
    public function generateChecksum()
    {
        return sha1(serialize($this));
    }

    public function toArray()
    {
        $data = [
            'email_address' => $this->getEmailAddress()
        ];

        if (!empty($this->getPayerId())) {
            $data['payer_id'] = $this->getPayerId();
        }

        if (!empty($this->getName())) {
            $data['name'] = $this->getName()->toArray();
        }

        if (!empty($this->getAddress())) {
            $data['address'] = $this->getAddress()->toArray();
        }

        if (!empty($this->getBirthDate())) {
            $data['birth_date'] = $this->getBirthDate();
        }

        if (!empty($this->getPhone())) {
            $data['phone'] = $this->getPhone()->toArray();
        }

        if (!empty($this->getTaxInfo())) {
            $data['tax_info'] = $this->getTaxInfo()->toArray();
        }

        return array_filter($data);
    }
}
