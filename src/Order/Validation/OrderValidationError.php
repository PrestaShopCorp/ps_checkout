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

namespace PrestaShop\Module\PrestashopCheckout\Order\Validation;

use PrestaShop\Module\PrestashopCheckout\Exception\OrderValidationException;
use PrestaShop\Module\PrestashopCheckout\Translations\OrderValidationTranslation;

class OrderValidationError
{
    /**
     * @var array
     */
    private $translations;

    /**
     * @param OrderValidationTranslation $translations
     */
    public function __construct(OrderValidationTranslation $translations)
    {
        $this->translations = $translations->getTranslations();
    }

    /**
     * @param int $exceptionCode
     *
     * @return string
     */
    public function getErrorMessage($exceptionCode)
    {
        switch ($exceptionCode) {
            case OrderValidationException::PSCHECKOUT_PAYER_GIVEN_NAME_INVALID:
                return $this->translations['validation']['payer']['firstname'];
            case OrderValidationException::PSCHECKOUT_PAYER_SURNAME_INVALID:
                return $this->translations['validation']['payer']['surname'];
            case OrderValidationException::PSCHECKOUT_PAYER_EMAIL_ADDRESS_INVALID:
                return $this->translations['validation']['payer']['email'];
            case OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_STREET_INVALID:
                return $this->translations['validation']['payer']['streetAddress'];
            case OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_CITY_INVALID:
                return $this->translations['validation']['payer']['cityAddress'];
            case OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_COUNTRY_CODE_INVALID:
                return $this->translations['validation']['payer']['countryCodeAddress'];
            case OrderValidationException::PSCHECKOUT_PAYER_ADDRESS_POSTAL_CODE_INVALID:
                return $this->translations['validation']['payer']['postalCodeAddress'];
            case OrderValidationException::PSCHECKOUT_APPLICATION_CONTEXT_BRAND_NAME_INVALID:
                return $this->translations['validation']['applicationContext']['brandName'];
            case OrderValidationException::PSCHECKOUT_APPLICATION_CONTEXT_SHIPPING_PREFERENCE_INVALID:
                return $this->translations['validation']['applicationContext']['shippingPreference'];
            case OrderValidationException::PSCHECKOUT_ITEM_INVALID:
                return $this->translations['validation']['item']['name'];
            case OrderValidationException::PSCHECKOUT_ITEM_INVALID_AMOUNT_CURRENCY:
                return $this->translations['validation']['item']['unitCurrency'];
            case OrderValidationException::PSCHECKOUT_ITEM_INVALID_AMOUNT_VALUE:
                return $this->translations['validation']['item']['unitAmount'];
            case OrderValidationException::PSCHECKOUT_ITEM_INVALID_TAX_CURRENCY:
                return $this->translations['validation']['item']['taxCurrency'];
            case OrderValidationException::PSCHECKOUT_ITEM_INVALID_TAX_VALUE:
                return $this->translations['validation']['item']['taxValue'];
            case OrderValidationException::PSCHECKOUT_ITEM_INVALID_QUANTITY:
                return $this->translations['validation']['item']['quantity'];
            case OrderValidationException::PSCHECKOUT_ITEM_INVALID_CATEGORY:
                return $this->translations['validation']['item']['category'];
            case OrderValidationException::PSCHECKOUT_INVALID_INTENT:
                return $this->translations['validation']['general']['intent'];
            case OrderValidationException::PSCHECKOUT_CURRENCY_CODE_INVALID:
                return $this->translations['validation']['general']['currencyCode'];
            case OrderValidationException::PSCHECKOUT_AMOUNT_EMPTY:
                return $this->translations['validation']['general']['amount'];
            case OrderValidationException::PSCHECKOUT_MERCHANT_ID_INVALID:
                return $this->translations['validation']['general']['merchantId'];
            case OrderValidationException::PSCHECKOUT_SHIPPING_NAME_INVALID:
                return $this->translations['validation']['shipping']['name'];
            case OrderValidationException::PSCHECKOUT_SHIPPING_ADDRESS_INVALID:
                return $this->translations['validation']['shipping']['address'];
            case OrderValidationException::PSCHECKOUT_SHIPPING_CITY_INVALID:
                return $this->translations['validation']['shipping']['city'];
            case OrderValidationException::PSCHECKOUT_SHIPPING_COUNTRY_CODE_INVALID:
                return $this->translations['validation']['shipping']['countryCode'];
            case OrderValidationException::PSCHECKOUT_SHIPPING_POSTAL_CODE_INVALID:
                return $this->translations['validation']['shipping']['postalCode'];
            default:
                return $this->translations['validation']['general']['defaultError'];
        }
    }
}
