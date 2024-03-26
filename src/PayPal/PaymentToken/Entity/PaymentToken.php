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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Entity;

use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;

class PaymentToken
{
    const TABLE = 'pscheckout_payment_token';

    /**
     * @var PaymentTokenId
     */
    private $id;
    /**
     * @var PayPalCustomerId
     */
    private $payPalCustomerId;
    /**
     * @var string
     */
    private $paymentSource;
    /**
     * @var array
     */
    private $data;
    /**
     * @var bool
     */
    private $isFavorite;
    /**
     * @var string
     */
    private $merchantId;
    /**
     * @var string
     */
    private $status;

    /**
     * @param string $id
     * @param string $payPalCustomerId
     * @param string $paymentSource
     * @param array $data
     * @param string $merchantId
     * @param bool $isFavorite
     */
    public function __construct($id, $payPalCustomerId, $paymentSource, $data, $merchantId, $status, $isFavorite = false)
    {
        $this->id = new PaymentTokenId($id);
        $this->payPalCustomerId = new PayPalCustomerId($payPalCustomerId);
        $this->paymentSource = $paymentSource;
        $this->data = $data;
        $this->isFavorite = $isFavorite;
        $this->merchantId = $merchantId;
        $this->status = $status;
    }

    /**
     * @return PaymentTokenId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return PaymentToken
     */
    public function setId($id)
    {
        $this->id = new PaymentTokenId($id);

        return $this;
    }

    /**
     * @return PayPalCustomerId
     */
    public function getPayPalCustomerId()
    {
        return $this->payPalCustomerId;
    }

    /**
     * @param string $payPalCustomerId
     *
     * @return PaymentToken
     */
    public function setPayPalCustomerId($payPalCustomerId)
    {
        $this->payPalCustomerId = new PayPalCustomerId($payPalCustomerId);

        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @param string $paymentSource
     *
     * @return PaymentToken
     */
    public function setPaymentSource($paymentSource)
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return PaymentToken
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return bool
     */
    public function isFavorite()
    {
        return $this->isFavorite;
    }

    /**
     * @param bool $isFavorite
     *
     * @return PaymentToken
     */
    public function setIsFavorite($isFavorite)
    {
        $this->isFavorite = $isFavorite;

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
     * @return PaymentToken
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return PaymentToken
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
