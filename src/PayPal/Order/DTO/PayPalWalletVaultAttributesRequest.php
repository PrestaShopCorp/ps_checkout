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

class PayPalWalletVaultAttributesRequest
{
    /**
     * @var string
     */
    private $store_in_vault;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $usage_pattern;
    /**
     * @var ShippingRequest
     */
    private $shipping;
    /**
     * @var string
     */
    private $usage_type = 'MERCHANT';
    /**
     * @var string
     */
    private $customer_type;
    /**
     * @var bool
     */
    private $permit_multiple_payment_tokens;

    /**
     * @return string
     */
    public function getStoreInVault()
    {
        return $this->store_in_vault;
    }

    /**
     * @param string $store_in_vault
     *
     * @return void
     */
    public function setStoreInVault($store_in_vault)
    {
        $this->store_in_vault = $store_in_vault;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getUsagePattern()
    {
        return $this->usage_pattern;
    }

    /**
     * @param string $usage_pattern
     *
     * @return void
     */
    public function setUsagePattern($usage_pattern)
    {
        $this->usage_pattern = $usage_pattern;
    }

    /**
     * @return ShippingRequest
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param ShippingRequest $shipping
     *
     * @return void
     */
    public function setShipping(ShippingRequest $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return string
     */
    public function getUsageType()
    {
        return $this->usage_type;
    }

    /**
     * @param string $usage_type
     *
     * @return void
     */
    public function setUsageType($usage_type)
    {
        $this->usage_type = $usage_type;
    }

    /**
     * @return string
     */
    public function getCustomerType()
    {
        return $this->customer_type;
    }

    /**
     * @param string $customer_type
     *
     * @return void
     */
    public function setCustomerType($customer_type)
    {
        $this->customer_type = $customer_type;
    }

    /**
     * @return bool
     */
    public function isPermitMultiplePaymentTokens()
    {
        return $this->permit_multiple_payment_tokens;
    }

    /**
     * @param bool $permit_multiple_payment_tokens
     *
     * @return void
     */
    public function setPermitMultiplePaymentTokens($permit_multiple_payment_tokens)
    {
        $this->permit_multiple_payment_tokens = $permit_multiple_payment_tokens;
    }
}
