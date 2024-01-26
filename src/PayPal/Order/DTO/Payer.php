<?php

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
    }

}
