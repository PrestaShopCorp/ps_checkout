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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Builder\PayPalSdkLink\PayPalSdkLinkBuilder;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

class ExpressCheckout
{
    const PRODUCT_MODE = 'product';
    const CART_MODE = 'cart';
    const CHECKOUT_MODE = 'checkout';

    /**
     * @var \Ps_checkout
     */
    private $module;

    /**
     * @var \Context
     */
    private $context;

    private $displayMode = self::PRODUCT_MODE;

    public function __construct(\Ps_checkout $module, \Context $context)
    {
        $this->module = $module;
        $this->context = $context;
    }

    public function setDisplayMode($mode)
    {
        $this->displayMode = $mode;
    }

    public function render()
    {
        if (false === $this->module->merchantIsValid()) {
            return false;
        }

        /** @var PaypalAccountRepository $paypalAccountRepository */
        $paypalAccountRepository = $this->module->getService('ps_checkout.repository.paypal.account');
        /** @var PayPalSdkLinkBuilder $paypalSdkLink */
        $paypalSdkLink = $this->module->getService('ps_checkout.sdk.paypal.linkbuilder');
        $paypalSdkLink->enableDisplayExpressCheckout();

        $this->context->smarty->assign([
            'paypalSdkLink' => $paypalSdkLink->buildLink(),
            'displayMode' => $this->displayMode,
            'isPs176' => version_compare(_PS_VERSION_, '1.7.6.0', '>='),
            'jsExpressCheckoutPath' => $this->module->getPathUri() . 'views/js/initExpressCheckout.js',
            'checkoutLink' => $this->context->link->getPageLink('order', true, $this->context->language->id, ['paymentMethod' => 'paypal']),
            'expressCheckoutController' => $this->context->link->getModuleLink($this->module->name, 'ExpressCheckout'),
            'paypalIsActive' => $paypalAccountRepository->paypalPaymentMethodIsValid(),
            'currencyIsoCode' => $this->context->currency->iso_code,
            'isCardPaymentError' => (bool) \Tools::getValue('hferror'),
        ]);

        return $this->module->display($this->module->getLocalPath(), 'views/templates/front/expressCheckout.tpl');
    }
}
