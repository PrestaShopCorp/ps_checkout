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
 * Base vaulting specification. The object can be extended for specific use cases within each
 * payment_source that supports vaulting.
 */
class VaultInstruction
{
    /**
     * @var string
     */
    private $storeInVault;

    /**
     * @param string $storeInVault
     */
    public function __construct(string $storeInVault)
    {
        $this->storeInVault = $storeInVault;
    }

    /**
     * Returns Store in Vault.
     * Defines how and when the payment source gets vaulted.
     */
    public function getStoreInVault(): string
    {
        return $this->storeInVault;
    }

    /**
     * Sets Store in Vault.
     * Defines how and when the payment source gets vaulted.
     *
     * @required
     * @maps store_in_vault
     * @return self
     */
    public function setStoreInVault(string $storeInVault): self
    {
        $this->storeInVault = $storeInVault;

        return $this;
    }
}
