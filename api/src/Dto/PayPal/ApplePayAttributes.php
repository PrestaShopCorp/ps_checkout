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
 * Additional attributes associated with apple pay.
 */
class ApplePayAttributes
{
    /**
     * @var CustomerInformation|null
     */
    private $customer;

    /**
     * @var VaultInstruction|null
     */
    private $vault;

    /**
     * Returns Customer.
     * This object represents a merchant’s customer, allowing them to store contact details, and track all
     * payments associated with the same customer.
     */
    public function getCustomer(): ?CustomerInformation
    {
        return $this->customer;
    }

    /**
     * Sets Customer.
     * This object represents a merchant’s customer, allowing them to store contact details, and track all
     * payments associated with the same customer.
     *
     * @maps customer
     */
    public function setCustomer(?CustomerInformation $customer): void
    {
        $this->customer = $customer;
    }

    /**
     * Returns Vault.
     * Base vaulting specification. The object can be extended for specific use cases within each
     * payment_source that supports vaulting.
     */
    public function getVault(): ?VaultInstruction
    {
        return $this->vault;
    }

    /**
     * Sets Vault.
     * Base vaulting specification. The object can be extended for specific use cases within each
     * payment_source that supports vaulting.
     *
     * @maps vault
     */
    public function setVault(?VaultInstruction $vault): void
    {
        $this->vault = $vault;
    }
}
