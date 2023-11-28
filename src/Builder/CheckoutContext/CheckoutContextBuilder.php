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

namespace PrestaShop\Module\PrestashopCheckout\Builder\CheckoutContext;

use PrestaShop\Module\PrestashopCheckout\Cart\Cart;
use PrestaShop\Module\PrestashopCheckout\Configuration\Configuration;
use PrestaShop\Module\PrestashopCheckout\Context\CheckoutContext;
use PrestaShop\Module\PrestashopCheckout\Country;
use PrestaShop\Module\PrestashopCheckout\Customer\Customer;
use PrestaShop\Module\PrestashopCheckout\Exception\ShopException;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSource;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceConfigurationRepository;
use PrestaShop\Module\PrestashopCheckout\Merchant\Merchant;
use PrestaShop\Module\PrestashopCheckout\Routing\Router;
use PrestaShop\Module\PrestashopCheckout\Shop\Shop;

class CheckoutContextBuilder
{
    public function build()
    {
        return new CheckoutContext(
            $this->buildCart(),
            $this->buildConfiguration(),
            $this->buildCustomer(),
            $this->buildMerchant(),
            $this->buildShop(),
            $this->buildFundingSources()
        );
    }

    /**
     * @return Cart
     */
    private function buildCart()
    {
        return new Cart();
    }

    /**
     * @return Configuration
     */
    private function buildConfiguration()
    {
        return new Configuration();
    }

    /**
     * @return Customer
     */
    private function buildCustomer()
    {
        return new Customer();
    }

    /**
     * @return Merchant
     */
    private function buildMerchant()
    {
        return new Merchant(
            1,
            new Country('', ''),
            []
        );
    }

    /**
     * @return Shop
     * @throws ShopException
     */
    private function buildShop()
    {
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        /** @var Router $router */
        $router = $module->getService('ps_checkout.prestashop.router');

        return new Shop(
            \Context::getContext()->shop->id,
            $router->getCheckoutValidateLink(),
            ''
        );
    }

    /**
     * @return FundingSource[]
     */
    private function buildFundingSources()
    {
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        /** @var FundingSourceConfigurationRepository $fundingSourceConfigurationRepository */
        $fundingSourceConfigurationRepository = $module->getService('ps_checkout.funding_source.configuration.repository');

        $fundingSources = [];
        $fundingSourcesDb = $fundingSourceConfigurationRepository->getAll();
        foreach ($fundingSourcesDb as $fundingSource) {
            $fundingSources[] = new FundingSource(
                $fundingSource->name,
                '',
                $fundingSource->position,
                [],
                $fundingSource->active,
                true
            );
        }

        return $fundingSources;
    }
}
