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

class Payer
{
    /**
     * @var string
     */
    private $email_address;
    /**
     * @var string
     */
    private $payer_id;
    /**
     * @var Name
     */
    private $name;
    /**
     * @var PhoneWithType
     */
    private $phone;
    /**
     * @var string
     */
    private $birth_date;
    /**
     * @var TaxInfo
     */
    private $tax_info;
    /**
     * @var AddressRequest
     */
    private $address;

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * @param string $email_address
     */
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayerId()
    {
        return $this->payer_id;
    }

    /**
     * @param string $payer_id
     */
    public function setPayerId($payer_id)
    {
        $this->payer_id = $payer_id;

        return $this;
    }

    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Name $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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

        return $this;
    }

    /**
     * @return string
     */
    public function getBirthDate()
    {
        return $this->birth_date;
    }

    /**
     * @param string $birth_date
     */
    public function setBirthDate($birth_date)
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    /**
     * @return TaxInfo
     */
    public function getTaxInfo()
    {
        return $this->tax_info;
    }

    /**
     * @param TaxInfo $tax_info
     */
    public function setTaxInfo($tax_info)
    {
        $this->tax_info = $tax_info;

        return $this;
    }

    /**
     * @return AddressRequest
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param AddressRequest $address
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }
}
