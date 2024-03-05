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

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;

class UpdatePayPalOrderCommand
{
    /**
     * @var PayPalOrderId
     */
    private $orderPayPalId;

    /**
     * @var CartId
     */
    private $cartId;

    /**
     * @var string
     */
    private $fundingSource;

    /**
     * @var bool
     */
    private $isHostedFields;

    /**
     * @var bool
     */
    private $isExpressCheckout;

    /**
     * @param string $orderPayPalId
     * @param int $cartId
     * @param string $fundingSource
     * @param bool $isHostedFields
     * @param bool $isExpressCheckout
     *
     * @throws CartException|PayPalOrderException
     */
    public function __construct($orderPayPalId, $cartId, $fundingSource, $isHostedFields, $isExpressCheckout)
    {
        $this->orderPayPalId = new PayPalOrderId($orderPayPalId);
        $this->cartId = new CartId($cartId);
        $this->fundingSource = $fundingSource;
        $this->isHostedFields = $isHostedFields;
        $this->isExpressCheckout = $isExpressCheckout;
    }

    /**
     * @return PayPalOrderId
     */
    public function getPayPalOrderId()
    {
        return $this->orderPayPalId;
    }

    /**
     * @return CartId
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @return string
     */
    public function getFundingSource()
    {
        return $this->fundingSource;
    }

    /**
     * @return bool
     */
    public function isHostedFields()
    {
        return $this->isHostedFields;
    }

    /**
     * @return bool
     */
    public function isExpressCheckout()
    {
        return $this->isExpressCheckout;
    }
}
