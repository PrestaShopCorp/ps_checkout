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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Identity\QueryHandler;

use Configuration;
use Context;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Query\GetClientTokenPayPalQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Query\GetClientTokenPayPalQueryResult;

class GetClientTokenPayPalQueryHandler
{
    public function handle(GetClientTokenPayPalQuery $clientTokenPayPalQuery)
    {
        $createdAt = time();
        $context = Context::getContext();
        $merchantId = Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT', null, null, $context->shop->id);
        $customerId = $clientTokenPayPalQuery->getCustomerId();
        $apiOrder = new Order($context->link);
        $response = $apiOrder->getClientToken($merchantId, $customerId);

        return new GetClientTokenPayPalQueryResult(
            $response['client_token'],
            $response['id_token'],
            (int) $response['expires_in'],
            $createdAt
        );
    }
}
