<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Module\PrestashopCheckout\PaymentOptions\PaymentOptionsProvider;

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module version 2.0.0
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_2_0_0($module)
{
    $paymentOptions = (new PaymentOptionsProvider())->createDefaultPaymentOptions();

    return (bool) Configuration::updateGlobalValue('PS_CHECKOUT_LOGGER_MAX_FILES', '15')
        && (bool) Configuration::updateGlobalValue('PS_CHECKOUT_LOGGER_LEVEL', \Monolog\Logger::ERROR)
        && (bool) Configuration::updateGlobalValue('PS_CHECKOUT_LOGGER_HTTP', '1')
        && (bool) Configuration::updateGlobalValue('PS_CHECKOUT_LOGGER_HTTP_FORMAT', 'DEBUG')
        && (bool) Configuration::updateValue(
            'PS_CHECKOUT_PAYMENT_METHODS_ORDER',
            json_encode($paymentOptions->getPaymentOptions()),
            false,
            null,
            (int) \Context::getContext()->shop->id
        );
}
