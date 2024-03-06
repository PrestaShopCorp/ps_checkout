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

use PrestaShop\Module\PrestashopCheckout\Checkout\Command\CancelCheckoutCommand;
use PrestaShop\Module\PrestashopCheckout\Checkout\Command\UpdatePaymentMethodSelectedCommand;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Command\UpdatePayPalOrderCommand;

/**
 * This controller receive ajax call on customer click on a payment button
 */
class Ps_CheckoutCheckModuleFrontController extends AbstractFrontController
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
            if (false === Validate::isLoadedObject($this->context->cart)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'No cart found.',
                ]);
            }

            $bodyContent = file_get_contents('php://input');

            if (empty($bodyContent)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            $bodyValues = json_decode($bodyContent, true);

            if (empty($bodyValues)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Payload invalid',
                ]);
            }

            $cartId = (int) $this->context->cart->id;
            $fundingSource = isset($bodyValues['fundingSource']) ? $bodyValues['fundingSource'] : 'paypal';
            $orderId = isset($bodyValues['orderID']) ? $bodyValues['orderID'] : null;
            $isExpressCheckout = isset($bodyValues['isExpressCheckout']) && $bodyValues['isExpressCheckout'];
            $isHostedFields = isset($bodyValues['isHostedFields']) && $bodyValues['isHostedFields'];

            if (empty($orderId)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Missing PayPal Order Id',
                ]);
            }

            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->module->getService('ps_checkout.bus.command');
            $commandBus->handle(new UpdatePaymentMethodSelectedCommand($cartId, $orderId, $fundingSource, $isExpressCheckout, $isHostedFields));
            try {
                $commandBus->handle(new UpdatePayPalOrderCommand($orderId, $cartId, $fundingSource, $isHostedFields, $isExpressCheckout));
            } catch (Exception $exception) {
                $this->module->getLogger()->error(
                    'Failed to patch PayPal Order',
                    [
                        'PayPalOrderId' => $orderId,
                        'FundingSource' => $fundingSource,
                        'isExpressCheckout' => $isExpressCheckout,
                        'isHostedFields' => $isHostedFields,
                        'id_cart' => (int) $this->context->cart->id,
                    ]
                );
                $commandBus->handle(new CancelCheckoutCommand(
                    $this->context->cart->id,
                    $orderId,
                    PsCheckoutCart::STATUS_CANCELED,
                    $fundingSource,
                    $isExpressCheckout,
                    $isHostedFields
                ));
            }

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => $bodyValues,
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            $this->module->getLogger()->error(
                sprintf(
                    'CheckController - Exception %s : %s',
                    $exception->getCode(),
                    $exception->getMessage()
                )
            );

            $this->exitWithExceptionMessage($exception);
        }
    }
}
