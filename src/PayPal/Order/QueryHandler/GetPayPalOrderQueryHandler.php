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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\QueryHandler;

use Exception;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQueryResult;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;

class GetPayPalOrderQueryHandler
{
    /**
     * @param GetPayPalOrderQuery $getPayPalOrderQuery
     *
     * @return GetPayPalOrderQueryResult
     *
     * @throws PayPalOrderException
     */
    public function handle(GetPayPalOrderQuery $getPayPalOrderQuery)
    {
        try {
            $orderPayPal = new PaypalOrder($getPayPalOrderQuery->getOrderId());
        } catch (Exception $exception) {
            throw new PayPalOrderException(sprintf('Unable to retrieve PayPal Order #%d', $getPayPalOrderQuery->getOrderId()), PayPalOrderException::CANNOT_RETRIEVE_ORDER, $exception);
        }

        if (!$orderPayPal->isLoaded()) {
            throw new PayPalOrderException(sprintf('No data for PayPal Order #%d', $getPayPalOrderQuery->getOrderId()), PayPalOrderException::EMPTY_ORDER_DATA);
        }

        return new GetPayPalOrderQueryResult($orderPayPal->getOrder());
    }
}
