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

namespace PrestaShop\Module\PrestashopCheckout\Handler;

use Context;
use Module;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\DTO\Orders\CreatePayPalOrderRequest;
use PrestaShop\Module\PrestashopCheckout\DTO\Orders\CreatePayPalOrderResponse;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\PayPalOrderHttpClientInterface;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use Ps_checkout;

class CreatePaypalOrderHandler
{
    /**
     * Prestashop context object
     *
     * @var Context
     */
    private $context;
    /**
     * @var PayPalOrderHttpClientInterface
     */
    private $orderHttpClient;

    public function __construct(PrestaShopContext $context, PayPalOrderHttpClientInterface $orderHttpClient)
    {
        $this->context = $context;
        $this->orderHttpClient = $orderHttpClient;
    }

    /**
     * @param bool $expressCheckout
     * @param bool $updateOrder
     * @param string|null $paypalOrderId
     *
     * @return array
     *
     * @throws PsCheckoutException
     */
    public function handle($expressCheckout = false, $updateOrder = false, $paypalOrderId = null)
    {
        // Present an improved cart in order to create the payload
        $cartPresenter = (new CartPresenter())->present();

        $builder = new OrderPayloadBuilder($cartPresenter, true);

        /** @var Ps_checkout $module */
        $module = Module::getInstanceByName('ps_checkout');

        /** @var ShopContext $shopContext */
        $shopContext = $module->getService('ps_checkout.context.shop');

        // Build full payload in 1.7
        if ($shopContext->isShop17()) {
            // enable express checkout mode if in express checkout
            if (true === $expressCheckout) {
                $builder->setExpressCheckout(true);
            }

            // enable update mode if we build an order for update it
            if (true === $updateOrder) {
                $builder->setIsUpdate(true);
                $builder->setPaypalOrderId($paypalOrderId);
            }

            $builder->buildFullPayload();
        } else {
            // enable express checkout mode if in express checkout
            if (true === $expressCheckout) {
                $builder->setExpressCheckout(true);
            }

            // enable update mode if we build an order for update it
            if (true === $updateOrder) {
                $builder->setIsUpdate(true);
                $builder->setPaypalOrderId($paypalOrderId);
            }

            // if on 1.6 always build minimal payload
            $builder->buildMinimalPayload();
        }

        $payload = $builder->presentPayload()->getArray();

        // Create the paypal order or update it
        if (true === $updateOrder) {
            $paypalOrder = (new Order($this->context->getLink()))->patch($payload);
        } else {
            $paypalOrder = (new Order($this->context->getLink()))->create($payload);
        }

        // Retry with minimal payload when full payload failed (only on 1.7)
        if (substr((string) $paypalOrder['httpCode'], 0, 1) === '4' && $shopContext->isShop17()) {
            $builder->buildMinimalPayload();
            $payload = $builder->presentPayload()->getArray();

            if (true === $updateOrder) {
                $paypalOrder = (new Order($this->context->link))->patch($payload);
            } else {
                $paypalOrder = (new Order($this->context->link))->create($payload);
            }
        }

        return $paypalOrder;
    }

    /**
     * @param CreatePayPalOrderRequest $payload
     *
     * @return CreatePayPalOrderResponse
     *
     * @throws PayPalOrderException
     */
    public function createOrder(CreatePayPalOrderRequest $payload)
    {
        return $this->orderHttpClient->createOrder($payload);
    }
}
