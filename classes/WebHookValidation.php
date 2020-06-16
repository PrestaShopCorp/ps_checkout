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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class WebHookValidation
{
    /**
     * Validates the webHook header data
     *
     * @param array $headerValues
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function validateHeaderDatas(array $headerValues)
    {
        if (empty($headerValues)) {
            throw new PsCheckoutException('Header can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_HEADER_EMPTY);
        }

        if (empty($headerValues['Shop-Id'])) {
            throw new PsCheckoutException('Shop-Id can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_SHOP_ID_EMPTY);
        }

        if (empty($headerValues['Merchant-Id'])) {
            throw new PsCheckoutException('Merchant-Id can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_MERCHANT_ID_EMPTY);
        }

        if (empty($headerValues['Psx-Id'])) {
            throw new PsCheckoutException('Psx-Id can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_PSX_ID_EMPTY);
        }

        return true;
    }

    /**
     * Validates the webHook body data
     *
     * @param array $payload
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function validateBodyDatas(array $payload)
    {
        if (empty($payload)) {
            throw new PsCheckoutException('Body can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_BODY_EMPTY);
        }

        if (empty($payload['eventType'])) {
            throw new PsCheckoutException('eventType can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_EVENT_TYPE_EMPTY);
        }

        if (empty($payload['category'])) {
            throw new PsCheckoutException('category can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_CATEGORY_EMPTY);
        }

        if (empty($payload['resource'])) {
            throw new PsCheckoutException('Resource can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_RESOURCE_EMPTY);
        }

        return true;
    }

    /**
     * Validates the webHook resource data
     *
     * @param array $resource
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function validateRefundResourceValues(array $resource)
    {
        if (empty($resource)) {
            throw new PsCheckoutException('Resource can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_RESOURCE_EMPTY);
        }

        if (empty($resource['amount']['value'])) {
            throw new PsCheckoutException('Amount value can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_AMOUNT_EMPTY);
        }

        if (0 >= $resource['amount']['value']) {
            throw new PsCheckoutException('Amount value must be higher than 0', PsCheckoutException::PSCHECKOUT_WEBHOOK_AMOUNT_INVALID);
        }

        if (empty($resource['amount']['currency_code'])) {
            throw new PsCheckoutException('Amount currency can\'t be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_CURRENCY_EMPTY);
        }

        return true;
    }

    /**
     * Validates the webHook orderId
     *
     * @param int|string $orderId can be paypal order id (string) or prestashop order id (int)
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function validateRefundOrderIdValue($orderId)
    {
        if (empty($orderId)) {
            throw new PsCheckoutException('orderId must not be empty', PsCheckoutException::PSCHECKOUT_WEBHOOK_ORDER_ID_EMPTY);
        }

        return true;
    }
}
