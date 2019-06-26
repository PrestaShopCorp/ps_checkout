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
            return $errors['header'] = 'Header can\'t be empty';
        }

        if (empty($headerValues['Shop-Id'])) {
            $errors['header'][] = 'Shop-Id can\'t be empty';
        }

        if (empty($headerValues['Merchant-Id'])) {
            $errors['header'][] = 'Merchant-Id can\'t be empty';
        }

        if (empty($headerValues['Psx-Id'])) {
            $errors['header'][] = 'Psx-Id can\'t be empty';
        }

        if (!in_array($headerValues['category'], self::ALLOWED_CATEGORIES)) {
            $errors['header'][] = sprintf('Category must be one of these values: %s', implode(', ', self::ALLOWED_CATEGORIES));
        }

        if (!is_string($headerValues['eventType'])) {
            $errors['header'][] = 'eventType must be a string';
        }

        if (empty($headerValues['eventType'])) {
            $errors['header'][] = 'eventType can\'t be empty';
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
            return $errors['resource'] = 'Resource can\'t be empty';
        }

        if (empty($resource['amount'])) {
            $errors['amount'][] = 'Amount can\'t be empty';
        }

        if (empty($resource['amount']['value'])) {
            $errors['amount'][] = 'Amount value can\'t be empty';
        }

        if (0 >= $resource['amount']['value']) {
            $errors['amount'][] = 'Amount value must be higher than 0';
        }

        if (empty($resource['amount']['currency'])) {
            $errors['amount'][] = 'Amount currency can\'t be empty';
        }

        if (empty($resource['orderId'])) {
            $errors['amount'][] = 'OrderId can\'t be empty';
        }

        if (!is_int($resource['orderId'])) {
            $errors['amount'][] = 'OrderId must be a int';
        }

        return true;
    }
}
