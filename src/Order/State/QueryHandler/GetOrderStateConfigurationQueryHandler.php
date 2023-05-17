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

namespace PrestaShop\Module\PrestashopCheckout\Order\State\QueryHandler;

use PrestaShop\Module\PrestashopCheckout\Order\State\Exception\OrderStateException;
use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfigurationKeys;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQueryResult;
use PrestaShop\Module\PrestashopCheckout\Order\State\ValueObject\OrderStateConfiguration;

class GetOrderStateConfigurationQueryHandler
{
    /**
     * @param GetOrderStateConfigurationQuery $query
     *
     * @return GetOrderStateConfigurationQueryResult
     *
     * @throws OrderStateException
     */
    public function handle(GetOrderStateConfigurationQuery $query)
    {
        return new GetOrderStateConfigurationQueryResult(
            new OrderStateConfiguration(OrderStateConfigurationKeys::CANCELED, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::CANCELED)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::PAYMENT_ERROR, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::PAYMENT_ERROR)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::OUT_OF_STOCK_UNPAID)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::OUT_OF_STOCK_PAID, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::OUT_OF_STOCK_PAID)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::PAYMENT_ACCEPTED, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::PAYMENT_ACCEPTED)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::REFUNDED, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::REFUNDED)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::AUTHORIZED, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::AUTHORIZED)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::PARTIALLY_PAID, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::PARTIALLY_PAID)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::PARTIALLY_REFUNDED, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::PARTIALLY_REFUNDED)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::WAITING_CAPTURE, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::WAITING_CAPTURE)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::WAITING_CREDIT_CARD_PAYMENT)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::WAITING_PAYPAL_PAYMENT)),
            new OrderStateConfiguration(OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT, (int) \Configuration::getGlobalValue(OrderStateConfigurationKeys::WAITING_LOCAL_PAYMENT))
        );
    }
}
