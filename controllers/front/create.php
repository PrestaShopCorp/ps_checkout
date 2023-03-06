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
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\CreatePayPalOrderCommand;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCartIdQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForCartIdQueryResult;

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
            // BEGIN Express Checkout
            $bodyValues = [];
            $bodyContent = file_get_contents('php://input');

            if (!empty($bodyContent)) {
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

            $vaultId = isset($bodyValues['vaultId']) ? $bodyValues['vaultId'] : null;
            $vault = isset($bodyValues['vault']) && $bodyValues['vault'];
            $favorite = isset($bodyValues['favorite']) && $bodyValues['favorite'];

            $fundingSource = isset($bodyValues['fundingSource']) ? $bodyValues['fundingSource'] : 'paypal';
            $isCardFields = isset($bodyValues['isCardFields']) && $bodyValues['isCardFields'];
            $isExpressCheckout = (isset($bodyValues['isExpressCheckout']) && $bodyValues['isExpressCheckout']) || empty($this->context->cart->id_address_delivery);

            if ($isExpressCheckout) {
                $psCheckoutCartCollection = new PrestaShopCollection(PsCheckoutCart::class);
                $psCheckoutCartCollection->where('id_cart', '=', (int) $cartId);
                $psCheckoutCartCollection->where('isExpressCheckout', '=', '1');
                $psCheckoutCartCollection->where('paypal_status', 'IN', [PsCheckoutCart::STATUS_CREATED, PsCheckoutCart::STATUS_APPROVED, PsCheckoutCart::STATUS_PAYER_ACTION_REQUIRED]);
                $psCheckoutCartCollection->where('date_upd', '>', date('Y-m-d H:i:s', strtotime('-1 hour')));
                $psCheckoutCartCollection->orderBy('date_upd', 'desc');
                /** @var PsCheckoutCart|false $psCheckoutCart */
                $psCheckoutCart = $psCheckoutCartCollection->getFirst();
                if ($psCheckoutCart) {
                    $this->exitWithResponse([
                        'status' => true,
                        'httpCode' => 200,
                        'body' => [
                            'orderID' => $psCheckoutCart->paypal_order,
                        ],
                        'exceptionCode' => null,
                        'exceptionMessage' => null,
                    ]);
                }
            }

            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->module->getService('ps_checkout.bus.command');
            $commandBus->handle(new CreatePayPalOrderCommand($cartId, $fundingSource, $isCardFields, $isExpressCheckout, $vaultId, $favorite, $vault));

            /** @var GetPayPalOrderForCartIdQueryResult $getPayPalOrderForCartIdQueryResult */
            $getPayPalOrderForCartIdQueryResult = $commandBus->handle(new GetPayPalOrderForCartIdQuery($cartId));
            $order = $getPayPalOrderForCartIdQueryResult->getOrder();

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'orderID' => $order['id'],
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
