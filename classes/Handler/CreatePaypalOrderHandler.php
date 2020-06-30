<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Handler;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\ShopContext;

class CreatePaypalOrderHandler
{
    /**
     * Prestashop context object
     *
     * @var \Context
     */
    private $context;

    public function __construct(\Context $context = null)
    {
        if (null === $context) {
            $context = \Context::getContext();
        }

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
    public function handle($expressCheckout = false, $updateOrder = false, $paypalOrderId = null)
    {
        // Present an improved cart in order to create the payload
        $cartPresenter = (new CartPresenter())->present();

        $builder = new OrderPayloadBuilder($cartPresenter);

        // Build full payload in 1.7
        if ((new ShopContext())->isShop17()) {
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
        } else { // if on 1.6 always build minimal payload
            $builder->buildMinimalPayload();
        }

        $payload = $builder->presentPayload()->getJson();

        // Create the paypal order or update it
        if (true === $updateOrder) {
            $paypalOrder = (new Order($this->context->link))->patch($payload);
        } else {
            $paypalOrder = (new Order($this->context->link))->create($payload);
        }

        // Retry with minimal payload when full payload failed (only on 1.7)
        if (substr((string) $paypalOrder['httpCode'], 0, 1) === '4' && (new ShopContext())->isShop17()) {
            $builder->buildMinimalPayload();
            $payload = $builder->presentPayload()->getJson();

            if (true === $updateOrder) {
                $paypalOrder = (new Order($this->context->link))->patch($payload);
            } else {
                $paypalOrder = (new Order($this->context->link))->create($payload);
            }
        }

        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        $module->getLogger()->info(sprintf(
            'Create PayPal Order %s from cart %s',
            $paypalOrder['body']['id'],
            $this->context->cart->id
        ));

        return $paypalOrder;
    }
}
