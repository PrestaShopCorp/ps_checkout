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

use PrestaShop\Module\PrestashopCheckout\Cart\Exception\CartNotFoundException;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CreatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShop\Module\PrestashopCheckout\Cart\ValueObject\CartId;


/**
 * This controller receive ajax call to create a PayPal Order
 */
class Ps_CheckoutCreateModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @see FrontController::postProcess()
     *
     * @todo Move logic to a Service
     */
    public function postProcess()
    {
        try {
            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->module->getService('ps_checkout.bus.command');

            // BEGIN Express Checkout
            $bodyValues = [];
            $bodyContent = file_get_contents('php://input');

            if (false === empty($bodyContent)) {
                $bodyValues = json_decode($bodyContent, true);
            }

            if (isset($bodyValues['quantity_wanted'], $bodyValues['id_product'], $bodyValues['id_product_attribute'], $bodyValues['id_customization'])) {
                $cart = new Cart();
                $cart->id_currency = $this->context->currency->id;
                $cart->id_lang = $this->context->language->id;
                $cart->add();
                $isQuantityAdded = $cart->updateQty(
                    (int) $bodyValues['quantity_wanted'],
                    (int) $bodyValues['id_product'],
                    empty($bodyValues['id_product_attribute']) ? null : (int) $bodyValues['id_product_attribute'],
                    empty($bodyValues['id_customization']) ? false : (int) $bodyValues['id_customization'],
                    $operator = 'up'
                );

                if (!$isQuantityAdded) {
                    $this->exitWithResponse([
                        'status' => false,
                        'httpCode' => 400,
                        'body' => [
                            'error' => [
                                'message' => 'Failed to update cart quantity.',
                            ],
                        ],
                        'exceptionCode' => null,
                        'exceptionMessage' => null,
                    ]);
                }

                $cart->update();

                $this->module->getLogger()->info(
                    'Express checkout : Create Cart',
                    [
                        'id_cart' => (int) $cart->id,
                    ]
                );

                $this->context->cart = $cart;
                $this->context->cookie->__set('id_cart', (int) $cart->id);
                $this->context->cookie->write();
            }
            // END Express Checkout
            $cartId = (int) $this->context->cart->id;

            $fundingSource = isset($bodyValues['fundingSource']) ? $bodyValues['fundingSource'] : 'paypal';
            $isHostedFields = isset($bodyValues['isHostedFields']) && $bodyValues['isHostedFields'];
            $isExpressCheckout = (isset($bodyValues['isExpressCheckout']) && $bodyValues['isExpressCheckout']) || empty($this->context->cart->id_address_delivery);

            $commandBus->handle(new CreatePayPalOrderCommand(new CartId($cartId), $fundingSource, $isHostedFields, $isExpressCheckout));

            $order = $commandBus->handle(new GetPayPalOrderQuery(null, new CartId($cartId)));
            $orderId = $order['id'];


            // If we have a PayPal Order Id with a status CREATED or APPROVED or PAYER_ACTION_REQUIRED we mark it as CANCELED and create new one
            // This is needed because cart gets updated so we need to update paypal order too
//            if ($psCheckoutCart && $psCheckoutCart->getPaypalOrderId()) {
//                $psCheckoutCart->paypal_status = PsCheckoutCart::STATUS_CANCELED;
//                $psCheckoutCartRepository->save($psCheckoutCart);
//                $psCheckoutCart = false;
//            }

//            $paypalOrder = $this->module->getService('ps_checkout.handler.order.create_order');
//            $response = $paypalOrder->handle($isExpressCheckout);

//            if (false === $response['status']) {
//                throw new PsCheckoutException($response['exceptionMessage'], (int) $response['exceptionCode']);
//            }
//
//            if (empty($response['body']['id'])) {
//                throw new PsCheckoutException('Paypal order id is missing.', PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING);
//            }

//            $paymentSource = isset($response['body']['payment_source']) ? key($response['body']['payment_source']) : 'paypal';
//            $fundingSource = isset($bodyValues['fundingSource']) ? $bodyValues['fundingSource'] : $paymentSource;
//            $orderId = isset($bodyValues['orderID']) ? $bodyValues['orderID'] : null;
//            $isExpressCheckout = isset($bodyValues['isExpressCheckout']) && $bodyValues['isExpressCheckout'];
//            $isHostedFields = isset($bodyValues['isHostedFields']) && $bodyValues['isHostedFields'];
//            /** @var PayPalConfiguration $configuration */
//            $configuration = $this->module->getService('ps_checkout.paypal.configuration');
//
//            $this->module->getLogger()->info(
//                'PayPal Order created',
//                [
//                    'PayPalOrderId' => $orderId,
//                    'FundingSource' => $fundingSource,
//                    'isExpressCheckout' => $isExpressCheckout,
//                    'isHostedFields' => $isHostedFields,
//                    'id_cart' => (int) $this->context->cart->id,
//                    'amount' => $this->context->cart->getOrderTotal(true, Cart::BOTH),
//                    'environment' => $configuration->getPaymentMode(),
//                    'intent' => $configuration->getIntent(),
//                ]
//            );

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'orderID' => $orderId,
                ],
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (CartNotFoundException $exception) {
            $this->exitWithResponse([
                'httpCode' => 400,
                'body' => 'No cart found.',
            ]);
        } catch (Exception $exception) {
            $this->module->getLogger()->error(
                'CreateController - Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                ]
            );

            $this->exitWithExceptionMessage($exception);
        }
    }
}
