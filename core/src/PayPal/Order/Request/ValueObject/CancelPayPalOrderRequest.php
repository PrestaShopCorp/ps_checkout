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

namespace PsCheckout\Core\PayPal\Order\Request\ValueObject;

class CancelPayPalOrderRequest
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
     * @var string
     */
    private $orderStatus;

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
    private $isHostedFields;

    /**
     * @var string|null
     */
    private $reason;

    /**
     * @var string|null
     */
    private $error;

    /**
     * CancelOrderRequest constructor.
     *
     * @param array $request
     */
    public function __construct(array $request, int $cartId)
    {
        $this->cartId = $cartId;
        $this->orderId = isset($request['orderID']) ? (string) $request['orderID'] : null;
        $this->orderStatus = isset($request['orderStatus']) ? (string) $request['orderStatus'] : 'CANCELED';
        $this->fundingSource = isset($request['fundingSource']) ? (string) $request['fundingSource'] : null;
        $this->isExpressCheckout = isset($request['isExpressCheckout']) && (bool) $request['isExpressCheckout'];
        $this->isHostedFields = isset($request['isHostedFields']) && (bool) $request['isHostedFields'];
        $this->reason = isset($request['reason']) ? (string) $request['reason'] : null;
        $this->error = isset($request['error'])
            ? (is_string($request['error']) ? $request['error'] : json_encode($request['error']))
            : null;
    }

    /**
     * Get the cart ID.
     *
     * @return int|null
     */
    public function getCartId()
    {
        return $this->cartId;
    }

    /**
     * Get the order ID.
     *
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * Get the order status.
     *
     * @return string
     */
    public function getOrderStatus(): string
    {
        return $this->orderStatus;
    }

    /**
     * Get the funding source.
     *
     * @return string|null
     */
    public function getFundingSource()
    {
        return $this->fundingSource;
    }

    /**
     * Check if it is an express checkout.
     *
     * @return bool
     */
    public function isExpressCheckout(): bool
    {
        return $this->isExpressCheckout;
    }

    /**
     * Check if hosted fields are used.
     *
     * @return bool
     */
    public function isHostedFields(): bool
    {
        return $this->isHostedFields;
    }

    /**
     * Get the cancellation reason.
     *
     * @return string|null
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Get the error message.
     *
     * @return string|null
     */
    public function getError()
    {
        return $this->error;
    }
}
