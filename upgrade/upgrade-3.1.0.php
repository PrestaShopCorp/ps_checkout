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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module version 3.1.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_3_1_0($module)
{
    $removedFiles = [
        '/src/Api/Firebase/Auth.php',
        '/src/Api/Firebase/AuthFactory.php',
        '/src/Api/Firebase/Client/FirebaseClient.php',
        '/src/Api/Firebase/Client/index.php',
        '/src/Api/Firebase/Token.php',
        '/src/Api/Firebase/index.php',
        '/src/Api/Payment/Dispute.php',
        '/src/Api/Payment/Onboarding.php',
        '/src/Api/Psx/Client/PsxClient.php',
        '/src/Api/Psx/Client/index.php',
        '/src/Api/Psx/Onboarding.php',
        '/src/Api/Psx/index.php',
        '/src/Builder/Payload/OnboardingPayloadBuilder.php',
        '/src/Dispatcher/MerchantDispatcher.php',
        '/src/Environment/FirebaseEnv.php',
        '/src/Environment/PsxEnv.php',
        '/src/Environment/SegmentEnv.php',
        '/src/Environment/SentryEnv.php',
        '/src/Environment/SsoEnv.php',
        '/src/ExpressCheckout.php',
        '/src/Handler/ExceptionHandler.php',
        '/src/Handler/ModuleFilteredRavenClient.php',
        '/src/PayPal/PayPalMerchantIntegrationProvider.php',
        '/src/Presenter/Store/Modules/FirebaseModule.php',
        '/src/Presenter/Store/Modules/PsxModule.php',
        '/src/PsxData/PsxDataMatrice.php',
        '/src/PsxData/PsxDataPrepare.php',
        '/src/PsxData/PsxDataValidation.php',
        '/src/PsxData/index.php',
        '/src/Refund.php',
        '/src/Segment/SegmentAPI.php',
        '/src/Segment/SegmentTracker.php',
        '/src/Updater/PaypalAccountUpdater.php',
        '/src/WebHookOrder.php',
        '/views/js/initCardPayment.js',
        '/views/js/initExpressCheckout.js',
        '/views/js/initPaypalAndCard.js',
        '/views/js/initPaypalPayment.js',
        '/views/json/all_countries.json',
        '/views/json/all_languages.json',
        '/views/json/countries_states.json',
        '/views/json/i18n/business-information-de.json',
        '/views/json/i18n/business-information-en.json',
        '/views/json/i18n/business-information-es.json',
        '/views/json/i18n/business-information-fr.json',
        '/views/json/i18n/business-information-it.json',
        '/views/json/i18n/index.php',
        '/views/json/index.php',
        '/views/templates/front/expressCheckout.tpl',
        '/views/templates/front/paymentCardConfirmation.tpl',
        '/views/templates/front/paymentOptions/hosted-fields.tpl',
        '/views/templates/front/paymentOptions/paypal.tpl',
        '/views/templates/front/paymentPaypalConfirmation.tpl',
        '/views/templates/front/validateOrder.tpl',
        '/views/templates/front/validateOrderLegacy.tpl',
    ];

    $shouldInvalidateFiles = function_exists('opcache_invalidate') && filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOLEAN);

    foreach ($removedFiles as $removedFile) {
        $filepath = _PS_MODULE_DIR_ . $module->name . $removedFile;

        if (file_exists($filepath)) {
            $isRemoved = @unlink($filepath) && !file_exists($filepath);

            if ($isRemoved && $shouldInvalidateFiles) {
                @opcache_invalidate($filepath, true);
            }
        }
    }

    return true;
}
