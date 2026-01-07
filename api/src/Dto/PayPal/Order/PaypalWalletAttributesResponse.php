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

namespace PsCheckout\Api\Dto\PayPal\Order;

/**
 * Additional attributes associated with the use of a PayPal Wallet.
 */
class PaypalWalletAttributesResponse
{
    /**
     * @var PaypalWalletVaultResponse|null
     */
    private $vault;

    /**
     * @var CobrandedCard[]|null
     */
    private $cobrandedCards;

    /**
     * Returns Vault.
     * The details about a saved PayPal Wallet payment source.
     */
    public function getVault(): ?PaypalWalletVaultResponse
    {
        return $this->vault;
    }

    /**
     * Sets Vault.
     * The details about a saved PayPal Wallet payment source.
     *
     * @maps vault
     */
    public function setVault(?PaypalWalletVaultResponse $vault): void
    {
        $this->vault = $vault;
    }

    /**
     * Returns Cobranded Cards.
     * An array of merchant cobranded cards used by buyer to complete an order. This array will be present
     * if a merchant has onboarded their cobranded card with PayPal and provided corresponding label(s).
     *
     * @return CobrandedCard[]|null
     */
    public function getCobrandedCards(): ?array
    {
        return $this->cobrandedCards;
    }

    /**
     * Sets Cobranded Cards.
     * An array of merchant cobranded cards used by buyer to complete an order. This array will be present
     * if a merchant has onboarded their cobranded card with PayPal and provided corresponding label(s).
     *
     * @maps cobranded_cards
     *
     * @param CobrandedCard[]|null $cobrandedCards
     */
    public function setCobrandedCards(?array $cobrandedCards): void
    {
        $this->cobrandedCards = $cobrandedCards;
    }
}
