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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity;

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;

class PayPalOrder
{
    const TABLE = 'pscheckout_order';
    const CUSTOMER_INTENT_VAULT = 'VAULT';
    const CUSTOMER_INTENT_FAVORITE = 'FAVORITE';
    const CUSTOMER_INTENT_USES_VAULTING = 'USES_VAULTING';

    /**
     * @var PayPalOrderId
     */
    private $id;
    /**
     * @var int
     */
    private $idCart;
    /**
     * @var string
     */
    private $intent;
    /**
     * @var string
     */
    private $fundingSource;
    /**
     * @var string
     */
    private $status;
    /**
     * @var array
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
    /**
     * @var array
     */
    private $customerIntent;
    /**
     * @var PaymentTokenId|null
     */
    private $paymentTokenId;

    public function __construct($id, $idCart, $intent, $fundingSource, $status, $paymentSource = [], $environment = 'LIVE', $isCardFields = false, $isExpressCheckout = false, $customerIntent = [], $paymentTokenId = null)
    {
        $this->id = new PayPalOrderId($id);
        $this->idCart = $idCart;
        $this->intent = $intent;
        $this->fundingSource = $fundingSource;
        $this->status = $status;
        $this->paymentSource = $paymentSource;
        $this->environment = $environment;
        $this->isCardFields = (bool) $isCardFields;
        $this->isExpressCheckout = (bool) $isExpressCheckout;
        $this->customerIntent = $customerIntent;
        $this->paymentTokenId = $paymentTokenId;
    }

    /**
     * @return PayPalOrderId
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param PayPalOrderId $id
     *
     * @return PayPalOrder
     */
    public function setId(PayPalOrderId $id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return array
     */
    public function getPaymentSource()
    {
        return $this->paymentSource;
    }

    /**
     * @param array $paymentSource
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
    public function isCardFields()
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
    public function isExpressCheckout()
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

    /**
     * @return array
     */
    public function getCustomerIntent()
    {
        return $this->customerIntent;
    }

    /**
     * @param array $customerIntent
     *
     * @return PayPalOrder
     */
    public function setCustomerIntent($customerIntent)
    {
        $this->customerIntent = $customerIntent;

        return $this;
    }

    /**
     * Checks if customer intent contains an intent property and returns boolean value
     *
     * @param self::CUSTOMER_INTENT_VAULT|self::CUSTOMER_INTENT_FAVORITE|self::CUSTOMER_INTENT_USES_VAULTING $intent
     *
     * @return bool
     */
    public function checkCustomerIntent($intent)
    {
        return in_array($intent, $this->customerIntent);
    }

    /**
     * @return PaymentTokenId|null
     */
    public function getPaymentTokenId()
    {
        return $this->paymentTokenId;
    }

    /**
     * @param PaymentTokenId|null $paymentTokenId
     */
    public function setPaymentTokenId($paymentTokenId)
    {
        $this->paymentTokenId = $paymentTokenId;
    }
}
