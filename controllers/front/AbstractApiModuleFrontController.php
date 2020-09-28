<?php

use PrestaShop\Module\PrestashopCheckout\Api\APIResponseFormatter;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

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

abstract class AbstractApiModuleFrontController extends ModuleFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @throws PsCheckoutException
     */
    protected function checkPrerequisite()
    {
        if (false === $this->checkIfContextIsValid()) {
            throw new PsCheckoutException('The context is not valid', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
        }

        if (false === $this->checkIfPaymentOptionIsAvailable()) {
            throw new PsCheckoutException('This payment method is not available.', PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE);
        }

        $customer = new Customer($this->context->cart->id_customer);

        if (false === Validate::isLoadedObject($customer)) {
            throw new PsCheckoutException('Customer is not loaded yet');
        }
    }

    /**
     * @return mixed
     *
     * @throws PsCheckoutException
     */
    protected function getDatasFromRequest()
    {
        $request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();

        $bodyContent = $request->getContent();

        if (empty($bodyContent)) {
            throw new PsCheckoutException('Body cannot be empty', PsCheckoutException::PSCHECKOUT_VALIDATE_BODY_EMPTY);
        }

        $bodyValues = json_decode($bodyContent, true);

        if (empty($bodyValues)) {
            throw new PsCheckoutException('Body cannot be empty', PsCheckoutException::PSCHECKOUT_VALIDATE_BODY_EMPTY);
        }
        return $bodyValues;
    }

    /**
     * @param Exception $exception
     * @param string $exceptionMessageForCustomer
     */
    protected function sendInternalServerError(Exception $exception, $exceptionMessageForCustomer)
    {
        /** @var APIResponseFormatter $apiResponse */
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
        /** @var APIResponseFormatter $apiResponse */
        $apiResponse = $this->module->getService('ps_checkout.api.response');
        $response = $apiResponse->sendBadRequestError($exception);
        $response->send();
        exit;
    }

    /**
     * @param array $data
     */
    protected function sendOkResponse($data)
    {
        /** @var APIResponseFormatter $apiResponse */
        $apiResponse = $this->module->getService('ps_checkout.api.response');
        $response = $apiResponse->sendOkResponse($data);
        $response->send();
        exit;
    }

    /**
     * Check if the context is valid
     *
     * @return bool
     */
    protected function checkIfContextIsValid()
    {
        return true === Validate::isLoadedObject($this->context->cart)
            && true === Validate::isUnsignedInt($this->context->cart->id_customer)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_delivery)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_invoice);
    }

    /**
     * Check that this payment option is still available in case the customer changed
     * his address just before the end of the checkout process
     *
     * @return bool
     */
    protected function checkIfPaymentOptionIsAvailable()
    {
        $modules = Module::getPaymentModules();

        if (empty($modules)) {
            return false;
        }

        foreach ($modules as $module) {
            if (isset($module['name']) && $this->module->name === $module['name']) {
                return true;
            }
        }

        return false;
    }
}
