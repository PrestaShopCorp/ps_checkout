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
 * Additional attributes associated with the use of this Venmo Wallet.
 */
class VenmoWalletAdditionalAttributes
{
    /**
     * @var VenmoWalletCustomerInformation|null
     */
    private $customer;

    /**
     * @var VenmoWalletVaultAttributes|null
     */
    private $vault;

    /**
     * Returns Customer.
     * The details about a customer in PayPal's system of record.
     */
    public function getCustomer(): ?VenmoWalletCustomerInformation
    {
        return $this->customer;
    }

    /**
     * Sets Customer.
     * The details about a customer in PayPal's system of record.
     *
     * @maps customer
     * @return self
     */
    public function setCustomer(?VenmoWalletCustomerInformation $customer): self
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Returns Vault.
     * Resource consolidating common request and response attirbutes for vaulting Venmo Wallet.
     */
    public function getVault(): ?VenmoWalletVaultAttributes
    {
        return $this->vault;
    }

    /**
     * Sets Vault.
     * Resource consolidating common request and response attirbutes for vaulting Venmo Wallet.
     *
     * @maps vault
     * @return self
     */
    public function setVault(?VenmoWalletVaultAttributes $vault): self
    {
        $this->vault = $vault;

        return $this;
    }
}
