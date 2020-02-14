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
        $errors = [];

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
        $errors = [];

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
        $errors = [];

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
        $errors = [];

        if (empty($orderId)) {
            $errors[] = self::ORDER_ERROR;
        }

        return $errors;
    }
}
