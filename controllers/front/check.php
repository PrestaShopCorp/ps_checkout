<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;

/**
 * This controller receive ajax call on customer click on a payment button
 */
class Ps_CheckoutCheckModuleFrontController extends ModuleFrontController
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
        header('content-type:application/json');
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

            $psCheckoutCartCollection = new PrestaShopCollection('PsCheckoutCart');
            $psCheckoutCartCollection->where('id_cart', '=', (int) $this->context->cart->id);

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartCollection->getFirst();

            if (false === $psCheckoutCart) {
                throw new PsCheckoutException('Unable to find PayPal data associated to this Cart', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            if (empty($psCheckoutCart->paypal_order)) {
                throw new PsCheckoutException('Unable to find PayPal Order', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            if (false === empty($bodyValues['fundingSource']) && false !== Validate::isGenericName($bodyValues['fundingSource'])) {
                $psCheckoutCart->paypal_funding = $bodyValues['fundingSource'];
                $psCheckoutCart->update();
            }

            $paypalOrder = new CreatePaypalOrderHandler($this->context);
            $response = $paypalOrder->handle(false, true, $psCheckoutCart->paypal_order);

            if (false === $response['status']) {
                throw new PsCheckoutException('Unable to patch PayPal Order', PsCheckoutException::PSCHECKOUT_UPDATE_ORDER_HANDLE_ERROR);
            }

            echo json_encode([
                'status' => true,
                'httpCode' => 200,
                'body' => $bodyValues,
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            header('HTTP/1.0 400 Bad Request');

            echo json_encode([
                'status' => false,
                'httpCode' => 400,
                'body' => '',
                'exceptionCode' => $exception->getCode(),
                'exceptionMessage' => $exception->getMessage(),
            ]);
        }

        exit;
    }
}
