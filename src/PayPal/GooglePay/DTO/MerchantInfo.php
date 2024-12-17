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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\DTO;

class MerchantInfo
{
    /**
     * @var string
     */
    private $merchantName;

    /**
     * @var string
     */
    private $merchantId;

    /**
     * @var string
     */
    private $merchantOrigin;

    /**
     * @return string
     */
    public function getMerchantName()
    {
        return $this->merchantName;
    }

    /**
     * @param string $merchantName
     *
     * @return MerchantInfo
     */
    public function setMerchantName($merchantName)
    {
        $this->merchantName = $merchantName;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     *
     * @return MerchantInfo
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantOrigin()
    {
        return $this->merchantOrigin;
    }

    /**
     * @param string $merchantOrigin
     *
     * @return MerchantInfo
     */
    public function setMerchantOrigin($merchantOrigin)
    {
        $this->merchantOrigin = $merchantOrigin;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_filter([
            'merchantName' => $this->merchantName,
            'merchantId' => $this->merchantId,
            'merchantOrigin' => $this->merchantOrigin,
        ]);
    }
}
