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

class ps_checkoutDispatchWebHookModuleFrontController extends ModuleFrontController
{
    const PSESSENTIALS_DEV_URL = 'out.psessentials-integration.net';
    const PSESSENTIALS_PROD_URL = 'out.psessentials.net';
    const PS_CHECKOUT_SHOP_UID_LABEL = 'PS_CHECKOUT_SHOP_UUID_V4';
    const PS_CHECKOUT_PAYPAL_ID_LABEL = 'PS_CHECKOUT_PAYPAL_ID_MERCHANT';

    /**
     * Contains the summary of the event, coming from Paypal
     *
     * @var string
     */
    private $summary;

    /**
     * @var string
     */
    private $category;

    /**
     * Contains the Event Type coming from PSL
     *
     * @var string
     */
    private $eventType;

    /**
     * Get all the datas
     *
     * @var array
     */
    private $resource;

    /**
     * Order Id coming from Paypal
     *
     * @var int
     */
    private $orderId;

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

    public function initContent()
    {
        // Check IP address whitelist
        if (false === $this->checkIPWhitelist()) {
            return false;
        }

        $payload = json_decode(\Tools::getValue('payload'));
        $errors = (new WebHookValidation())->validate($payload);

        // If there is errors, return them
        if (is_array($errors)) {
            /*
            * @TODO : Throw array exception
            */
            return false;
        }

        $this->setAtributesValues($payload);

        // Check if have execution permissions
        if (false === $this->checkExecutionPermissions()) {
            return false;
        }

        $this->dispatchWebHook($payload);
    }

    /**
     * Set Attributes values from the payload
     *
     * @param array $payload
     */
    private function setAtributesValues($payload)
    {
        $this->shopId = (int) $payload['Shop-Id'];
        $this->merchantId = (int) $payload['Merchant-Id'];
        $this->firebaseId = (int) $payload['Psx-Id'];
        $this->summary = (string) $payload['summary'];
        $this->category = (string) $payload['category'];
        $this->eventType = (string) $payload['eventType'];
        $this->resource = (array) $payload['resource'];
    }

    /**
     * Check if the calling IP is in the whitelist
     *
     * @return bool
     */
    private function checkIPWhitelist()
    {
        $sourceIp = $this->getSourceIP();

        $devIP = dns_get_record(self::PSESSENTIALS_DEV_URL, 'DNS_TXT');
        $prodIP = dns_get_record(self::PSESSENTIALS_PROD_URL, 'DNS_TXT');

        return $this->findWhiteListInDNS($prodIP, $sourceIp) || $this->findWhiteListInDNS($devIP, $sourceIp);
    }

    /**
     * Get the source IP from the HTTP_CLIENT_IP or HTTP_X_FORWARDED_FOR or REMOTE_ADDR
     *
     * @return string
     */
    private function getSourceIP()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        return $_SERVER['REMOTE_ADDR'];
    }

    /**
     * Check if the called IP can be found in the DNS record
     *
     * @param array $recordDNS
     * @param string $searchIP
     *
     * @return bool
     */
    private function findWhiteListInDNS($recordDNS, $searchIP)
    {
        foreach ($recordDNS as $value) {
            if (stristr($value['txt'], $searchIP)) {
                return true;
            }
        }

        return false;
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
     */
    private function dispatchWebHook($payload)
    {
        if ('ShopNotificationMerchantAccount' === $this->category) {
            $merchantManager = new MerchantDispatcher();
            $merchantManager->dispatchEventType(
                $this->eventType
            );
        }

        if ('ShopNotificationOrderChange' === $this->category) {
            $orderError = (new WebHookValidation())->validateOrderId($payload['orderId']);

            if (true !== $orderError) {
                /*
                * @TODO : Throw array exception
                */
                return false;
            }

            $orderManager = new OrderDispatcher();
            $orderManager->dispatchEventType(
                $this->eventType,
                $this->resource
            );
        }
    }
}
