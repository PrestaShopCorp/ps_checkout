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
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;

class CheckOrderState
{
    const TRANSITION_ALLOWED = [
        OrderStateConfigurationKeys::PAYMENT_ACCEPTED => [
            OrderStateConfigurationKeys::PARTIALLY_REFUNDED,
            OrderStateConfigurationKeys::REFUNDED,
        ],
        OrderStateConfigurationKeys::WAITING_CAPTURE => [
            OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT,
            OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT,
        ],
        OrderStateConfigurationKeys::PARTIALLY_REFUNDED => [
            OrderStateConfigurationKeys::REFUNDED,
        ],
        OrderStateConfigurationKeys::REFUNDED => [],
        OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT => [
            OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
            OrderStateConfigurationKeys::PARTIALLY_PAID,
            OrderStateConfigurationKeys::CANCELED,
            OrderStateConfigurationKeys::PAYMENT_ERROR,
        ],
        OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT => [
            OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
            OrderStateConfigurationKeys::PARTIALLY_PAID,
            OrderStateConfigurationKeys::CANCELED,
            OrderStateConfigurationKeys::PAYMENT_ERROR,
        ],
        OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT => [
            OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
            OrderStateConfigurationKeys::PARTIALLY_PAID,
            OrderStateConfigurationKeys::CANCELED,
            OrderStateConfigurationKeys::PAYMENT_ERROR,
        ],
        OrderStateConfigurationKeys::AUTHORIZED => [
            OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT,
            OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT,
            OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT,
            OrderStateConfigurationKeys::PARTIALLY_REFUNDED,
        ],
        OrderStateConfigurationKeys::PARTIALLY_PAID => [
            OrderStateConfigurationKeys::PAYMENT_ERROR,
            OrderStateConfigurationKeys::PAYMENT_ACCEPTED,
        ],
        OrderStateConfigurationKeys::OUT_OF_STOCK_PAID => [],
        OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID => [],
        OrderStateConfigurationKeys::PAYMENT_ERROR => [],
        OrderStateConfigurationKeys::CANCELED => [],
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
