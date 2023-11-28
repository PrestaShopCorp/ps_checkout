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

namespace PrestaShop\Module\PrestashopCheckout\Context;

use PrestaShop\Module\PrestashopCheckout\Cart\Cart;
use PrestaShop\Module\PrestashopCheckout\Configuration\Configuration;
use PrestaShop\Module\PrestashopCheckout\Customer\Customer;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSource;
use PrestaShop\Module\PrestashopCheckout\Merchant\Merchant;
use PrestaShop\Module\PrestashopCheckout\Shop\Shop;

class CheckoutContext
{
    /** @var Cart */
    private $cart;

    /** @var Configuration */
    private $configuration;

    /** @var Customer */
    private $customer;

    /** @var Merchant */
    private $merchant;

    /** @var Shop */
    private $shop;

    /** @var FundingSource[] */
    private $fundingSources;

    /**
     * @param Cart $cart
     * @param Configuration $configuration
     * @param Customer $customer
     * @param Merchant $merchant
     * @param Shop $shop
     * @param FundingSource[] $fundingSources
     */
    public function __construct(
        Cart $cart,
        Configuration $configuration,
        Customer $customer,
        Merchant $merchant,
        Shop $shop,
        array $fundingSources
    ) {
        $this->cart = $cart;
        $this->configuration = $configuration;
        $this->customer = $customer;
        $this->merchant = $merchant;
        $this->shop = $shop;
        $this->fundingSources = $fundingSources;
    }

    /**
     * @return Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return Merchant
     */
    public function getMerchant()
    {
        return $this->merchant;
    }

    /**
     * @return Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @return FundingSource[]
     */
    public function getFundingSources()
    {
        return $this->fundingSources;
    }

    /**
     * @return FundingSource[]
     */
    public function getEligibleFundingSources()
    {
        $eligibleFundingSources = [];
        foreach ($this->fundingSources as $fundingSource) {
            if ($this->isPaymentSourceEligible($fundingSource)) {
                $eligibleFundingSources[] = $fundingSource;
            }
        }

        return $eligibleFundingSources;
    }

    /**
     * @return array
     */
    public function getEligibleUseCases()
    {
        return [];
    }

    /**
     * @return bool
     */
    public function isPaymentSourceEligible()
    {
        return true;
    }
}
