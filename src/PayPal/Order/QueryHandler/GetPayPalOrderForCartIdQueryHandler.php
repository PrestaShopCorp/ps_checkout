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

use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCartIdQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCartIdQueryResult;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use Psr\SimpleCache\CacheInterface;

class GetPayPalOrderForCartIdQueryHandler
{
    /**
     * @var CacheInterface
     */
    private $orderPayPalCache;
    /**
     * @var PsCheckoutCartRepository
     */
    private $checkoutCartRepository;

    public function __construct(CacheInterface $orderPayPalCache, PsCheckoutCartRepository $checkoutCartRepository)
    {
        $this->orderPayPalCache = $orderPayPalCache;
        $this->checkoutCartRepository = $checkoutCartRepository;
    }

    /**
     * @param GetPayPalOrderForCartIdQuery $getPayPalOrderQuery
     *
     * @return GetPayPalOrderForCartIdQueryResult
     *
     * @throws PayPalOrderException
     */
    public function handle(GetPayPalOrderForCartIdQuery $getPayPalOrderQuery)
    {
        $psCheckoutCart = $this->checkoutCartRepository->findOneByCartId($getPayPalOrderQuery->getCartId()->getValue());

        /** @var array $order */
        $order = $this->orderPayPalCache->get($psCheckoutCart->getPaypalOrderId());

        if (empty($order)) {
            throw new PayPalOrderException('PayPal order not found', PayPalOrderException::CANNOT_RETRIEVE_ORDER);
        }

        return new GetPayPalOrderForCartIdQueryResult($order);
    }
}
