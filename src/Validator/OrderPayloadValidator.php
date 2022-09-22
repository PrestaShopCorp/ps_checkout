<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\Validator;

use PrestaShop\Module\PrestashopCheckout\Exception\OrderValidationException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalCountryProvider;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalCurrencyProvider;

class OrderPayloadValidator
{
    /**
     * @var PayPalCountryProvider
     */
    private $countryPayPalProvider;

    /**
     * @var PayPalCurrencyProvider
     */
    private $currencyPayPalProvider;

    public function __construct()
    {
        $this->countryPayPalProvider = new PayPalCountryProvider();
        $this->currencyPayPalProvider = new PayPalCurrencyProvider();
    }

    public function checkBaseNode($node)
    {
        if ($node['intent'] != 'CAPTURE') {
            throw new OrderValidationException(sprintf('Passed intent %s is unsupported', $node['intent']), OrderValidationException::PSCHECKOUT_INVALID_INTENT);
        }

        try {
            $this->currencyPayPalProvider->getByCode($node['amount']['currency_code']);
        } catch (PsCheckoutException $exception) {
            throw new OrderValidationException($exception->getMessage(), OrderValidationException::PSCHECKOUT_CURRENCY_CODE_INVALID, $exception);
        }

        if ($node['amount']['value'] <= 0) {
            throw new OrderValidationException(sprintf('Passed amount %s is less or equal to zero', $node['amount']['value']), OrderValidationException::PSCHECKOUT_AMOUNT_EMPTY);
        }

        if (empty($node['payee']['merchant_id'])) {
            throw new OrderValidationException(sprintf('Passed merchant id %s is invalid', $node['payee']['merchant_id']), OrderValidationException::PSCHECKOUT_MERCHANT_ID_INVALID);
        }
    }

    public function checkShippingNode($node)
    {
        if (empty($node['shipping']['name']['full_name'])) {
            throw new OrderValidationException('shipping name is empty', OrderValidationException::PSCHECKOUT_SHIPPING_NAME_INVALID);
        }

        if (empty($node['shipping']['address']['address_line_1'])) {
            throw new OrderValidationException('shipping address is empty', OrderValidationException::PSCHECKOUT_SHIPPING_ADDRESS_INVALID);
        }

        try {
            $country = $this->countryPayPalProvider->getByCode($node['shipping']['address']['country_code']);
        } catch (PsCheckoutException $exception) {
            throw new OrderValidationException($exception->getMessage(), OrderValidationException::PSCHECKOUT_SHIPPING_COUNTRY_CODE_INVALID, $exception);
        }

        if (empty($node['shipping']['address']['admin_area_1']) && $country->isStateRequired()) {
            throw new OrderValidationException('shipping state is empty', OrderValidationException::PSCHECKOUT_SHIPPING_CITY_INVALID);
        }

        if (empty($node['shipping']['address']['admin_area_2']) && $country->isCityRequired()) {
            throw new OrderValidationException('shipping city is empty', OrderValidationException::PSCHECKOUT_SHIPPING_CITY_INVALID);
        }

        if (empty($node['shipping']['address']['postal_code']) && $country->isZipCodeRequired()) {
            throw new OrderValidationException('shipping postal code is empty', OrderValidationException::PSCHECKOUT_SHIPPING_POSTAL_CODE_INVALID);
        }
    }

    public function checkPayerNode($node)
    {
        if (empty($node['payer']['name']['given_name'])) {
            throw new OrderValidationException('payer given name is empty', OrderValidationException::PSCHECKOUT_PAYER_GIVEN_NAME_INVALID);
        }

        if (empty($node['payer']['name']['surname'])) {
            throw new OrderValidationException('payer surname is empty', OrderValidationException::PSCHECKOUT_PAYER_SURNAME_INVALID);
        }

        if (!filter_var($node['payer']['email_address'], FILTER_VALIDATE_EMAIL)) {
            throw new OrderValidationException('payer email_address is empty', OrderValidationException::PSCHECKOUT_PAYER_EMAIL_ADDRESS_INVALID);
        }

        if (empty($node['payer']['address']['address_line_1'])) {
            throw new OrderValidationException('payer address street is empty', OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_STREET_INVALID);
        }

        if (empty($node['payer']['address']['admin_area_2'])) {
            throw new OrderValidationException('payer address city is empty', OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_CITY_INVALID);
        }

        try {
            $country = $this->countryPayPalProvider->getByCode($node['payer']['address']['country_code']);
        } catch (PsCheckoutException $exception) {
            throw new OrderValidationException($exception->getMessage(), OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_COUNTRY_CODE_INVALID, $exception);
        }

        if (empty($node['payer']['address']['postal_code']) && $country->isCityRequired()) {
            throw new OrderValidationException('payer address country code is empty', OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_POSTAL_CODE_INVALID);
        }
    }

    public function checkApplicationContextNode($node)
    {
        if (empty($node['application_context']['brand_name'])) {
            throw new OrderValidationException('application contex brand name is missed', OrderValidationException::PSCHECKOUT_APPLICATION_CONTEXT_BRAND_NAME_INVALID);
        }

        if (empty($node['application_context']['shipping_preference'])) {
            throw new OrderValidationException('application contex shipping preference is missed', OrderValidationException::PSCHECKOUT_APPLICATION_CONTEXT_SHIPPING_PREFERENCE_INVALID);
        }
    }

    public function checkAmountBreakDownNode($node)
    {
        foreach ($node['items'] as $item) {
            if (empty($item['name'])) {
                throw new OrderValidationException('item name is empty', OrderValidationException::PSCHECKOUT_ITEM_INVALID);
            }

            if (empty($item['sku'])) {
                throw new OrderValidationException('item sku is empty', OrderValidationException::PSCHECKOUT_ITEM_ORDER_NOT_FOUND);
            }

            try {
                $this->currencyPayPalProvider->getByCode($item['unit_amount']['currency_code']);
            } catch (PsCheckoutException $exception) {
                throw new OrderValidationException($exception->getMessage(), OrderValidationException::PSCHECKOUT_ITEM_INVALID_AMOUNT_CURRENCY, $exception);
            }

            if (empty($item['unit_amount']['value'])) {
                throw new OrderValidationException('item unit_amount value is empty', OrderValidationException::PSCHECKOUT_ITEM_INVALID_AMOUNT_VALUE);
            }

            if (empty($item['tax']['currency_code'])) {
                throw new OrderValidationException('item tax currency code is empty', OrderValidationException::PSCHECKOUT_ITEM_INVALID_TAX_CURRENCY);
            }

            if (empty($item['tax']['value'])) {
                throw new OrderValidationException('item tax value is empty', OrderValidationException::PSCHECKOUT_ITEM_INVALID_TAX_VALUE);
            }

            if (empty($item['quantity'])) {
                throw new OrderValidationException('item quantity is empty', OrderValidationException::PSCHECKOUT_ITEM_INVALID_QUANTITY);
            }

            if (empty($item['category'])) {
                throw new OrderValidationException('item category is empty', OrderValidationException::PSCHECKOUT_ITEM_INVALID_CATEGORY);
            }
        }
    }
}
