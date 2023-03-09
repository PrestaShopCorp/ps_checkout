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

namespace PrestaShop\Module\PrestashopCheckout\Cart\ValueObject;

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;

class CartId
{
    /**
     * @var int
     */
    private $cartId;

    /**
     * @param int $cartId
     *
     * @throws CartException
     */
    public function __construct($cartId)
    {
        $this->assertIntegerIsGreaterThanZero($cartId);

        $this->cartId = $cartId;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->cartId;
    }

    /**
     * @param int $cartId
     *
     * @throws CartException
     */
    public function assertIntegerIsGreaterThanZero($cartId)
    {
        if (!is_int($cartId) || 0 >= $cartId) {
            throw new CartException(sprintf('Cart id %s is invalid. Cart id must be number that is greater than zero.', var_export($cartId, true)), CartException::INVALID_ID);
        }
    }
}
