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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command;

use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;

class SavePayPalOrderCommand
{
    /**
     * @var array
     */
    private $order;
    /**
     * @var CartId|null
     */
    private $cartId;
    /**
     * @var string|null
     */
    private $paymentMode;
    /**
     * @var array
     */
    private $customerIntent;
    /**
     * @var bool|null
     */
    private $isExpressCheckout;
    /**
     * @var bool|null
     */
    private $isCardFields;
    /**
     * @var null
     */
    private $fundingSource;
    /**
     * @var PaymentTokenId|null
     */
    private $paymentTokenId;

    /**
     * @param array $order
     */
    public function __construct($order, CartId $cartId = null, $fundingSource = null, $paymentMode = null, $customerIntent = [], $isExpressCheckout = null, $isCardFields = null, $paymentTokenId = null)
    {
        $this->order = $order;
        $this->cartId = $cartId;
        $this->paymentMode = $paymentMode;
        $this->customerIntent = $customerIntent;
        $this->isExpressCheckout = $isExpressCheckout;
        $this->isCardFields = $isCardFields;
        $this->fundingSource = $fundingSource;
        $this->paymentTokenId = $paymentTokenId;
    }

    /**
     * @return array
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return CartId|null
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @return string|null
     */
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * @return array
     */
    public function getCustomerIntent()
    {
        return $this->customerIntent;
    }

    /**
     * @return bool|null
     */
    public function isExpressCheckout()
    {
        return $this->isExpressCheckout;
    }

    /**
     * @return bool|null
     */
    public function isCardFields()
    {
        return $this->isCardFields;
    }

    /**
     * @return string|null
     */
    public function getFundingSource()
    {
        return $this->fundingSource;
    }

    /**
     * @return PaymentTokenId|null
     */
    public function getPaymentTokenId()
    {
        return $this->paymentTokenId;
    }
}
