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

class ApplePayRequest
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $email_address;
    /**
     * @var Phone
     */
    private $phone_number;
    /**
     * @var CardStoredCredentialsRequest
     */
    private $stored_credentials;
    /**
     * @var string
     */
    private $vault_id;
    /**
     * @var ApplePayAttributesRequest
     */
    private $attributes;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return Phone
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * @param Phone $phone_number
     *
     * @return void
     */
    public function setPhoneNumber(Phone $phone_number)
    {
        $this->phone_number = $phone_number;
    }

    /**
     * @return CardStoredCredentialsRequest
     */
    public function getStoredCredentials()
    {
        return $this->stored_credentials;
    }

    /**
     * @param CardStoredCredentialsRequest $stored_credentials
     *
     * @return void
     */
    public function setStoredCredentials(CardStoredCredentialsRequest $stored_credentials)
    {
        $this->stored_credentials = $stored_credentials;
    }

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
     * @return ApplePayAttributesRequest
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param ApplePayAttributesRequest $attributes
     *
     * @return void
     */
    public function setAttributes(ApplePayAttributesRequest $attributes)
    {
        $this->attributes = $attributes;
    }
}
