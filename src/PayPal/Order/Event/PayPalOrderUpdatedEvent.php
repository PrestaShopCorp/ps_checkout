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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Event;

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;

class PayPalOrderUpdatedEvent extends PayPalOrderEvent
{
    /**
     * @var CartId
     */
    private $cartId;

    /**
     * @var bool
     */
    private $isHostedFields;

    /**
     * @var bool
     */
    private $isExpressCheckout;

    /**
     * @var string
     */
    private $fundingSource;

    /**
     * @param string $orderPayPalId
     * @param array $orderPayPal
     * @param int $cartId
     * @param bool $isHostedFields
     * @param bool $isExpressCheckout
     * @param string $fundingSource
     *
     * @throws CartException
     * @throws PayPalOrderException
     */
    public function __construct($orderPayPalId, $orderPayPal, $cartId, $isHostedFields, $isExpressCheckout, $fundingSource)
    {
        parent::__construct($orderPayPalId, $orderPayPal);
        $this->cartId = new CartId($cartId);
        $this->isHostedFields = $isHostedFields;
        $this->isExpressCheckout = $isExpressCheckout;
        $this->fundingSource = $fundingSource;
    }

    /**
     * @return CartId
     */
    public function getCartId()
    {
        return $this->cartId;
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

    /**
     * @return string
     */
    public function getFundingSource()
    {
        return $this->fundingSource;
    }
}
