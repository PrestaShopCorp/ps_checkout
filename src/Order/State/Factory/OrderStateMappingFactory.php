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

namespace PrestaShop\Module\PrestashopCheckout\Order\State\Factory;

use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQueryResult;

class OrderStateMappingFactory
{
    /**
     * @var CommandBusInterface
     */
    private $commandBus;

    /**
     * @param CommandBusInterface $commandBus
     */
    public function __construct($commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @return array
     */
    public function create()
    {
        /** @var GetOrderStateConfigurationQueryResult $queryResult */
        $queryResult = $this->commandBus->handle(new GetOrderStateConfigurationQuery());

        return [
            OrderStateConfigurationKeys::CANCELED => $queryResult->getCanceledStateId(),
            OrderStateConfigurationKeys::PAYMENT_ERROR => $queryResult->getPaymentErrorStateId(),
            OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID => $queryResult->getOutOfStockUnpaidStateId(),
            OrderStateConfigurationKeys::OUT_OF_STOCK_PAID => $queryResult->getOutOfStockPaidStateId(),
            OrderStateConfigurationKeys::PAYMENT_ACCEPTED => $queryResult->getPaymentAcceptedStateId(),
            OrderStateConfigurationKeys::REFUNDED => $queryResult->getRefundedStateId(),
            OrderStateConfigurationKeys::AUTHORIZED => $queryResult->getAuthorizedStateId(),
            OrderStateConfigurationKeys::PARTIALLY_PAID => $queryResult->getPartiallyPaidStateId(),
            OrderStateConfigurationKeys::PARTIALLY_REFUNDED => $queryResult->getPartiallyRefundedStateId(),
            OrderStateConfigurationKeys::WAITING_CAPTURE => $queryResult->getWaitingCaptureStateId(),
            OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT => $queryResult->getWaitingPaymentCardStateId(),
            OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT => $queryResult->getWaitingPaymentLocalStateId(),
            OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT => $queryResult->getWaitingPaymentPayPalStateId(),
        ];
    }
}
