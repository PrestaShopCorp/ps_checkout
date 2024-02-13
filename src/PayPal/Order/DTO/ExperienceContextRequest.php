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

class ExperienceContextRequest
{
    /**
     * @var string
     */
    private $brand_name;
    /**
     * @var string
     */
    private $locale;
    /**
     * @var string
     */
    private $shipping_preference;
    /**
     * @var string
     */
    private $return_url;
    /**
     * @var string
     */
    private $cancel_url;

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->brand_name;
    }

    /**
     * @param string $brand_name
     *
     * @return void
     */
    public function setBrandName($brand_name)
    {
        $this->brand_name = $brand_name;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return void
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getShippingPreference()
    {
        return $this->shipping_preference;
    }

    /**
     * @param string $shipping_preference
     *
     * @return void
     */
    public function setShippingPreference($shipping_preference)
    {
        $this->shipping_preference = $shipping_preference;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->return_url;
    }

    /**
     * @param string $return_url
     *
     * @return void
     */
    public function setReturnUrl($return_url)
    {
        $this->return_url = $return_url;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancel_url;
    }

    /**
     * @param string $cancel_url
     *
     * @return void
     */
    public function setCancelUrl($cancel_url)
    {
        $this->cancel_url = $cancel_url;
    }
}
