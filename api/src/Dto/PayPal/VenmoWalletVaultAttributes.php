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
 * Resource consolidating common request and response attirbutes for vaulting Venmo Wallet.
 */
class VenmoWalletVaultAttributes
{
    /**
     * @var string
     */
    private $storeInVault;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var string|null
     */
    private $usagePattern;

    /**
     * @var string
     */
    private $usageType;

    /**
     * @var string|null
     */
    private $customerType = VenmoPaymentTokenCustomerType::CONSUMER;

    /**
     * @var bool|null
     */
    private $permitMultiplePaymentTokens = false;

    /**
     * @param string $storeInVault
     * @param string $usageType
     */
    public function __construct(string $storeInVault, string $usageType)
    {
        $this->storeInVault = $storeInVault;
        $this->usageType = $usageType;
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
     */
    public function setStoreInVault(string $storeInVault): void
    {
        $this->storeInVault = $storeInVault;
    }

    /**
     * Returns Description.
     * The description displayed to Venmo consumer on the approval flow for Venmo, as well as on the Venmo
     * payment token management experience on Venmo.com.
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Sets Description.
     * The description displayed to Venmo consumer on the approval flow for Venmo, as well as on the Venmo
     * payment token management experience on Venmo.com.
     *
     * @maps description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Returns Usage Pattern.
     * Expected business/pricing model for the billing agreement.
     */
    public function getUsagePattern(): ?string
    {
        return $this->usagePattern;
    }

    /**
     * Sets Usage Pattern.
     * Expected business/pricing model for the billing agreement.
     *
     * @maps usage_pattern
     */
    public function setUsagePattern(?string $usagePattern): void
    {
        $this->usagePattern = $usagePattern;
    }

    /**
     * Returns Usage Type.
     * The usage type associated with the Venmo payment token.
     */
    public function getUsageType(): string
    {
        return $this->usageType;
    }

    /**
     * Sets Usage Type.
     * The usage type associated with the Venmo payment token.
     *
     * @required
     * @maps usage_type
     */
    public function setUsageType(string $usageType): void
    {
        $this->usageType = $usageType;
    }

    /**
     * Returns Customer Type.
     * The customer type associated with the Venmo payment token. This is to indicate whether the customer
     * acting on the merchant / platform is either a business or a consumer.
     */
    public function getCustomerType(): ?string
    {
        return $this->customerType;
    }

    /**
     * Sets Customer Type.
     * The customer type associated with the Venmo payment token. This is to indicate whether the customer
     * acting on the merchant / platform is either a business or a consumer.
     *
     * @maps customer_type
     */
    public function setCustomerType(?string $customerType): void
    {
        $this->customerType = $customerType;
    }

    /**
     * Returns Permit Multiple Payment Tokens.
     * Create multiple payment tokens for the same payer, merchant/platform combination. Use this when the
     * customer has not logged in at merchant/platform. The payment token thus generated, can then also be
     * used to create the customer account at merchant/platform. Use this also when multiple payment tokens
     * are required for the same payer, different customer at merchant/platform. This helps to identify
     * customers distinctly even though they may share the same Venmo account.
     */
    public function getPermitMultiplePaymentTokens(): ?bool
    {
        return $this->permitMultiplePaymentTokens;
    }

    /**
     * Sets Permit Multiple Payment Tokens.
     * Create multiple payment tokens for the same payer, merchant/platform combination. Use this when the
     * customer has not logged in at merchant/platform. The payment token thus generated, can then also be
     * used to create the customer account at merchant/platform. Use this also when multiple payment tokens
     * are required for the same payer, different customer at merchant/platform. This helps to identify
     * customers distinctly even though they may share the same Venmo account.
     *
     * @maps permit_multiple_payment_tokens
     */
    public function setPermitMultiplePaymentTokens(?bool $permitMultiplePaymentTokens): void
    {
        $this->permitMultiplePaymentTokens = $permitMultiplePaymentTokens;
    }
}
