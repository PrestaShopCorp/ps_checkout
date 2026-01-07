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
 * Merchant provided details of the native app or mobile web browser to facilitate buyer's app switch
 * to the PayPal consumer app.
 */
class AppSwitchContext
{
    /**
     * @var NativeAppContext|null
     */
    private $nativeApp;

    /**
     * @var MobileWebContext|null
     */
    private $mobileWeb;

    /**
     * Returns Native App.
     * Merchant provided, buyer's native app preferences to app switch to the PayPal consumer app.
     */
    public function getNativeApp(): ?NativeAppContext
    {
        return $this->nativeApp;
    }

    /**
     * Sets Native App.
     * Merchant provided, buyer's native app preferences to app switch to the PayPal consumer app.
     *
     * @maps native_app
     * @return self
     */
    public function setNativeApp(?NativeAppContext $nativeApp): self
    {
        $this->nativeApp = $nativeApp;

        return $this;
    }

    /**
     * Returns Mobile Web.
     * Buyer's mobile web browser context to app switch to the PayPal consumer app.
     */
    public function getMobileWeb(): ?MobileWebContext
    {
        return $this->mobileWeb;
    }

    /**
     * Sets Mobile Web.
     * Buyer's mobile web browser context to app switch to the PayPal consumer app.
     *
     * @maps mobile_web
     * @return self
     */
    public function setMobileWeb(?MobileWebContext $mobileWeb): self
    {
        $this->mobileWeb = $mobileWeb;

        return $this;
    }
}
