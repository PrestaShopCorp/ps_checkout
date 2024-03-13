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

class PaymentToken
{
    const TABLE = 'pscheckout_payment_token';

    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $payPalCustomerId;
    /**
     * @var string
     */
    private $paymentSource;
    /**
     * @var string
     */
    private $data;
    /**
     * @var bool
     */
    private $isFavorite;

    /**
     * @param string $id
     * @param string $payPalCustomerId
     * @param string $paymentSource
     * @param string $data
     * @param bool $isFavorite
     */
    public function __construct($id, $payPalCustomerId, $paymentSource, $data, $isFavorite = false)
    {
        $this->id = $id;
        $this->payPalCustomerId = $payPalCustomerId;
        $this->paymentSource = $paymentSource;
        $this->data = $data;
        $this->isFavorite = $isFavorite;
    }

    /**
     * @return string
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
     * @return string
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
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $data
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
}
