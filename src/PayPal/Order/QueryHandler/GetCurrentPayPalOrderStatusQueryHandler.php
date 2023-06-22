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
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetCurrentPayPalOrderStatusQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetCurrentPayPalOrderStatusQueryResult;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;

class GetCurrentPayPalOrderStatusQueryHandler
{
    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    /**
     * @param PsCheckoutCartRepository $psCheckoutCartRepository
     */
    public function __construct(PsCheckoutCartRepository $psCheckoutCartRepository)
    {
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    /**
     * @param GetCurrentPayPalOrderStatusQuery $getPayPalOrderQuery
     *
     * @return GetCurrentPayPalOrderStatusQueryResult
     *
     * @throws PayPalOrderException
     */
    public function handle(GetCurrentPayPalOrderStatusQuery $getPayPalOrderQuery)
    {
        try {
            $psCheckoutCart = $this->psCheckoutCartRepository->findOneByPayPalOrderId($getPayPalOrderQuery->getOrderPayPalId()->getValue());
        } catch (Exception $exception) {
            throw new PayPalOrderException('Cannot retrieve cart', PayPalOrderException::PRESTASHOP_CART_NOT_FOUND, $exception);
        }

        return new GetCurrentPayPalOrderStatusQueryResult(
            $psCheckoutCart->getPaypalOrderId(),
            $psCheckoutCart->getPaypalStatus()
        );
    }
}
