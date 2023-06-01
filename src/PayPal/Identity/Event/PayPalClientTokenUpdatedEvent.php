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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Event;

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;
use PrestaShop\Module\PrestashopCheckout\Event\Event;

class PayPalClientTokenUpdatedEvent extends Event
{
    /**
     * @var CartId
     */
    private $cartId;

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $tokenId;

    /**
     * @var int
     */
    private $expireIn;

    /**
     * @var int
     */
    private $createdAt;

    /**
     * @param int $cartId
     * @param string $token
     * @param string $tokenId
     * @param int $expireIn
     * @param int $createdAt
     *
     * @throws CartException
     */
    public function __construct($cartId, $token, $tokenId, $expireIn, $createdAt)
    {
        $this->cartId = new CartId($cartId);
        $this->token = $token;
        $this->tokenId = $tokenId;
        $this->expireIn = $expireIn;
        $this->createdAt = $createdAt;
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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return string
     */
    public function getIdToken()
    {
        return $this->tokenId;
    }

    /**
     * @return int
     */
    public function getExpireIn()
    {
        return $this->expireIn;
    }

    /**
     * @return int
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }
}
