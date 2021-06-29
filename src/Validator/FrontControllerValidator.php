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

namespace PrestaShop\Module\PrestashopCheckout\Validator;

use PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration;

class FrontControllerValidator
{
    /**
     * @var MerchantValidator
     */
    private $merchantValidator;
    /**
     * @var ExpressCheckoutConfiguration
     */
    private $expressCheckoutConfiguration;
    /**
     * @var PayPalPayIn4XConfiguration
     */
    private $payIn4XConfiguration;

    public function __construct(
        MerchantValidator $merchantValidator,
        ExpressCheckoutConfiguration $expressCheckoutConfiguration,
        PayPalPayIn4XConfiguration $payIn4XConfiguration
    ) {
        $this->merchantValidator = $merchantValidator;
        $this->expressCheckoutConfiguration = $expressCheckoutConfiguration;
        $this->payIn4XConfiguration = $payIn4XConfiguration;
    }

    /**
     * @param string $controller
     *
     * @return bool
     */
    public function shouldLoadFrontJS($controller)
    {
        if (false === $this->merchantValidator->merchantIsValid()) {
            return false;
        }

        switch ($controller) {
            case 'orderopc':
            case 'order':
                return true;
            case 'product':
                return $this->expressCheckoutConfiguration->isProductPageEnabled() || $this->payIn4XConfiguration->isProductPageEnabled();
            case 'cart':
                return $this->expressCheckoutConfiguration->isOrderPageEnabled() || $this->payIn4XConfiguration->isOrderPageEnabled();
            case 'authentication':
                return $this->expressCheckoutConfiguration->isCheckoutPageEnabled();
        }

        return false;
    }

    /**
     * @param string $controller
     *
     * @return bool
     */
    public function shouldLoadFrontCss($controller)
    {
        if (false === $this->merchantValidator->merchantIsValid()) {
            return false;
        }

        switch ($controller) {
            // Payment step
            case 'orderopc':
            case 'order':
            // Payment methods logos (always if merchant is valid), Payment4X banner, ExpressCheckout button
            case 'product':
            case 'cart':
                return true;
            // ExpressCheckout button
            case 'authentication':
                return $this->expressCheckoutConfiguration->isCheckoutPageEnabled();
        }

        return false;
    }
}
