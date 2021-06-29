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

use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;

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
                throw new PsCheckoutException('No cart found.', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            $bodyContent = file_get_contents('php://input');

            if (empty($bodyContent)) {
                throw new PsCheckoutException('Payload invalid', PsCheckoutException::PSCHECKOUT_WEBHOOK_BODY_EMPTY);
            }

            $bodyValues = json_decode($bodyContent, true);

            if (empty($bodyValues)) {
                throw new PsCheckoutException('Payload invalid', PsCheckoutException::PSCHECKOUT_WEBHOOK_BODY_EMPTY);
            }

            /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->module->getService('ps_checkout.repository.pscheckoutcart');

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $this->context->cart->id);

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new PsCheckoutCart();
                $psCheckoutCart->id_cart = (int) $this->context->cart->id;
            }

            if (false === empty($bodyValues['fundingSource'])) {
                $psCheckoutCart->paypal_funding = $bodyValues['fundingSource'];
            }

            $psCheckoutCart->isExpressCheckout = isset($bodyValues['isExpressCheckout']) ? (bool) $bodyValues['isExpressCheckout'] : false;
            $psCheckoutCart->isHostedFields = isset($bodyValues['isHostedFields']) ? (bool) $bodyValues['isHostedFields'] : false;
            $psCheckoutCartRepository->save($psCheckoutCart);

            if (false === empty($psCheckoutCart->paypal_order)) {
                $isExpressCheckout = (isset($bodyValues['express_checkout']) && $bodyValues['express_checkout']) || empty($this->context->cart->id_address_delivery);
                $paypalOrder = new CreatePaypalOrderHandler($this->context);
                $response = $paypalOrder->handle($isExpressCheckout, true, $psCheckoutCart->paypal_order);

                if (false === $response['status']) {
                    throw new PsCheckoutException(sprintf('Unable to patch PayPal Order - Exception %s : %s', $response['exceptionCode'], $response['exceptionMessage']), PsCheckoutException::PSCHECKOUT_UPDATE_ORDER_HANDLE_ERROR);
                }
            }

            $this->exitWithResponse([
                'status' => true,
                'httpCode' => 200,
                'body' => $bodyValues,
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            $this->handleExceptionSendingToSentry($exception);

            /* @var \Psr\Log\LoggerInterface logger */
            $logger = $this->module->getService('ps_checkout.logger');
            $logger->error(
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
