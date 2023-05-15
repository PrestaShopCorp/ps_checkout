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

namespace PrestaShop\Module\PrestashopCheckout\Temp\Entities;

class ApplicationContext
{
    /** @var string */
    private $brandName;

    /** @var string */
    private $cancelUrl;

    /** @var string */
    private $locale;

    /** @var string */
    private $returnUrl;

    /** @var string */
    private $shippingPreference;

    /**
     * @return string
     */
    public function getBrandName()
    {
        return $this->brandName;
    }

    /**
     * @param string $brandName
     */
    public function setBrandName($brandName)
    {
        $this->brandName = $brandName;
    }

    /**
     * @return string
     */
    public function getCancelUrl()
    {
        return $this->cancelUrl;
    }

    /**
     * @param string $cancelUrl
     */
    public function setCancelUrl($cancelUrl)
    {
        $this->cancelUrl = $cancelUrl;
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
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return string
     */
    public function getReturnUrl()
    {
        return $this->returnUrl;
    }

    /**
     * @param string $returnUrl
     */
    public function setReturnUrl($returnUrl)
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * @return string
     */
    public function getShippingPreference()
    {
        return $this->shippingPreference;
    }

    /**
     * @param string $shippingPreference
     */
    public function setShippingPreference($shippingPreference)
    {
        $this->shippingPreference = $shippingPreference;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $data = [];
        if (!empty($this->getBrandName())) {
            $data['brand_name'] = $this->getBrandName();
        }

        if (!empty($this->getShippingPreference())) {
            $data['shipping_preference'] = $this->getShippingPreference();
        }

        return array_filter($data);
    }
}
