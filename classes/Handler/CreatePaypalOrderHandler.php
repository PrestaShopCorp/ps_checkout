<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout\Handler;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
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

    public function __construct(\Context $context)
    {
        if (null === $context) {
            $context = \Context::getContext();
        }

        $this->context = $context;
    }

    public function handle($expressCheckout = false, $updateOrder = false, $paypalOrderId = null)
    {
        // Present an improved cart in order to create the payload
        $cartPresenter = new CartPresenter($this->context);
        $cartPresenter = $cartPresenter->present();

        $builder = new OrderPayloadBuilder($cartPresenter);

        // Build full payload in 1.7
        if ((new ShopContext())->shopIs17()) {
            // enable express checkout mode if in express checkout
            if ($expressCheckout) {
                $builder->setExpressCheckout(true);
            }

            // enable update mode if we build an order for update it
            if ($updateOrder) {
                $builder->setIsUpdate(true);
                $builder->setPaypalOrderId($paypalOrderId);
            }

            $builder->buildFullPayload();
        } else { // if on 1.6 always build minimal payload
            $builder->buildMinimalPayload();
        }

        $payload = $builder->presentPayload()->getJson();

        // Create the paypal order or update it
        if ($updateOrder) {
            $paypalOrder = (new Order($this->context->link))->patch($payload);
        } else {
            $paypalOrder = (new Order($this->context->link))->create($payload);
        }

        // Retry with minimal payload when full payload failed (only on 1.7)
        if (substr((string) $paypalOrder['httpCode'], 0, 1) === '4' && (new ShopContext())->shopIs17()) {
            $builder->buildMinimalPayload();
            $payload = $builder->presentPayload()->getJson();

            if ($updateOrder) {
                $paypalOrder = (new Order($this->context->link))->patch($payload);
            } else {
                $paypalOrder = (new Order($this->context->link))->create($payload);
            }
        }

        return $paypalOrder;
    }
}
