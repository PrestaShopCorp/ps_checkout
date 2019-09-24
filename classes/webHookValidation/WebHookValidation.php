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
    const HEADER_DATA_ERROR = 'Header can\'t be empty';
    const HEADER_SHOPID_ERROR = 'Shop-Id can\'t be empty';
    const HEADER_MERCHANTID_ERROR = 'Merchant-Id can\'t be empty';
    const HEADER_PSXID_ERROR = 'Psx-Id can\'t be empty';
    const BODY_DATA_ERROR = 'Body can\'t be empty';
    const BODY_EVENTTYPE_ERROR = 'eventType can\'t be empty';
    const BODY_CATEGORY_ERROR = 'category can\'t be empty';
    const BODY_RESOURCE_ERROR = 'Resource can\'t be empty';
    const RESOURCE_DATA_ERROR = 'Resource can\'t be empty';
    const RESOURCE_VALUE_EMPTY_ERROR = 'Amount value can\'t be empty';
    const RESOURCE_VALUE_ZERO_ERROR = 'Amount value must be higher than 0';
    const RESOURCE_CURRENCY_ERROR = 'Amount currency can\'t be empty';
    const ORDER_ERROR = 'orderId must not be empty';

    /**
     * Validates the webHook header data
     *
     * @param array $headerValues
     *
     * @return array
     */
    public function validateHeaderDatas(array $headerValues)
    {
        $errors = array();

        if (empty($headerValues)) {
            $errors[] = self::HEADER_DATA_ERROR;

            return $errors;
        }

        if (empty($headerValues['Shop-Id'])) {
            $errors[] = self::HEADER_SHOPID_ERROR;
        }

        if (empty($headerValues['Merchant-Id'])) {
            $errors[] = self::HEADER_MERCHANTID_ERROR;
        }

        if (empty($headerValues['Psx-Id'])) {
            $errors[] = self::HEADER_PSXID_ERROR;
        }

        return $errors;
    }

    /**
     * Validates the webHook body data
     *
     * @param array $payload
     *
     * @return array
     */
    public function validateBodyDatas(array $payload)
    {
        $errors = array();

        if (empty($payload)) {
            $errors[] = self::BODY_DATA_ERROR;

            return $errors;
        }

        if (empty($payload['eventType'])) {
            $errors[] = self::BODY_EVENTTYPE_ERROR;
        }

        if (empty($payload['category'])) {
            $errors[] = self::BODY_CATEGORY_ERROR;
        }

        if (empty($payload['resource'])) {
            $errors[] = self::BODY_RESOURCE_ERROR;
        }

        return $errors;
    }

    /**
     * Validates the webHook resource data
     *
     * @param array $resource
     *
     * @return array|bool Error lists, bool if ok
     */
    public function validateRefundResourceValues(array $resource)
    {
        $errors = array();

        if (empty($resource)) {
            $errors[] = self::RESOURCE_DATA_ERROR;

            return $errors;
        }

        if (empty($resource['amount']->value)) {
            $errors[] = self::RESOURCE_VALUE_EMPTY_ERROR;
        }

        if (0 >= $resource['amount']->value) {
            $errors[] = self::RESOURCE_VALUE_ZERO_ERROR;
        }

        if (empty($resource['amount']->currency_code)) {
            $errors[] = self::RESOURCE_CURRENCY_ERROR;
        }

        return $errors;
    }

    /**
     * Validates the webHook orderId
     *
     * @param int|string $orderId can be paypal order id (string) or prestashop order id (int)
     *
     * @return array|bool Error lists, bool if ok
     */
    public function validateRefundOrderIdValue($orderId)
    {
        $errors = array();

        if (empty($orderId)) {
            $errors[] = self::ORDER_ERROR;
        }

        return $errors;
    }
}
