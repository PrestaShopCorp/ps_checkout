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
 * Information about cardholder possession validation and cardholder identification and verifications
 * (ID&V).
 */
class AssuranceDetails
{
    /**
     * @var bool|null
     */
    private $accountVerified = false;

    /**
     * @var bool|null
     */
    private $cardHolderAuthenticated = false;

    /**
     * Returns Account Verified.
     * If true, indicates that Cardholder possession validation has been performed on returned payment
     * credential.
     */
    public function getAccountVerified(): ?bool
    {
        return $this->accountVerified;
    }

    /**
     * Sets Account Verified.
     * If true, indicates that Cardholder possession validation has been performed on returned payment
     * credential.
     *
     * @maps account_verified
     * @return self
     */
    public function setAccountVerified(?bool $accountVerified): self
    {
        $this->accountVerified = $accountVerified;

        return $this;
    }

    /**
     * Returns Card Holder Authenticated.
     * If true, indicates that identification and verifications (ID&V) was performed on the returned
     * payment credential.If false, the same risk-based authentication can be performed as you would for
     * card transactions. This risk-based authentication can include, but not limited to, step-up with 3D
     * Secure protocol if applicable.
     */
    public function getCardHolderAuthenticated(): ?bool
    {
        return $this->cardHolderAuthenticated;
    }

    /**
     * Sets Card Holder Authenticated.
     * If true, indicates that identification and verifications (ID&V) was performed on the returned
     * payment credential.If false, the same risk-based authentication can be performed as you would for
     * card transactions. This risk-based authentication can include, but not limited to, step-up with 3D
     * Secure protocol if applicable.
     *
     * @maps card_holder_authenticated
     * @return self
     */
    public function setCardHolderAuthenticated(?bool $cardHolderAuthenticated): self
    {
        $this->cardHolderAuthenticated = $cardHolderAuthenticated;

        return $this;
    }
}
