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


namespace PrestaShop\Module\PrestashopCheckout\Entity;

use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\ValueObject\PaymentTokenId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;

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
     * @var int
     */
    private $customerId;
    /**
     * @var int
     */
    private $shopId;
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
     * @param string $id
     * @param string $payPalCustomerId
     * @param int $customerId
     * @param int $shopId
     * @param string $paymentSource
     * @param array $data
     * @param bool $isFavorite
     */
    public function __construct($id, $payPalCustomerId, $customerId, $shopId, $paymentSource, $data, $isFavorite = false)
    {
        $this->id = new PaymentTokenId($id);
        $this->payPalCustomerId = new PayPalCustomerId($payPalCustomerId);
        $this->customerId = (int) $customerId;
        $this->shopId = $shopId;
        $this->paymentSource = $paymentSource;
        $this->data = $data;
        $this->isFavorite = $isFavorite;
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
        $this->id = $id;

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
        $this->payPalCustomerId = $payPalCustomerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param mixed $customerId
     *
     * @return PaymentToken
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = (int) $customerId;

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
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $shopId
     *
     * @return PaymentToken
     */
    public function setShopId($shopId)
    {
        $this->shopId = (int) $shopId;

        return $this;
    }
}
