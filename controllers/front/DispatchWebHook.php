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

use PrestaShop\Module\PrestashopCheckout\webHookValidation;

class ps_checkoutDispatchWebHookModuleFrontController extends ModuleFrontController
{
    const PS_CHECKOUT_IP_PROD = '0.0.0.0';
    const PS_CHECKOUT_IP_DEV = '172.17.0.2';

    /**
     * Contains the summary of the event, coming from Paypal
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $category;

    /**
     * Contains the Event Type coming from PSL
     * @var string
     */
    private $eventType;

    /**
     * Get all the 
     * @var array
     */
    private $resource;

    /**
     * Order Id coming from Paypal
     * @var int
     */
    private $orderId;

    /**
     * Id coming from PSL
     * @var int
     */
    private $shopId;

    /**
     * Id coming from Paypal
     * @var int
     */
    private $merchantId;

    /**
     * Id coming from Firebase
     * @var int
     */
    private $firebaseId;


    public function initContent()
    {
        // Check IP address whitelist
        if (!$this->checkIPWhitelist()) {
            return false;
        }
        
        $payload = json_decode(\Tools::getValue('payload'));
        $errors = (new webHookValidation)->validate($payload);

        // If there is errors, return them
        if (is_array($errors)) {
            throw new \PrestaShopException($errors);
        }
        
        $this->setAtributesValues($payload);

        // Check if have execution permissions
        if (!$this->checkExecutionPermissions()) {
            return false;
        }
        
        $this->dispatchWebHook($payload);
    }

    /**
     * Set Attributes values from the payload
     *
     * @param  array $payload
     *
     * @return void
     */
    private function setAtributesValues($payload)
    {
        $this->shopId = (int)$payload['Shop-Id'];
        $this->merchantId = (int)$payload['Merchant-Id'];
        $this->firebaseId = (int)$payload['Psx-Id'];
        $this->summary = (string)$payload['summary'];
        $this->category = (string)$payload['category'];
        $this->eventType = (string)$payload['eventType'];
        $this->resource = (array)$payload['resource'];
    }

    /**
     * Check if the calling IP is in the whitelist
     *
     * @return bool
     */
    private function checkIPWhitelist()
    {
        $sourceIp = $_SERVER['REMOTE_ADDR'];

        // check white list
        if (!in_array($sourceIp, array(PS_CHECKOUT_IP_PROD, PS_CHECKOUT_IP_DEV))) {
            return false;
        }

        return true;
    }

    /**
     * Check the IP whitelist and Shop, Merchant and Psx Ids
     *
     * @return bool
     */
    private function checkExecutionPermissions()
    {
        $localShopId = $this->module->configurationList['PS_CHECKOUT_SHOP_UUID_V4'];
        $localMerchantId = $this->module->configurationList['PS_CHECKOUT_PAYPAL_ID_MERCHANT'];
        $localFirebaseId = $this->module->configurationList['PS_CHECKOUT_FIREBASE_ID_TOKEN'];

        if ($this->shopId !== $localShopId) {
            return false;
        }

        if ($this->merchantId !== $localMerchantId) {
            return false;
        }

        if ($this->firebaseId !== $localFirebaseId) {
            return false;
        }

        return true;
    }

    /**
     * Dispatch the web Hook according to the category
     *
     * @param array $payLoad
     * 
     * @return void
     */
    private function dispatchWebHook($payload)
    {
        if ('ShopNotificationMerchantAccount' === $this->category) {
            // do merchant management
        }

        if ('ShopNotificationOrderChange' === $this->category) {
            $orderError = (new webHookValidation)->validateOrderId($payload['orderId']);

            if (is_string($orderError)) {
                throw new \PrestaShopException($orderError);
            }

            // do order change
        }
    }
    
}
