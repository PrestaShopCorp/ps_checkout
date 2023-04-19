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

use PrestaShop\Module\PrestashopCheckout\Order\State\OrderStateConfiguration;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQuery;
use PrestaShop\Module\PrestashopCheckout\Order\State\Query\GetOrderStateConfigurationQueryResult;

class GetOrderStateConfigurationQueryHandler
{
    public function handle(GetOrderStateConfigurationQuery $query)
    {
        return new GetOrderStateConfigurationQueryResult(
            \Configuration::getGlobalValue(OrderStateConfiguration::CANCELED),
            \Configuration::getGlobalValue(OrderStateConfiguration::PAYMENT_ERROR),
            \Configuration::getGlobalValue(OrderStateConfiguration::OUT_OF_STOCK_UNPAID),
            \Configuration::getGlobalValue(OrderStateConfiguration::OUT_OF_STOCK_PAID),
            \Configuration::getGlobalValue(OrderStateConfiguration::PAYMENT_ACCEPTED),
            \Configuration::getGlobalValue(OrderStateConfiguration::REFUNDED),
            \Configuration::getGlobalValue(OrderStateConfiguration::AUTHORIZED),
            \Configuration::getGlobalValue(OrderStateConfiguration::PARTIALLY_PAID),
            \Configuration::getGlobalValue(OrderStateConfiguration::PARTIALLY_REFUNDED),
            \Configuration::getGlobalValue(OrderStateConfiguration::WAITING_CAPTURE),
            \Configuration::getGlobalValue(OrderStateConfiguration::WAITING_CREDIT_CARD_PAYMENT),
            \Configuration::getGlobalValue(OrderStateConfiguration::WAITING_PAYPAL_PAYMENT),
            \Configuration::getGlobalValue(OrderStateConfiguration::WAITING_LOCAL_PAYMENT)
        );
    }
}
