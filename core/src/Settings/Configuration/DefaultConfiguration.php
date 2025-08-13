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

namespace PsCheckout\Core\Settings\Configuration;

class DefaultConfiguration
{
    const DEFAULT_CONFIGURATION_VALUES = [
        'PS_CHECKOUT_INTENT' => 'CAPTURE',
        'PS_CHECKOUT_MODE' => 'LIVE',
        'PS_CHECKOUT_PAYPAL_ID_MERCHANT' => '',
        'PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT' => '',
        'PS_CHECKOUT_PAYPAL_EMAIL_STATUS' => '',
        'PS_CHECKOUT_PAYPAL_PAYMENT_STATUS' => '',
        'PS_CHECKOUT_CARD_PAYMENT_STATUS' => '',
        'PS_CHECKOUT_CARD_PAYMENT_ENABLED' => true,
        'PS_CHECKOUT_EC_ORDER_PAGE' => false,
        'PS_CHECKOUT_EC_CHECKOUT_PAGE' => false,
        'PS_CHECKOUT_EC_PRODUCT_PAGE' => false,
        'PS_CHECKOUT_PAY_IN_4X_PRODUCT_PAGE' => false,
        'PS_CHECKOUT_PAY_IN_4X_ORDER_PAGE' => false,
        'PS_CHECKOUT_PAYPAL_CB_INLINE' => false,
        'PS_CHECKOUT_LOGGER_MAX_FILES' => '30',
        'PS_CHECKOUT_LOGGER_LEVEL' => '100',
        'PS_CHECKOUT_LOGGER_HTTP' => '1',
        'PS_CHECKOUT_LOGGER_HTTP_FORMAT' => 'DEBUG',
        'PS_CHECKOUT_INTEGRATION_DATE' => '2024-04-01',
        'PS_CHECKOUT_WEBHOOK_SECRET' => '',
        'PS_CHECKOUT_DISPLAY_LOGO_PRODUCT' => '1',
        'PS_CHECKOUT_DISPLAY_LOGO_CART' => '1',
        'PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES' => 'SCA_WHEN_REQUIRED',
        'PS_CHECKOUT_PAYPAL_BUTTON' => '{"shape":"pill","label":"pay","color":"gold"}',
    ];
}
