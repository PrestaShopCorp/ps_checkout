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
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\Response\ResponseApiHandler;
use PrestaShop\Module\PrestashopCheckout\Http\MaaslandHttpClient;
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

    public function __construct(Context $context)
    {
        $this->context = $context;
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
    public function handle($expressCheckout = false, $isCardPayment = false, $updateOrder = false, $paypalOrderId = null)
    {
        // Present an improved cart in order to create the payload
        $cartPresenter = (new CartPresenter())->present();

        $builder = new OrderPayloadBuilder($cartPresenter, true);

        /** @var Ps_checkout $module */
        $module = Module::getInstanceByName('ps_checkout');

        /** @var ShopContext $shopContext */
        $shopContext = $module->getService(ShopContext::class);

        $builder->setIsCard($isCardPayment);

        // enable express checkout mode if in express checkout
        $builder->setExpressCheckout($expressCheckout);

        // enable update mode if we build an order for update it
        $builder->setIsUpdate($updateOrder);
        if ($updateOrder) {
            $builder->setPaypalOrderId($paypalOrderId);
        }

        if ($shopContext->isShop17()) {
            // Build full payload in 1.7
            $builder->buildFullPayload();
        } else {
            // if on 1.6 always build minimal payload
            $builder->buildMinimalPayload();
        }

        $payload = $builder->presentPayload()->getArray();

        /** @var MaaslandHttpClient $checkoutHttpClient */
        $checkoutHttpClient = $module->getService(MaaslandHttpClient::class);

        // Create the paypal order or update it
        try {
            if (true === $updateOrder) {
                $response = $checkoutHttpClient->updateOrder($payload);
            } else {
                $response = $checkoutHttpClient->createOrder($payload);
            }
        } catch (PayPalException $exception) {
            $previousException = $exception->getPrevious();
            $response = method_exists($previousException, 'getResponse') ? $previousException->getResponse() : null;
            // Retry with minimal payload when full payload failed (only on 1.7)
            if ($response && substr((string) $response->getStatusCode(), 0, 1) === '4' && $shopContext->isShop17()) {
                $builder->buildMinimalPayload();
                $payload = $builder->presentPayload()->getArray();

                if (true === $updateOrder) {
                    $response = $checkoutHttpClient->updateOrder($payload);
                } else {
                    $response = $checkoutHttpClient->createOrder($payload);
                }
            } else {
                throw $exception;
            }
        }

        $responseHandler = new ResponseApiHandler();

        return $responseHandler->handleResponse($response);
    }
}
