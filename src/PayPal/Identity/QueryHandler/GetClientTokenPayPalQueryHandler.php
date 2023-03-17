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
use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartException;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Event\PayPalClientTokenUpdatedEvent;
use PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Query\GetClientTokenPayPalQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Identity\Query\GetClientTokenPayPalQueryResult;

class GetClientTokenPayPalQueryHandler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param GetClientTokenPayPalQuery $clientTokenPayPalQuery
     *
     * @return GetClientTokenPayPalQueryResult
     *
     * @throws CartException
     * @throws PsCheckoutException
     */
    public function handle(GetClientTokenPayPalQuery $clientTokenPayPalQuery)
    {
        $createdAt = time();
        $context = Context::getContext();
        $merchantId = Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT', null, null, $context->shop->id);
        $customerId = $clientTokenPayPalQuery->getCustomerId()->getValue();
        $apiOrder = new Order($context->link);
        $response = $apiOrder->getClientToken($merchantId, $customerId);

        $this->eventDispatcher->dispatch(
            new PayPalClientTokenUpdatedEvent(
                $clientTokenPayPalQuery->getCartId()->getValue(),
                $response['client_token'],
                $response['id_token'],
                (int) $response['expires_in'],
                $createdAt
            )
        );

        return new GetClientTokenPayPalQueryResult(
            $response['client_token'],
            $response['id_token'],
            (int) $response['expires_in'],
            $createdAt
        );
    }
}
