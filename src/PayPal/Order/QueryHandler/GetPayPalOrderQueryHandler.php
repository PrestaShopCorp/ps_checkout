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

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQueryResult;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Psr\SimpleCache\CacheInterface;

class GetPayPalOrderQueryHandler
{
    /**
     * @var CacheInterface
     */
    private $orderCache;
    /**
     * @var PsCheckoutCartRepository
     */
    private $checkoutCartRepository;

    public function __construct(CacheInterface $orderCache, PsCheckoutCartRepository $checkoutCartRepository)
    {
        $this->orderCache = $orderCache;
        $this->checkoutCartRepository = $checkoutCartRepository;
    }

    /**
     * @param GetPayPalOrderQuery $query
     *
     * @return GetPayPalOrderQueryResult
     *
     * @throws \PrestaShopException
     */
    public function handle(GetPayPalOrderQuery $query)
    {
        $orderId = !$query->getOrderId()->getValue() ? null : $query->getOrderId()->getValue();

        if (!$orderId) {
            $psCheckoutCart = $this->checkoutCartRepository->findOneByCartId($query->getCartId()->getValue());
            $orderId = $psCheckoutCart->paypal_order;
        }

        return new GetPayPalOrderQueryResult($this->orderCache->get($orderId));
    }
}
