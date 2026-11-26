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

namespace PsCheckout\Core\PayPal\Order\Exception;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;

class CartAlreadyPaidException extends PsCheckoutException
{
    /**
     * @var PayPalOrderResponse|null
     */
    private $paidPayPalOrder;

    /**
     * @param string $message
     * @param PayPalOrderResponse|null $paidPayPalOrder The paid PayPal order as read from PayPal, when it was read
     */
    public function __construct(string $message, $paidPayPalOrder = null)
    {
        parent::__construct($message, self::CART_ALREADY_PAID);

        $this->paidPayPalOrder = $paidPayPalOrder;
    }

    /**
     * @return PayPalOrderResponse|null
     */
    public function getPaidPayPalOrder()
    {
        return $this->paidPayPalOrder;
    }
}
