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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\Query;

use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\GooglePay\Builder\GooglePayTransactionInfoBuilder;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;

class GetGooglePayTransactionInfoQueryHandler
{
    /**
     * @var GooglePayTransactionInfoBuilder
     */
    private $builder;

    public function __construct(GooglePayTransactionInfoBuilder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * @param GetGooglePayTransactionInfoQuery $query
     *
     * @return GetGooglePayTransactionInfoQueryResult
     *
     * @throws PsCheckoutException
     */
    public function handle(GetGooglePayTransactionInfoQuery $query)
    {
        $cartPresenter = (new CartPresenter())->present();
        $orderPayloadBuilder = new OrderPayloadBuilder($cartPresenter);

        $orderPayloadBuilder->buildFullPayload();
        $payload = $orderPayloadBuilder->presentPayload();

        return new GetGooglePayTransactionInfoQueryResult($this->builder->buildMinimalTransactionInfoFromPayPalPayload($payload->getArray()));
    }
}
