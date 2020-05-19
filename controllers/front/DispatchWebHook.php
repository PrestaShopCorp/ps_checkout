<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Webhook;
use PrestaShop\Module\PrestashopCheckout\MerchantDispatcher;
use PrestaShop\Module\PrestashopCheckout\OrderDispatcher;
use PrestaShop\Module\PrestashopCheckout\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;
use PrestaShop\Module\PrestashopCheckout\UnauthorizedException;
use PrestaShop\Module\PrestashopCheckout\WebHookNock;
use PrestaShop\Module\PrestashopCheckout\WebHookValidation;

class ps_checkoutDispatchWebHookModuleFrontController extends ModuleFrontController
{
    const PS_CHECKOUT_PAYPAL_ID_LABEL = 'PS_CHECKOUT_PAYPAL_ID_MERCHANT';

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
            $errors = $validationValues->validateHeaderDatas($headerValues);

            // If there is errors, return them
            if (!empty($errors)) {
                throw new UnauthorizedException($errors);
            }

            $this->setAtributesHeaderValues($headerValues);

            $bodyContent = file_get_contents('php://input');

            if (empty($bodyContent)) {
                throw new UnauthorizedException(WebHookValidation::BODY_DATA_ERROR);
            }

            $bodyValues = json_decode($bodyContent, true);

            if (empty($bodyValues)) {
                throw new UnauthorizedException(WebHookValidation::BODY_DATA_ERROR);
            }

            $errors = $validationValues->validateBodyDatas($bodyValues);

            // If there is errors, return them
            if (!empty($errors)) {
                throw new UnauthorizedException($errors);
            }

            if (false === $this->checkPSLSignature($bodyValues)) {
                throw new UnauthorizedException('Invalid PSL signature');
            }

            $this->setAtributesBodyValues($bodyValues);

            // Check if have execution permissions
            if (false === $this->checkExecutionPermissions()) {
                return false;
            }

            return $this->dispatchWebHook();
        } catch (PsCheckoutException $e) {
            (new WebHookNock())->setHeader($e->getHTTPCode(), $e->getArrayMessages());
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
        $context = \Context::getContext();
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
            return getallheaders();
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
     */
    private function checkExecutionPermissions()
    {
        /*
        *   @TODO : Get payload hash to confirm that it's not modified
        */
        $localShopId = (new ShopUuidManager())->getForShop((int) \Context::getContext()->shop->id);

        if ($this->shopId !== $localShopId) {
            throw new UnauthorizedException('shopId wrong');
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
        if ('ShopNotificationMerchantAccount' === $this->payload['category']) {
            return (new MerchantDispatcher())->dispatchEventType(
                ['merchantId' => $this->merchantId]
            );
        }

        if ('ShopNotificationOrderChange' === $this->payload['category']) {
            return (new OrderDispatcher())->dispatchEventType($this->payload);
        }

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
}
