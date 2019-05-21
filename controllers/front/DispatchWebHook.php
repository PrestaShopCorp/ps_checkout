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
use PrestaShop\Module\PrestashopCheckout\Payment;

class ps_checkoutDispatchWebHookModuleFrontController extends ModuleFrontController
{
    const PS_CHECKOUT_IP_PROD = '0.0.0.0';
    const PS_CHECKOUT_IP_DEV = '172.17.0.1';
    const PS_CHECKOUT_SHOP_UID_LABEL = 'PS_CHECKOUT_SHOP_UUID_V4';
    const PS_CHECKOUT_PAYPAL_ID_LABEL = 'PS_CHECKOUT_PAYPAL_ID_MERCHANT';
    const PS_CHECKOUT_MERCHANT_REVOKED = 'MERCHANT.PARTNER-CONSENT.REVOKED';
    const PS_CHECKOUT_MERCHANT_COMPLETED = 'MERCHANT.ONBOARDING.COMPLETED';
    const PS_CHECKOUT_PAYMENT_REVERSED = 'PAYMENT.CAPTURE.REVERSED';
    const PS_CHECKOUT_PAYMENT_REFUNED = 'PAYMENT.CAPTURE.REFUNDED';
    const PS_CHECKOUT_PAYMENT_AUTH_VOIDED = 'PAYMENT.AUTHORIZATION.VOIDED';
    const PS_CHECKOUT_PAYMENT_PENDING = 'PAYMENT.CAPTURE.PENDING';
    const PS_CHECKOUT_PAYMENT_COMPLETED = 'PAYMENT.CAPTURE.COMPLETED';
    const PS_CHECKOUT_PAYMENT_DENIED = 'PAYMENT.CAPTURE.DENIED';


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
     * Get all the datas
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
            /*
            * @TODO : Throw array exception
            */
            return false;
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
        if (!in_array($sourceIp, array(self::PS_CHECKOUT_IP_PROD, self::PS_CHECKOUT_IP_DEV))) {
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
        /*
        *   @TODO : Get payload hash to confirm that it's not modified
        */
        $localShopId = $this->module->configurationList[self::PS_CHECKOUT_SHOP_UID_LABEL];
        $localMerchantId = $this->module->configurationList[self::PS_CHECKOUT_PAYPAL_ID_LABEL];

        if ($this->shopId !== $localShopId) {
            return false;
        }

        if ($this->merchantId !== $localMerchantId) {
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
                /*
                * @TODO : Throw array exception
                */
                return false;
            }
            
            $this->dispatchOrderEventType();
        }
    }

    /**
     * Dispatch the order event type
     *
     * @return void
     */
    private function dispatchOrderEventType()
    {
        $orderAction = new Payment;

        if ($this->eventType === self::PS_CHECKOUT_PAYMENT_REVERSED 
        || $this->eventType === self::PS_CHECKOUT_PAYMENT_REVERSED) {
            $orderAction->refundOrderWebHook(
                $this->resource
            );
        }

        if ($this->eventType === self::PS_CHECKOUT_PAYMENT_PENDING
        || $this->eventType === self::PS_CHECKOUT_PAYMENT_COMPLETED 
        || $this->eventType === self::PS_CHECKOUT_PAYMENT_DENIED) {
            $orderAction->updateStatusOrderWebHook(
                $this->resource
            );
        }
    }
    
}
