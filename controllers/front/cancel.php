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
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;

/**
 * This controller receive ajax call on customer canceled payment
 */
class Ps_CheckoutCancelModuleFrontController extends AbstractFrontController
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

            $orderId = isset($bodyValues['orderID']) ? $bodyValues['orderID'] : null;
            $fundingSource = isset($bodyValues['fundingSource']) ? $bodyValues['fundingSource'] : null;
            $isExpressCheckout = isset($bodyValues['isExpressCheckout']) && $bodyValues['isExpressCheckout'];
            $isHostedFields = isset($bodyValues['isHostedFields']) && $bodyValues['isHostedFields'];
            $reason = isset($bodyValues['reason']) ? Tools::safeOutput($bodyValues['reason']) : null;
            $error = isset($bodyValues['error'])
                ? Tools::safeOutput(is_string($bodyValues['error']) ? $bodyValues['error'] : json_encode($bodyValues['error']))
                : null;

            if ($orderId) {
                /** @var CommandBusInterface $commandBus */
                $commandBus = $this->module->getService('ps_checkout.bus.command');

                $commandBus->handle(new CancelCheckoutCommand(
                    $this->context->cart->id,
                    $orderId,
                    PsCheckoutCart::STATUS_CANCELED,
                    $fundingSource,
                    $isExpressCheckout,
                    $isHostedFields
                ));
            }

            $this->module->getLogger()->log(
                $error ? 400 : 200,
                'Customer canceled payment',
                [
                    'PayPalOrderId' => $orderId,
                    'FundingSource' => $fundingSource,
                    'id_cart' => $this->context->cart->id,
                    'isExpressCheckout' => $isExpressCheckout,
                    'isHostedFields' => $isHostedFields,
                    'reason' => $reason,
                    'error' => $error,
                ]
            );

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => $bodyValues,
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            $this->module->getLogger()->error(
                'CancelController - Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                ]
            );

            $this->exitWithExceptionMessage($exception);
        }
    }
}
