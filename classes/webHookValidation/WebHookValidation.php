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
        'ShopNotificationOrderChange',
    );

    /**
     * Validates the webHook header datas
     *
     * @param array $headerValues
     *
     * @return array|bool Error lists, bool if ok
     */
    public function validateHeaderDatas(array $headerValues)
    {
        $errors = array();

        if (empty($headerValues)) {
            return $errors[] = 'Header can\'t be empty';
        }

        if (empty($headerValues['Shop-Id'])) {
            $errors[] = 'Shop-Id can\'t be empty';
        }

        if (empty($headerValues['Merchant-Id'])) {
            $errors[] = 'Merchant-Id can\'t be empty';
        }

        if (empty($headerValues['Psx-Id'])) {
            $errors[] = 'Psx-Id can\'t be empty';
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Validates the webHook resource datas
     *
     * @param array $resource
     *
     * @return array|bool Error lists, bool if ok
     */
    public function validateRefundResourceValues(array $resource)
    {
        $errors = array();

        if (empty($resource)) {
            return $errors[] = 'Resource can\'t be empty';
        }

        if (empty($resource['amount'])) {
            $errors[] = 'Amount can\'t be empty';
        }

        if (empty($resource['amount']['value'])) {
            $errors[] = 'Amount value can\'t be empty';
        }

        if (0 >= $resource['amount']['value']) {
            $errors[] = 'Amount value must be higher than 0';
        }

        if (empty($resource['amount']['currency'])) {
            $errors[] = 'Amount currency can\'t be empty';
        }

        if (empty($resource['orderId'])) {
            $errors[] = 'OrderId can\'t be empty';
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }

    /**
     * Validates the webHook orderId
     *
     * @param int $orderId
     *
     * @return array|bool Error lists, bool if ok
     */
    public function validateRefundOrderIdValue($orderId)
    {
        $errors = array();

        if (!is_int($orderId)) {
            return $errors[] = 'orderId must be an integer';
        }

        if (!empty($errors)) {
            return $errors;
        }

        return true;
    }
}
