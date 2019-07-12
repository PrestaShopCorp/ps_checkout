<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
use PrestaShop\Module\PrestashopCheckout\OrderDispatcher;
use PrestaShop\Module\PrestashopCheckout\MerchantDispatcher;
use PrestaShop\Module\PrestashopCheckout\WebHookValidation;
use PrestaShop\Module\PrestashopCheckout\WebHookNock;

class ps_checkoutDispatchWebHookModuleFrontController extends ModuleFrontController
{
    const PSESSENTIALS_DEV_URL = 'out.psessentials-integration.net';
    const PSESSENTIALS_PROD_URL = 'out.psessentials.net';
    const PS_CHECKOUT_SHOP_UID_LABEL = 'PS_CHECKOUT_SHOP_UUID_V4';
    const PS_CHECKOUT_PAYPAL_ID_LABEL = 'PS_CHECKOUT_PAYPAL_ID_MERCHANT';

    /**
     * Id coming from PSL
     *
     * @var int
     */
    private $shopId;

    /**
     * Id coming from Paypal
     *
     * @var int
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
        $headerValues = getallheaders();
        $validationValues = new WebHookValidation();
        $errors = $validationValues->validateHeaderDatas($headerValues);

        // If there is errors, return them
        if (!empty($errors)) {
            (new WebHookNock())->setHeader(401, $errors);

            return false;
        }

        $this->setAtributesHeaderValues($headerValues);

        $bodyValues = \Tools::jsonDecode(file_get_contents('php://input'), true);
        $errors = $validationValues->validateBodyDatas($bodyValues);

        // If there is errors, return them
        if (!empty($errors)) {
            (new WebHookNock())->setHeader(401, $errors);

            return false;
        }

        $this->setAtributesBodyValues($bodyValues);

        // Check if have execution permissions
        if (false === $this->checkExecutionPermissions()) {
            return false;
        }

        return $this->dispatchWebHook();
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
        $this->payload = array(
            'resource' => (array) \Tools::jsonDecode($bodyValues['resource']),
            'eventType' => (string) $bodyValues['eventType'],
            'category' => (string) $bodyValues['category'],
            'summary' => (string) $bodyValues['summary'],
            'orderId' => (string) $bodyValues['orderId'],
        );
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
        $localShopId = \Configuration::get(self::PS_CHECKOUT_SHOP_UID_LABEL);
        $localMerchantId = \Configuration::get(self::PS_CHECKOUT_PAYPAL_ID_LABEL);

        if ($this->shopId !== $localShopId) {
            (new WebHookNock())->setHeader(
                401,
                array(
                    'permissions' => 'shopId wrong',
                )
            );

            return false;
        }

        if ($this->merchantId !== $localMerchantId) {
            (new WebHookNock())->setHeader(
                403,
                array(
                    'permissions' => 'merchantId wrong',
                )
            );

            return false;
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
            $merchantManager = new MerchantDispatcher();

            return $merchantManager->dispatchEventType();
        }

        if ('ShopNotificationOrderChange' === $this->payload['category']) {
            $orderManager = new OrderDispatcher();

            return $orderManager->dispatchEventType(
                $this->payload
            );
        }

        return true;
    }
}
