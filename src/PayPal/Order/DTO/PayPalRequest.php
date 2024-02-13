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

class PayPalRequest
{
    /**
     * @var string
     */
    private $vault_id;
    /**
     * @var string
     */
    private $email_address;
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
    private $bith_date;
    /**
     * @var TaxInfo
     */
    private $tax_info;
    /**
     * @var AddressRequest
     */
    private $address;
    /**
     * @var PayPalWalletAttributesRequest
     */
    private $attributes;
    /**
     * @var PayPalWalletExperienceContext
     */
    private $experience_context;
    /**
     * @var string
     */
    private $billing_agreement_id;

    /**
     * @return string
     */
    public function getVaultId()
    {
        return $this->vault_id;
    }

    /**
     * @param string $vault_id
     *
     * @return void
     */
    public function setVaultId($vault_id)
    {
        $this->vault_id = $vault_id;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * @param string $email_address
     *
     * @return void
     */
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;
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
     *
     * @return void
     */
    public function setName(Name $name)
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
     *
     * @return void
     */
    public function setPhone(PhoneWithType $phone)
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getBithDate()
    {
        return $this->bith_date;
    }

    /**
     * @param string $bith_date
     *
     * @return void
     */
    public function setBithDate($bith_date)
    {
        $this->bith_date = $bith_date;
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
     *
     * @return void
     */
    public function setTaxInfo(TaxInfo $tax_info)
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
     *
     * @return void
     */
    public function setAddress(AddressRequest $address)
    {
        $this->address = $address;
    }

    /**
     * @return PayPalWalletAttributesRequest
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param PayPalWalletAttributesRequest $attributes
     *
     * @return void
     */
    public function setAttributes(PayPalWalletAttributesRequest $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @return PayPalWalletExperienceContext
     */
    public function getExperienceContext()
    {
        return $this->experience_context;
    }

    /**
     * @param PayPalWalletExperienceContext $experience_context
     *
     * @return void
     */
    public function setExperienceContext(PayPalWalletExperienceContext $experience_context)
    {
        $this->experience_context = $experience_context;
    }

    /**
     * @return string
     */
    public function getBillingAgreementId()
    {
        return $this->billing_agreement_id;
    }

    /**
     * @param string $billing_agreement_id
     *
     * @return void
     */
    public function setBillingAgreementId($billing_agreement_id)
    {
        $this->billing_agreement_id = $billing_agreement_id;
    }
}
