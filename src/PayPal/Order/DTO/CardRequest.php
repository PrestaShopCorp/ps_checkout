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

class CardRequest
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var AddressRequest
     */
    private $billing_address;
    /**
     * @var CardAttributesRequest
     */
    private $attributes;
    /**
     * @var string
     */
    private $vault_id;
    /**
     * @var CardStoredCredentialsRequest
     */
    private $stored_credentials;
    /**
     * @var CardExperienceContextRequest
     */
    private $experience_context;

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
     * @return AddressRequest
     */
    public function getBillingAddress()
    {
        return $this->billing_address;
    }

    /**
     * @param AddressRequest $billing_address
     *
     * @return void
     */
    public function setBillingAddress(AddressRequest $billing_address)
    {
        $this->billing_address = $billing_address;
    }

    /**
     * @return CardAttributesRequest
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param CardAttributesRequest $attributes
     *
     * @return void
     */
    public function setAttributes(CardAttributesRequest $attributes)
    {
        $this->attributes = $attributes;
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
     * @return CardExperienceContextRequest
     */
    public function getExperienceContext()
    {
        return $this->experience_context;
    }

    /**
     * @param CardExperienceContextRequest $experience_context
     *
     * @return void
     */
    public function setExperienceContext(CardExperienceContextRequest $experience_context)
    {
        $this->experience_context = $experience_context;
    }
}
