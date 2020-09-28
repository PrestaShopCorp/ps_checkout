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

class AbstractApiModuleFrontController extends ModuleFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @param Exception $exception
     * @param string $exceptionMessageForCustomer
     */
    protected function sendInternalServerError(Exception $exception, $exceptionMessageForCustomer)
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Api\APIResponseFormatter $apiResponse */
        $apiResponse = $this->module->getService('ps_checkout.api.response');
        $response = $apiResponse->sendInternalServerError($exception, $exceptionMessageForCustomer);
        $response->send();
        exit;
    }

    /**
     * @param Exception $exception
     */
    protected function sendBadRequestError(Exception $exception)
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Api\APIResponseFormatter $apiResponse */
        $apiResponse = $this->module->getService('ps_checkout.api.response');
        $response = $apiResponse->sendBadRequestError($exception);
        $response->send();
        exit;
    }

    /**
     * @param array $response
     */
    protected function sendOkResponse($response)
    {
        $data = [
            'paypal_status' => $response['status'],
            'paypal_order' => $response['paypalOrderId'],
            'paypal_transaction' => $response['transactionIdentifier'],
            'id_cart' => (int) $this->context->cart->id,
            'id_module' => (int) $this->module->id,
            'id_order' => (int) $this->module->currentOrder,
            'secure_key' => $this->context->customer->secure_key,
        ];

        /** @var \PrestaShop\Module\PrestashopCheckout\Api\APIResponseFormatter $apiResponse */
        $apiResponse = $this->module->getService('ps_checkout.api.response');
        $response = $apiResponse->sendOkResponse($data);
        $response->send();
        exit;
    }
}
