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

class VenmoRequest
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
     * @var VenmoExperienceContextRequest
     */
    private $experience_context;
    /**
     * @var PayPalWalletAttributesRequest
     */
    private $attributes;

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
     * @return VenmoExperienceContextRequest
     */
    public function getExperienceContext()
    {
        return $this->experience_context;
    }

    /**
     * @param VenmoExperienceContextRequest $experience_context
     *
     * @return void
     */
    public function setExperienceContext(VenmoExperienceContextRequest $experience_context)
    {
        $this->experience_context = $experience_context;
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
}
