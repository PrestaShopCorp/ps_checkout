<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2019 PrestaShop SA
* @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
* International Registered Trademark & Property of PrestaShop SA
**/

namespace PrestaShop\Module\PrestashopCheckout;

class WebHookValidation
{
    const ALLOWED_CATEGORIES = array(
        'ShopNotificationMerchantAccount',
        'ShopNotificationOrderChange'
    );

    /**
     * Validates the webHook datas
     * 
     * @param array $payload
     * 
     * @return array Error lists, bool if ok
     */
    public function validate($payload)
    {
        $errors = array();

        if (empty($payload)) {
            return $errors['payload'] = 'Payload can\'t be empty';
        }

        if (empty($payload['Shop-Id'])) {
            $errors['Shop-Id'] = 'Shop-Id can\'t be empty';
        }

        if (empty($payload['Merchant-Id'])) {
            $errors['Merchant-Id'] = 'Merchant-Id can\'t be empty';
        }

        if (empty($payload['Psx-Id'])) {
            $errors['Psx-Id'] = 'Psx-Id can\'t be empty';
        }

        if (!in_array($payload['category'], self::ALLOWED_CATEGORIES)) {
            $errors['category'] = sprintf('Category must be one of these values: %s', implode(', ', self::ALLOWED_CATEGORIES));
        }

        if (!is_string($payload['eventType'])) {
            $errors['eventType'] = 'eventType must be a string';
        }

        if (empty($payload['eventType'])) {
            $errors['eventType'] = 'eventType can\'t be empty';
        }        

        if (!is_array($payload['resource'])) {
            $errors['resource'] = 'resource must be an array';
        }

        if (empty($payload['resource'])) {
            $errors['resource'] = 'resource \'t be empty';
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Validates the webHook data "Order Id"
     *
     * @param  array $payload
     *
     * @return string|bool
     */
    public function validateOrderId($payload)
    {
        if (empty($payload['orderId'])) {
            return 'orderId can\'t be empty';
        }
        
        return true;
    }
}
