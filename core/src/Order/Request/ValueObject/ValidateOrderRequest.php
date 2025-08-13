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

namespace PsCheckout\Core\Order\Request\ValueObject;

class ValidateOrderRequest
{
    /**
     * @var int|null
     */
    private $cartId;

    /**
     * @var string|null
     */
    private $orderId;

    /**
     * @var string|null
     */
    private $payerId;

    /**
     * @var string|null
     */
    private $fundingSource;

    /**
     * @var bool
     */
    private $isExpressCheckout;

    /**
     * @var bool
     */
    private $isCardFields;

    public function __construct(array $request, int $cartId)
    {
        // Initialize values from the provided request array
        $this->cartId = $cartId ?? null;
        $this->orderId = $request['orderID'] ?? null;
        $this->payerId = $request['payerID'] ?? null;
        $this->fundingSource = $request['fundingSource'] ?? $request['paypalFundingSource'] ?? null;
        $this->isExpressCheckout = $request['isExpressCheckout'] ?? $request['isExpressCheckoutFromCart'] ?? false;
        $this->isCardFields = $request['isHostedFields'] ?? $request['isHostedFieldsFromCart'] ?? false;
    }

    /**
     * @return int|null
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return string|null
     */
    public function getPayerId()
    {
        return $this->payerId;
    }

    /**
     * @return string|null
     */
    public function getFundingSource()
    {
        return $this->fundingSource;
    }

    /**
     * @return bool
     */
    public function isExpressCheckout()
    {
        return $this->isExpressCheckout;
    }

    /**
     * @return bool
     */
    public function isCardFields()
    {
        return $this->isCardFields;
    }
}
