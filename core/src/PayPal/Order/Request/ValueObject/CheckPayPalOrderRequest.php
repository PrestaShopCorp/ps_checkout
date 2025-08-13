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

/**
 * Class CheckOrderRequest
 */
class CheckPayPalOrderRequest
{
    /** @var int */
    private $cartId;

    /** @var string */
    private $fundingSource;

    /** @var string|null */
    private $orderId;

    /** @var bool */
    private $isExpressCheckout;

    /** @var bool */
    private $isHostedFields;

    /**
     * CheckOrderRequest constructor.
     *
     * @param int $cartId
     * @param array $bodyValues
     */
    public function __construct(int $cartId, array $bodyValues)
    {
        $this->cartId = $cartId;
        $this->fundingSource = isset($bodyValues['fundingSource']) ? (string) $bodyValues['fundingSource'] : 'paypal';
        $this->orderId = isset($bodyValues['orderID']) ? (string) $bodyValues['orderID'] : null;
        $this->isExpressCheckout = isset($bodyValues['isExpressCheckout']) && (bool) $bodyValues['isExpressCheckout'];
        $this->isHostedFields = isset($bodyValues['isHostedFields']) && (bool) $bodyValues['isHostedFields'];
    }

    /**
     * @return int
     */
    public function getCartId(): int
    {
        return $this->cartId;
    }

    /**
     * @return string
     */
    public function getFundingSource(): string
    {
        return $this->fundingSource;
    }

    /**
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->orderId;
    }

    /**
     * @return bool
     */
    public function isExpressCheckout(): bool
    {
        return $this->isExpressCheckout;
    }

    /**
     * @return bool
     */
    public function isHostedFields(): bool
    {
        return $this->isHostedFields;
    }
}
