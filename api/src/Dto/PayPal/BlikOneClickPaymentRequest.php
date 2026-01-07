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
 * Information used to pay using BLIK one-click flow.
 */
class BlikOneClickPaymentRequest
{
    /**
     * @var string|null
     */
    private $authCode;

    /**
     * @var string
     */
    private $consumerReference;

    /**
     * @var string|null
     */
    private $aliasLabel;

    /**
     * @var string|null
     */
    private $aliasKey;

    /**
     * @param string $consumerReference
     */
    public function __construct(string $consumerReference)
    {
        $this->consumerReference = $consumerReference;
    }

    /**
     * Returns Auth Code.
     * The 6-digit code used to authenticate a consumer within BLIK.
     */
    public function getAuthCode(): ?string
    {
        return $this->authCode;
    }

    /**
     * Sets Auth Code.
     * The 6-digit code used to authenticate a consumer within BLIK.
     *
     * @maps auth_code
     */
    public function setAuthCode(?string $authCode): void
    {
        $this->authCode = $authCode;
    }

    /**
     * Returns Consumer Reference.
     * The merchant generated, unique reference serving as a primary identifier for accounts connected
     * between Blik and a merchant.
     */
    public function getConsumerReference(): string
    {
        return $this->consumerReference;
    }

    /**
     * Sets Consumer Reference.
     * The merchant generated, unique reference serving as a primary identifier for accounts connected
     * between Blik and a merchant.
     *
     * @required
     * @maps consumer_reference
     */
    public function setConsumerReference(string $consumerReference): void
    {
        $this->consumerReference = $consumerReference;
    }

    /**
     * Returns Alias Label.
     * A bank defined identifier used as a display name to allow the payer to differentiate between
     * multiple registered bank accounts.
     */
    public function getAliasLabel(): ?string
    {
        return $this->aliasLabel;
    }

    /**
     * Sets Alias Label.
     * A bank defined identifier used as a display name to allow the payer to differentiate between
     * multiple registered bank accounts.
     *
     * @maps alias_label
     */
    public function setAliasLabel(?string $aliasLabel): void
    {
        $this->aliasLabel = $aliasLabel;
    }

    /**
     * Returns Alias Key.
     * A Blik-defined identifier for a specific Blik-enabled bank account that is associated with a given
     * merchant. Used only in conjunction with a Consumer Reference.
     */
    public function getAliasKey(): ?string
    {
        return $this->aliasKey;
    }

    /**
     * Sets Alias Key.
     * A Blik-defined identifier for a specific Blik-enabled bank account that is associated with a given
     * merchant. Used only in conjunction with a Consumer Reference.
     *
     * @maps alias_key
     */
    public function setAliasKey(?string $aliasKey): void
    {
        $this->aliasKey = $aliasKey;
    }
}
