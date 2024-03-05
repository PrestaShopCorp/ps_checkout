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

class PayPalOrder
{
    /**
     * @var string|null
     */
    private $id;
    /**
     * @var int|null
     */
    private $idCart;
    /**
     * @var string|null
     */
    private $intent;
    /**
     * @var string|null
     */
    private $fundingSource;
    /**
     * @var string|null
     */
    private $status;
    /**
     * @var string|null
     */
    private $paymentSource;
    /**
     * @var string
     */
    private $environment;
    /**
     * @var bool
     */
    private $isCardFields;
    /**
     * @var bool
     */
    private $isExpressCheckout;

    public function __construct($id = null, $idCart = null, $intent = null, $fundingSource = null, $status = null, $paymentSource = null, $environment = 'LIVE', $isCardFields = false, $isExpressCheckout = false)
    {
        $this->id = $id;
        $this->idCart = $idCart;
        $this->intent = $intent;
        $this->fundingSource = $fundingSource;
        $this->status = $status;
        $this->paymentSource = $paymentSource;
        $this->environment = $environment;
        $this->isCardFields = (bool) $isCardFields;
        $this->isExpressCheckout = (bool) $isExpressCheckout;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return PayPalOrder
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIdCart()
    {
        return $this->idCart;
    }

    /**
     * @param int $idCart
     *
     * @return PayPalOrder
     */
    public function setIdCart($idCart)
    {
        $this->idCart = $idCart;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     *
     * @return PayPalOrder
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFundingSource()
    {
        return $this->fundingSource;
    }

    /**
     * @param string $fundingSource
     *
     * @return PayPalOrder
     */
    public function setFundingSource($fundingSource)
    {
        $this->fundingSource = $fundingSource;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     *
     * @return PayPalOrder
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @param string $paymentSource
     *
     * @return PayPalOrder
     */
    public function setPaymentSource($paymentSource)
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @param string $environment
     *
     * @return PayPalOrder
     */
    public function setEnvironment($environment)
    {
        $this->environment = $environment;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsCardFields()
    {
        return $this->isCardFields;
    }

    /**
     * @param bool $isCardFields
     *
     * @return PayPalOrder
     */
    public function setIsCardFields($isCardFields)
    {
        $this->isCardFields = (bool) $isCardFields;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsExpressCheckout()
    {
        return $this->isExpressCheckout;
    }

    /**
     * @param bool $isExpressCheckout
     *
     * @return PayPalOrder
     */
    public function setIsExpressCheckout($isExpressCheckout)
    {
        $this->isExpressCheckout = (bool) $isExpressCheckout;

        return $this;
    }
}
