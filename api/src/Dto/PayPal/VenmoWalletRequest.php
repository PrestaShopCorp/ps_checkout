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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * Information needed to pay using Venmo.
 */
class VenmoWalletRequest
{
    /**
     * @var string|null
     */
    private $vaultId;

    /**
     * @var string|null
     */
    private $emailAddress;

    /**
     * @var VenmoWalletExperienceContext|null
     */
    private $experienceContext;

    /**
     * @var VenmoWalletAdditionalAttributes|null
     */
    private $attributes;

    /**
     * Returns Vault Id.
     * The PayPal-generated ID for the vaulted payment source. This ID should be stored on the merchant's
     * server so the saved payment source can be used for future transactions.
     */
    public function getVaultId(): ?string
    {
        return $this->vaultId;
    }

    /**
     * Sets Vault Id.
     * The PayPal-generated ID for the vaulted payment source. This ID should be stored on the merchant's
     * server so the saved payment source can be used for future transactions.
     *
     * @maps vault_id
     * @return self
     */
    public function setVaultId(?string $vaultId): self
    {
        $this->vaultId = $vaultId;

        return $this;
    }

    /**
     * Returns Email Address.
     * The internationalized email address. Note: Up to 64 characters are allowed before and 255 characters
     * are allowed after the @ sign. However, the generally accepted maximum length for an email address is
     * 254 characters. The pattern verifies that an unquoted @ sign exists.
     */
    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    /**
     * Sets Email Address.
     * The internationalized email address. Note: Up to 64 characters are allowed before and 255 characters
     * are allowed after the @ sign. However, the generally accepted maximum length for an email address is
     * 254 characters. The pattern verifies that an unquoted @ sign exists.
     *
     * @maps email_address
     * @return self
     */
    public function setEmailAddress(?string $emailAddress): self
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    /**
     * Returns Experience Context.
     * Customizes the buyer experience during the approval process for payment with Venmo. Note: Partners
     * and Marketplaces might configure shipping_preference during partner account setup, which overrides
     * the request values.
     */
    public function getExperienceContext(): ?VenmoWalletExperienceContext
    {
        return $this->experienceContext;
    }

    /**
     * Sets Experience Context.
     * Customizes the buyer experience during the approval process for payment with Venmo. Note: Partners
     * and Marketplaces might configure shipping_preference during partner account setup, which overrides
     * the request values.
     *
     * @maps experience_context
     * @return self
     */
    public function setExperienceContext(?VenmoWalletExperienceContext $experienceContext): self
    {
        $this->experienceContext = $experienceContext;

        return $this;
    }

    /**
     * Returns Attributes.
     * Additional attributes associated with the use of this Venmo Wallet.
     */
    public function getAttributes(): ?VenmoWalletAdditionalAttributes
    {
        return $this->attributes;
    }

    /**
     * Sets Attributes.
     * Additional attributes associated with the use of this Venmo Wallet.
     *
     * @maps attributes
     * @return self
     */
    public function setAttributes(?VenmoWalletAdditionalAttributes $attributes): self
    {
        $this->attributes = $attributes;

        return $this;
    }
}
