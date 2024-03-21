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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Command;

use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;

class SavePaymentTokenCommand
{
    /** @var PaymentTokenId */
    private $paymentTokenId;

    /** @var PayPalCustomerId */
    private $paypalCustomerId;

    /** @var string */
    private $paymentSource;

    /** @var array */
    private $paymentTokenData;
    /** @var bool */
    private $setFavorite;
    /** @var string */
    private $merchantId;
    /** @var string */
    private $status;

    /**
     * @param PaymentTokenId $paymentTokenId
     * @param PayPalCustomerId $paypalCustomerId
     * @param string $paymentSource
     * @param array $paymentTokenData
     */
    public function __construct($paymentTokenId, $paypalCustomerId, $status, $paymentSource, $paymentTokenData, $merchantId, $setFavorite = false)
    {
        $this->paymentTokenId = $paymentTokenId;
        $this->paypalCustomerId = $paypalCustomerId;
        $this->paymentSource = $paymentSource;
        $this->paymentTokenData = $paymentTokenData;
        $this->setFavorite = $setFavorite;
        $this->merchantId = $merchantId;
        $this->status = $status;
    }

    /**
     * @return PaymentTokenId
     */
    public function getPaymentTokenId()
    {
        return $this->paymentTokenId;
    }

    /**
     * @return PayPalCustomerId
     */
    public function getPaypalCustomerId()
    {
        return $this->paypalCustomerId;
    }

    /**
     * @return string
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @return array
     */
    public function getPaymentTokenData()
    {
        return $this->paymentTokenData;
    }

    /**
     * @return bool
     */
    public function isFavorite()
    {
        return $this->setFavorite;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }
}
