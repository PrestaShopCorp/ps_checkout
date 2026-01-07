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
 * Additional attributes associated with the use of this card.
 */
class CardAttributes
{
    /**
     * @var CardCustomerInformation|null
     */
    private $customer;

    /**
     * @var VaultInstructionBase|null
     */
    private $vault;

    /**
     * @var CardVerification|null
     */
    private $verification;

    /**
     * Returns Customer.
     * The details about a customer in PayPal's system of record.
     */
    public function getCustomer(): ?CardCustomerInformation
    {
        return $this->customer;
    }

    /**
     * Sets Customer.
     * The details about a customer in PayPal's system of record.
     *
     * @maps customer
     */
    public function setCustomer(?CardCustomerInformation $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * Returns Vault.
     * Basic vault instruction specification that can be extended by specific payment sources that supports
     * vaulting.
     */
    public function getVault(): ?VaultInstructionBase
    {
        return $this->vault;
    }

    /**
     * Sets Vault.
     * Basic vault instruction specification that can be extended by specific payment sources that supports
     * vaulting.
     *
     * @maps vault
     */
    public function setVault(?VaultInstructionBase $vault): void
    {
        $this->vault = $vault;
    }

    /**
     * Returns Verification.
     * The API caller can opt in to verify the card through PayPal offered verification services (e.g.
     * Smart Dollar Auth, 3DS).
     */
    public function getVerification(): ?CardVerification
    {
        return $this->verification;
    }

    /**
     * Sets Verification.
     * The API caller can opt in to verify the card through PayPal offered verification services (e.g.
     * Smart Dollar Auth, 3DS).
     *
     * @maps verification
     */
    public function setVerification(?CardVerification $verification): void
    {
        $this->verification = $verification;
    }
}
