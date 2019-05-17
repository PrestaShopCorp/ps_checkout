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
    private $psxId;


    public function initContent()
    {
        $payload = json_decode(\Tools::getValue('payload'));

        $errors = (new webHookValidation)->validate($payload);

        // if there is errors, return them
        if (is_array($errors)) {
            throw new \PrestaShopException($errors);
        }
        
        // set attributes
        $this->setAtributesValues($payload);

        // check if have execution permissions
        $this->checkExecutionPermissions();
        
        // dispatch hook
        $this->dispatchWebHook();
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
        $this->psxId = (int)$payload['Psx-Id'];
        $this->summary = (string)$payload['summary'];
        $this->category = (string)$payload['category'];
        $this->eventType = (string)$payload['eventType'];
        $this->resource = (array)$payload['resource'];
    }

    /**
     * Check the IP whitelist and Shop, Merchant and Psx Ids
     *
     * @return bool
     */
    private function checkExecutionPermissions()
    {
        $sourceIp = $_SERVER['REMOTE_ADDR'];

        /*
            @TODO : Get real datas
        */
        $localShopId = 1;
        $localMerchantId = 1;
        $localPsxId = 1;

        // check white list
        if (!in_array($sourceIp, array(PS_CHECKOUT_IP_PROD, PS_CHECKOUT_IP_DEV))) {
            return false;
        }

        if ($this->shopId !== $localShopId) {
            return false;
        }

        if ($this->merchantId !== $localMerchantId) {
            return false;
        }

        if ($this->psxId !== $localPsxId) {
            return false;
        }

        return true;
    }

    /**
     * Dispatch the web Hook according to the category
     *
     * @return void
     */
    private function dispatchWebHook()
    {
        if ('ShopNotificationMerchantAccount' === $this->category) {
            // do merchant management
        }

        if ('ShopNotificationOrderChange' === $this->category) {
            // do update order
        }
    }
    
}
