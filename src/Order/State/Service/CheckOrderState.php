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

class CheckOrderState
{
    const TRANSITION_ALLOWED = [
        'PS_CHECKOUT_STATE_WAITING_CAPTURE' => [
            'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT',
            'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT',
            'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT'
        ],
        'PS_CHECKOUT_STATE_PARTIAL_REFUND' => [],
        'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' => [],
        'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' => [],
        'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' => [],
        'PS_CHECKOUT_STATE_AUTHORIZED' => [
            'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT',
            'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT',
            'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT',
            'PS_CHECKOUT_STATE_PARTIAL_REFUND'
        ]
    ];

    /**
     * @var array
     */
    private $orderStateMapping;

    /**
     * @param array $orderStateMapping
     */
    public function __construct(array $orderStateMapping)
    {
        $this->orderStateMapping = $orderStateMapping;
    }

    /**
     * @param int $currentOrderState
     * @param int $newOrderState
     *
     * @return bool
     */
    public function isCurrentOrderState($currentOrderState, $newOrderState)
    {
        return $currentOrderState === $newOrderState;
    }

    /**
     * @param int $currentOrderStateId
     * @param int $newOrderStateId
     *
     * @return bool
     */
    public function isOrderStateTransitionAvailable($currentOrderStateId, $newOrderStateId)
    {
        $currentKey = $this->getOrderStateKey($currentOrderStateId);
        $newKey = $this->getOrderStateKey($newOrderStateId);
        foreach (self::TRANSITION_ALLOWED as $key => $availableStateKeys) {
            if ($currentKey === $key && in_array($newKey, $availableStateKeys)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param int $orderStateId
     *
     * @return string
     */
    private function getOrderStateKey($orderStateId)
    {
        foreach ($this->orderStateMapping as $const => $id) {
            if ($orderStateId === $id) {
                return $const;
            }
        }

        return '';
    }
}
