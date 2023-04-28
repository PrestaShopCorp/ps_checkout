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

namespace PrestaShop\Module\PrestashopCheckout\Order\State\Service;

use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfiguration;

class CheckOrderState
{
    const TRANSITION_ALLOWED = [
        OrderStateConfiguration::PAYMENT_ACCEPTED => [
            OrderStateConfiguration::PARTIALLY_REFUNDED,
            OrderStateConfiguration::REFUNDED,
        ],
        OrderStateConfiguration::WAITING_CAPTURE => [
            OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,
            OrderStateConfiguration::WAITING_CREDIT_CARD_PAYMENT,
            OrderStateConfiguration::WAITING_LOCAL_PAYMENT,
        ],
        OrderStateConfiguration::PARTIALLY_REFUNDED => [
            OrderStateConfiguration::REFUNDED,
        ],
        OrderStateConfiguration::REFUNDED => [],
        OrderStateConfiguration::WAITING_PAYPAL_PAYMENT => [
            OrderStateConfiguration::PAYMENT_ACCEPTED,
            OrderStateConfiguration::PARTIALLY_PAID,
            OrderStateConfiguration::CANCELED,
            OrderStateConfiguration::PAYMENT_ERROR,
        ],
        OrderStateConfiguration::WAITING_CREDIT_CARD_PAYMENT => [],
        OrderStateConfiguration::WAITING_LOCAL_PAYMENT => [],
        OrderStateConfiguration::AUTHORIZED => [
            OrderStateConfiguration::WAITING_PAYPAL_PAYMENT,
            OrderStateConfiguration::WAITING_CREDIT_CARD_PAYMENT,
            OrderStateConfiguration::WAITING_LOCAL_PAYMENT,
            OrderStateConfiguration::PARTIALLY_REFUNDED,
        ],
        OrderStateConfiguration::PARTIALLY_PAID => [
            OrderStateConfiguration::PAYMENT_ERROR,
            OrderStateConfiguration::PAYMENT_ACCEPTED,
        ],
        OrderStateConfiguration::OUT_OF_STOCK_PAID => [],
        OrderStateConfiguration::OUT_OF_STOCK_UNPAID => [],
        OrderStateConfiguration::PAYMENT_ERROR => [],
        OrderStateConfiguration::CANCELED => [],
    ];

    /**
     * @param string $currentOrderState
     * @param string $newOrderState
     *
     * @return bool
     */
    public function isCurrentOrderState($currentOrderState, $newOrderState)
    {
        return $currentOrderState === $newOrderState;
    }

    /**
     * @param string $currentOrderStateId
     * @param string $newOrderStateId
     *
     * @return bool
     *
     * @throws OrderException
     */
    public function isOrderStateTransitionAvailable($currentOrderStateId, $newOrderStateId)
    {
        if (!is_string($currentOrderStateId)) {
            throw new OrderException(sprintf('Type of currentOrderStateId (%s) is not string', gettype($currentOrderStateId)), OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER);
        }
        if (!is_string($newOrderStateId)) {
            throw new OrderException(sprintf('Type of newOrderStateId (%s) is not string', gettype($newOrderStateId)), OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER);
        }
        if (!key_exists($currentOrderStateId, self::TRANSITION_ALLOWED)) {
            throw new OrderException(sprintf('The currentOrderStateId doesn\'t exist (%s)', $currentOrderStateId), OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER);
        }

        return in_array($newOrderStateId, self::TRANSITION_ALLOWED[$currentOrderStateId]);
    }
}
