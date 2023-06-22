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

use Monolog\Logger;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Webhook;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Dispatcher\OrderDispatcher;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Order\Exception\OrderNotFoundException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\WebHookValidation;

/**
 * @todo To be refactored
 */
class ps_checkoutDispatchWebHookModuleFrontController extends AbstractFrontController
{
    const PS_CHECKOUT_PAYPAL_ID_LABEL = 'PS_CHECKOUT_PAYPAL_ID_MERCHANT';

    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @var bool If set to true, will be redirected to authentication page
     */
    public $auth = false;

    /**
     * UUID coming from PSL
     *
     * @var string
     */
    private $shopId;

    /**
     * Id coming from Paypal
     *
     * @var string
     */
    private $merchantId;

    /**
     * Id coming from Firebase
     *
     * @var int
     */
    private $firebaseId;

    /**
     * Get all the HTTP body values
     *
     * @var array
     */
    private $payload;

    /**
     * Initialize the webhook script
     *
     * @return bool
     */
    public function display()
    {
        try {
            $headerValues = $this->getHeaderValues();
            $validationValues = new WebHookValidation();
            $validationValues->validateHeaderDatas($headerValues);

            $this->setAtributesHeaderValues($headerValues);

            $bodyContent = file_get_contents('php://input');

            if (empty($bodyContent)) {
                throw new PsCheckoutException('Body can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_BODY_EMPTY);
            }

            $bodyValues = json_decode($bodyContent, true);

            if (empty($bodyValues)) {
                throw new PsCheckoutException('Body can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_BODY_EMPTY);
            }

            $validationValues->validateBodyDatas($bodyValues);
            $this->setAtributesBodyValues($bodyValues);

            if (false === $this->checkPSLSignature($bodyValues)) {
                throw new PsCheckoutException('Invalid PSL signature', PsCheckoutException::PSCHECKOUT_WEBHOOK_PSL_SIGNATURE_INVALID);
            }

            // Check if have execution permissions
            if (false === $this->checkExecutionPermissions()) {
                return false;
            }

            return $this->dispatchWebHook();
        } catch (Exception $exception) {
            $this->handleException($exception);
        }

        return false;
    }

    /**
     * Check if the Webhook comes from the PSL
     *
     * @param array $bodyValues
     *
     * @return bool
     */
    private function checkPSLSignature(array $bodyValues)
    {
        $context = Context::getContext();
        $response = (new Webhook($context->link))->getShopSignature($bodyValues);

        // data return false if no error
        if (200 === $response['httpCode'] && 'VERIFIED' === $response['body']['message']) {
            return true;
        }

        return false;
    }

    /**
     * Get HTTP Headers
     *
     * @return array
     */
    private function getHeaderValues()
    {
        // Not available on nginx
        if (function_exists('getallheaders')) {
            $headers = getallheaders();

            // Ensure we will not return empty values if Request is FORWARDED
            if (
                false === empty($headers['Shop-Id'])
                && false === empty($headers['Merchant-Id'])
                && false === empty($headers['Psx-Id'])
            ) {
                return [
                    'Shop-Id' => $headers['Shop-Id'],
                    'Merchant-Id' => $headers['Merchant-Id'],
                    'Psx-Id' => $headers['Psx-Id'],
                ];
            }
        }

        return [
            'Shop-Id' => isset($_SERVER['HTTP_SHOP_ID']) ? $_SERVER['HTTP_SHOP_ID'] : null,
            'Merchant-Id' => isset($_SERVER['HTTP_MERCHANT_ID']) ? $_SERVER['HTTP_MERCHANT_ID'] : null,
            'Psx-Id' => isset($_SERVER['HTTP_PSX_ID']) ? $_SERVER['HTTP_PSX_ID'] : null,
        ];
    }

    /**
     * Set Header Attributes values from the HTTP request
     *
     * @param array $headerValues
     */
    private function setAtributesHeaderValues(array $headerValues)
    {
        $this->shopId = $headerValues['Shop-Id'];
        $this->merchantId = $headerValues['Merchant-Id'];
        $this->firebaseId = $headerValues['Psx-Id'];
    }

    /**
     * Set Body Attributes values from the payload
     *
     * @param array $bodyValues
     */
    private function setAtributesBodyValues(array $bodyValues)
    {
        $this->payload = [
            'resource' => (array) json_decode($bodyValues['resource'], true),
            'eventType' => (string) $bodyValues['eventType'],
            'category' => (string) $bodyValues['category'],
            'summary' => (string) $bodyValues['summary'],
            'orderId' => (string) $bodyValues['orderId'],
        ];
    }

    /**
     * Check the IP whitelist and Shop, Merchant and Psx Ids
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    private function checkExecutionPermissions()
    {
        /** @var PsAccountRepository $psAccountRepository */
        $psAccountRepository = $this->module->getService('ps_checkout.repository.prestashop.account');

        if ($this->shopId !== $psAccountRepository->getShopUuid()) {
            throw new PsCheckoutException('shopId wrong', PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_INVALID);
        }

        return true;
    }

    /**
     * Dispatch the web Hook according to the category
     *
     * @return bool
     */
    private function dispatchWebHook()
    {
        $this->module->getLogger()->info(
            'DispatchWebHook',
            [
                'merchantId' => $this->merchantId,
                'shopId' => $this->shopId,
                'firebaseId' => $this->firebaseId,
                'payload' => $this->payload,
            ]
        );

        if ('ShopNotificationOrderChange' === $this->payload['category']) {
            return (new OrderDispatcher())->dispatchEventType($this->payload);
        }

        $this->module->getLogger()->info(
            'DispatchWebHook ignored',
            [
                'merchantId' => $this->merchantId,
                'shopId' => $this->shopId,
                'firebaseId' => $this->firebaseId,
                'payload' => $this->payload,
            ]
        );

        return true;
    }

    /**
     * Override displayMaintenancePage to prevent the maintenance page to be displayed
     *
     * @see FrontController::displayMaintenancePage()
     */
    protected function displayMaintenancePage()
    {
        return;
    }

    /**
     * Override displayRestrictedCountryPage to prevent page country is not allowed
     *
     * @see FrontController::displayRestrictedCountryPage()
     */
    protected function displayRestrictedCountryPage()
    {
        return;
    }

    /**
     * Override geolocationManagement to prevent country GEOIP blocking
     *
     * @see FrontController::geolocationManagement()
     *
     * @param Country $defaultCountry
     *
     * @return false
     */
    protected function geolocationManagement($defaultCountry)
    {
        return false;
    }

    /**
     * Override sslRedirection to prevent redirection
     *
     * @see FrontController::sslRedirection()
     */
    protected function sslRedirection()
    {
        return;
    }

    /**
     * Override canonicalRedirection to prevent redirection
     *
     * @see FrontController::canonicalRedirection()
     *
     * @param string $canonical_url
     */
    protected function canonicalRedirection($canonical_url = '')
    {
        return;
    }

    /**
     * @param Exception $exception
     */
    private function handleException(Exception $exception)
    {
        $this->module->getLogger()->log(
            in_array($exception->getCode(), [PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND, OrderNotFoundException::NOT_FOUND], true) ? Logger::NOTICE : Logger::ERROR,
            'Webhook exception ' . $exception->getCode(),
            [
                'merchantId' => $this->merchantId,
                'shopId' => $this->shopId,
                'firebaseId' => $this->firebaseId,
                'payload' => $this->payload,
                'exception' => $exception,
            ]
        );

        http_response_code($this->getHttpCodeFromExceptionCode($exception->getCode()));
        header('X-Robots-Tag: noindex, nofollow');
        header('Content-Type: application/json');

        $bodyReturn = json_encode($exception->getMessage());

        echo $bodyReturn;
    }

    /**
     * @param int $exceptionCode
     *
     * @return int
     */
    private function getHttpCodeFromExceptionCode($exceptionCode)
    {
        $httpCode = 500;

        switch ($exceptionCode) {
            case PsCheckoutException::PRESTASHOP_REFUND_ALREADY_SAVED:
                $httpCode = 200;
                break;
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_PSL_SIGNATURE_INVALID:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_INVALID:
                $httpCode = 401;
                break;
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_AMOUNT_INVALID:
                $httpCode = 406;
                break;
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_HEADER_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_MERCHANT_ID_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_PSX_ID_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_BODY_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_EVENT_TYPE_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_CATEGORY_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_RESOURCE_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_AMOUNT_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_CURRENCY_EMPTY:
            case PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY:
            case PsCheckoutException::PSCHECKOUT_MERCHANT_IDENTIFIER_MISSING:
            case PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND:
            case OrderNotFoundException::NOT_FOUND:
                $httpCode = 422;
                break;
        }

        return $httpCode;
    }
}
