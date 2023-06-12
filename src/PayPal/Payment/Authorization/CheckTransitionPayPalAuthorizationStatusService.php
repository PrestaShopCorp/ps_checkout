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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Authorization;

use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderException;

class CheckTransitionPayPalAuthorizationStatusService
{
    /**
     * @param string $oldStatus
     * @param string $newStatus
     *
     * @return bool
     *
     * @throws OrderException
     */
    public function checkAvailableStatus($oldStatus, $newStatus)
    {
        if (!is_string($oldStatus)) {
            throw new OrderException(sprintf('Type of oldStatus (%s) is not string', gettype($oldStatus)), OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER);
        }
        if (!is_string($newStatus)) {
            throw new OrderException(sprintf('Type of newStatus (%s) is not string', gettype($newStatus)), OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER);
        }
        if (!key_exists($oldStatus, PayPalAuthorizationStatus::TRANSITION_AVAILABLE)) {
            throw new OrderException(sprintf('The oldStatus doesn\'t exist (%s)', $oldStatus), OrderException::STATUS_CHECK_AVAILABLE_BAD_PARAMETER);
        }

        return in_array($newStatus, PayPalAuthorizationStatus::TRANSITION_AVAILABLE[$oldStatus]);
    }
}
