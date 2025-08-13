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

namespace PsCheckout\Infrastructure\Repository;

use Exception;
use OrderHistory;
use PsCheckout\Core\Order\Exception\OrderException;

class OrderHistoryRepository implements OrderHistoryRepositoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function create(int $orderStateId, int $orderId, bool $useExistingPayments): bool
    {
        $history = new OrderHistory();
        $history->id_order = $orderId;

        try {
            $history->changeIdOrderState($orderStateId, $orderId, $useExistingPayments);

            $historyAdded = $history->addWithemail(true);
        } catch (Exception $exception) {
            throw new OrderException(sprintf('Failed to update status or send email when changing OrderState #%d of Order #%d.', $orderStateId, $orderId), OrderException::FAILED_UPDATE_ORDER_STATUS, $exception);
        }

        if (!$historyAdded) {
            throw new OrderException(sprintf('Failed to update status or send email when changing OrderState #%d of Order #%d.', $orderStateId, $orderId), OrderException::FAILED_UPDATE_ORDER_STATUS);
        }

        return $historyAdded;
    }
}
