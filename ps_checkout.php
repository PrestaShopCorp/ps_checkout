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
require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_checkout extends PaymentModule
{
    const COOKIE_PAYPAL_ORDER = 'pscheckoutPayPalOrder';

    /**
     * Default hook to install
     * 1.6 and 1.7
     *
     * @var array
     */
    const HOOK_LIST = [
        'displayAdminAfterHeader',
        'displayOrderConfirmation',
        'displayAdminOrderLeft',
        'displayAdminOrderMainBottom',
        'actionObjectShopAddAfter',
        'actionAdminControllerSetMedia',
        'displayPaymentTop',
        'displayPaymentByBinaries',
        'displayProductPriceBlock',
        'actionFrontControllerSetMedia',
        'header',
        'actionObjectOrderPaymentAddAfter',
        'actionObjectOrderPaymentUpdateAfter',
    ];

    /**
     * Hook to install for 1.7
     *
     * @var array
     */
    const HOOK_LIST_17 = [
        'paymentOptions',
        'displayExpressCheckout',
        'displayFooterProduct',
        'displayPersonalInformationTop',
        'actionCartUpdateQuantityBefore',
        'displayInvoiceLegalFreeText',
        'actionObjectProductInCartDeleteAfter',
    ];

    /**
     * Hook to install for 1.7.1
     *
     * @var array
     */
    const HOOK_LIST_171 = [
        'displayProductAdditionalInfo',
    ];

    /**
     * Names of ModuleAdminController used
     */
    const MODULE_ADMIN_CONTROLLERS = [
        'AdminAjaxPrestashopCheckout',
        'AdminPaypalOnboardingPrestashopCheckout',
    ];

    /**
     * Hook to install for 1.6
     *
     * @var array
     */
    const HOOK_LIST_16 = [
        'actionBeforeCartUpdateQty',
        'actionAfterDeleteProductInCart',
        'displayPayment',
        'displayCartTotalPriceLabel',
    ];

    public $configurationList = [
        'PS_CHECKOUT_INTENT' => 'CAPTURE',
        'PS_CHECKOUT_MODE' => 'LIVE',
        'PS_CHECKOUT_PAYMENT_METHODS_ORDER' => '',
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
        'PS_PSX_FIREBASE_EMAIL' => '',
        'PS_PSX_FIREBASE_ID_TOKEN' => '',
        'PS_PSX_FIREBASE_LOCAL_ID' => '',
        'PS_PSX_FIREBASE_REFRESH_TOKEN' => '',
        'PS_PSX_FIREBASE_REFRESH_DATE' => '',
        'PS_CHECKOUT_PSX_FORM' => '',
        'PS_CHECKOUT_PAYPAL_CB_INLINE' => false,
        'PS_CHECKOUT_LOGGER_MAX_FILES' => '15',
        'PS_CHECKOUT_LOGGER_LEVEL' => '400',
        'PS_CHECKOUT_LOGGER_HTTP' => '0',
        'PS_CHECKOUT_LOGGER_HTTP_FORMAT' => 'DEBUG',
        'PS_CHECKOUT_LIVE_STEP_VIEWED' => false,
        'PS_CHECKOUT_INTEGRATION_DATE' => self::INTEGRATION_DATE,
        'PS_CHECKOUT_SHOP_UUID_V4' => '',
    ];

    public $confirmUninstall;
    public $bootstrap;

    // Needed in order to retrieve the module version easier (in api call headers) than instanciate
    // the module each time to get the version
    const VERSION = '2.18.0';

    const INTEGRATION_DATE = '2020-07-30';

    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    private $disableSegment;

    /**
     * @var \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer
     */
    private $serviceContainer;

    /**
     * @var \PrestaShop\Module\PrestashopCheckout\Handler\ModuleFilteredRavenClient
     */
    private $sentryClient;

    public function __construct()
    {
        $this->name = 'ps_checkout';
        $this->tab = 'payments_gateways';

        // We cannot use the const VERSION because the const is not computed by addons marketplace
        // when the zip is uploaded
        $this->version = '2.18.0';
        $this->author = 'PrestaShop';
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->module_key = '82bc76354cfef947e06f1cc78f5efe2e';

        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->l('PrestaShop Checkout');
        $this->description = $this->l('Provide the most commonly used payment methods to your customers in this all-in-one module, and manage all your sales in a centralized interface.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.6.1.0', 'max' => _PS_VERSION_];
        $this->disableSegment = false;

        $this->installSentryExceptionLogger();
    }

    /**
     * Function executed at the install of the module
     *
     * @return bool
     */
    public function install()
    {
        // When PrestaShop install a module, enable() and install() are called but we want to track only install()
        // Should be done before parent::install() because enable() will be called first
        $this->disableSegment = true;

        // Install for both 1.7 and 1.6
        $result = (bool) parent::install() &&
            $this->installConfiguration() &&
            $this->installHooks() &&
            (new PrestaShop\Module\PrestashopCheckout\OrderStates())->installPaypalStates() &&
            (new PrestaShop\Module\PrestashopCheckout\Database\TableManager())->createTable() &&
            $this->installTabs() &&
            $this->disableIncompatibleCountries() &&
            $this->disableIncompatibleCurrencies() &&
            (new PrestaShop\Module\PrestashopCheckout\ShopUuidManager())->generateForAllShops();

        if (!$result) {
            return false;
        }

        // We must doing that here because before module is not installed so Service Container cannot be used
        $this->trackModuleAction('Install');

        return $result;
    }

    public function installHooks()
    {
        $result = (bool) $this->registerHook(self::HOOK_LIST);
        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');

        // Install specific to prestashop 1.6
        if (!$shopContext->isShop17()) {
            $result = $result && $this->registerHook(self::HOOK_LIST_16);
            $this->updatePosition(\Hook::getIdByName('payment'), false, 1);

            return $result;
        }

        // Install specific to prestashop 1.7
        if ($shopContext->isShop17()) {
            $result = $result && (bool) $this->registerHook(self::HOOK_LIST_17);
            $this->updatePosition(\Hook::getIdByName('paymentOptions'), false, 1);
        }

        // Install specific to prestashop 1.7.1
        if ($shopContext->isShop171()) {
            $result = $result && (bool) $this->registerHook(self::HOOK_LIST_171);
            $this->updatePosition(\Hook::getIdByName('displayProductAdditionalInfo'), false, 1);
        }

        return $result;
    }

    /**
     * Install configuration for each shop
     *
     * @return bool
     */
    public function installConfiguration()
    {
        $result = true;

        foreach (\Shop::getShops(false, null, true) as $shopId) {
            foreach ($this->configurationList as $name => $value) {
                if (false === Configuration::hasKey($name, null, null, (int) $shopId)) {
                    $result = $result && (bool) Configuration::updateValue(
                            $name,
                            $value,
                            false,
                            null,
                            (int) $shopId
                        );
                }
            }
        }

        return $result;
    }

    /**
     * This method is often use to create an ajax controller
     *
     * @return bool
     */
    public function installTabs()
    {
        $installTabCompleted = true;

        foreach (static::MODULE_ADMIN_CONTROLLERS as $controllerName) {
            if (Tab::getIdFromClassName($controllerName)) {
                continue;
            }

            $tab = new Tab();
            $tab->class_name = $controllerName;
            $tab->active = true;
            $tab->name = array_fill_keys(
                Language::getIDs(false),
                $this->displayName
            );
            $tab->id_parent = -1;
            $tab->module = $this->name;
            $installTabCompleted = $installTabCompleted && $tab->add();
        }

        return $installTabCompleted;
    }

    /**
     * Disable incompatible countries with PayPal for PrestaShop Checkout
     *
     * @return bool
     */
    public function disableIncompatibleCountries()
    {
        /** @var PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->getService('ps_checkout.paypal.configuration');
        $incompatibleCodes = $paypalConfiguration->getIncompatibleCountryCodes(false);
        $result = true;

        foreach ($incompatibleCodes as $incompatibleCode) {
            $db = \Db::getInstance();

            $result = $result && $db->execute('
                DELETE FROM ' . _DB_PREFIX_ . 'module_country
                WHERE id_country = (SELECT id_country FROM ' . _DB_PREFIX_ . 'country WHERE iso_code = "' . $incompatibleCode . '")
                AND id_module = ' . $this->id . '
                AND id_shop = ' . \Context::getContext()->shop->id
                );
        }

        return $result;
    }

    /**
     * Disable incompatible currencies with PayPal for PrestaShop Checkout
     *
     * @return bool
     */
    public function disableIncompatibleCurrencies()
    {
        /** @var PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->getService('ps_checkout.paypal.configuration');
        $incompatibleCodes = $paypalConfiguration->getIncompatibleCurrencyCodes(false);
        $result = true;

        foreach ($incompatibleCodes as $incompatibleCode) {
            $db = \Db::getInstance();

            $result = $result && $db->execute('
                DELETE FROM ' . _DB_PREFIX_ . 'module_currency
                WHERE id_currency = (SELECT id_currency FROM ' . _DB_PREFIX_ . 'currency WHERE iso_code = "' . $incompatibleCode . '")
                AND id_module = ' . $this->id . '
                AND id_shop = ' . \Context::getContext()->shop->id
                );
        }

        return $result;
    }

    /**
     * Function executed at the uninstall of the module
     *
     * @return bool
     */
    public function uninstall()
    {
        // When PrestaShop uninstall a module, disable() and uninstall() are called but we want to track only uninstall()
        // Should be done before parent::uninstall() because disable() will be called first
        $this->disableSegment = true;
        $this->trackModuleAction('Uninstall');

        foreach (array_keys($this->configurationList) as $name) {
            Configuration::deleteByName($name);
        }

        return parent::uninstall() &&
            (new PrestaShop\Module\PrestashopCheckout\Database\TableManager())->dropTable() &&
            $this->uninstallTabs();
    }

    /**
     * Activate current module.
     *
     * @param bool $force_all If true, enable module for all shop
     *
     * @return bool
     */
    public function enable($force_all = false)
    {
        $isEnabled = parent::enable($force_all);

        // When PrestaShop install a module, enable() and install() are called but we want to track only install()
        if ($isEnabled && false === $this->disableSegment) {
            $this->trackModuleAction('Activate');
        }

        // After event is sent or ignored, we want to track events like before
        if ($this->disableSegment) {
            $this->disableSegment = false;
        }

        return $isEnabled;
    }

    /**
     * Desactivate current module.
     *
     * @param bool $force_all If true, disable module for all shop
     *
     * @return bool
     */
    public function disable($force_all = false)
    {
        // When PrestaShop uninstall a module, disable() and uninstall() are called but we want to track only uninstall()
        if (false === $this->disableSegment) {
            $this->trackModuleAction('Deactivate');
        }

        // After event is sent or ignored, we want to track events like before
        if ($this->disableSegment) {
            $this->disableSegment = false;
        }

        return parent::disable($force_all);
    }

    /**
     * uninstall tabs
     *
     * @return bool
     */
    public function uninstallTabs()
    {
        $uninstallTabCompleted = true;

        foreach (static::MODULE_ADMIN_CONTROLLERS as $controllerName) {
            $id_tab = (int) Tab::getIdFromClassName($controllerName);
            $tab = new Tab($id_tab);
            if (Validate::isLoadedObject($tab)) {
                $uninstallTabCompleted = $uninstallTabCompleted && $tab->delete();
            }
        }

        return $uninstallTabCompleted;
    }

    /**
     * Express checkout on the first step of the checkout
     */
    public function hookDisplayPersonalInformationTop()
    {
        return $this->display(__FILE__, 'views/templates/hook/displayPersonalInformationTop.tpl');
    }

    /**
     * Express checkout and payment method logo block on the cart page
     */
    public function hookDisplayExpressCheckout()
    {
        if (!$this->merchantIsValid()) {
            return '';
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration $payIn4XService */
        $payIn4XService = $this->getService('ps_checkout.pay_in_4x.configuration');

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceProvider $fundingSourceProvider */
        $fundingSourceProvider = $this->getService('ps_checkout.funding_source.provider');

        $count = 0;
        $paymentOptions = [];

        foreach ($fundingSourceProvider->getAll() as $fundingSource) {
            if ($count === 8) {
                break;
            }

            $count += $fundingSource->name === 'card'
                ? 3
                : 1;

            while ($count > 8) {
                array_pop($paymentOptions);
                --$count;
            }
            $paymentOptions[] = $fundingSource->name;
        }

        $width = 25;
        if ($count == 6) {
            $width = 33;
        }

        if ($count < 6) {
            $width = 20;
        }

        $this->context->smarty->assign([
            'width' => $width,
            'modulePath' => $this->getPathUri(),
            'paymentOptions' => $paymentOptions,
            'payIn4XisOrderPageEnabled' => $payIn4XService->isOrderPageActive(),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayExpressCheckout.tpl');
    }

    /**
     * Payment method logo block on product page
     */
    public function hookDisplayProductAdditionalInfo()
    {
        if (!$this->merchantIsValid()) {
            return '';
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceProvider $fundingSourceProvider */
        $fundingSourceProvider = $this->getService('ps_checkout.funding_source.provider');

        $count = 0;
        $paymentOptions = [];

        foreach ($fundingSourceProvider->getAll() as $fundingSource) {
            if ($count === 8) {
                break;
            }

            $count += $fundingSource->name === 'card'
                ? 3
                : 1;

            while ($count > 8) {
                array_pop($paymentOptions);
                --$count;
            }
            $paymentOptions[] = $fundingSource->name;
        }

        $width = 25;
        if ($count == 6) {
            $width = 33;
        }

        if ($count < 6) {
            $width = 20;
        }

        $this->context->smarty->assign([
            'width' => $width,
            'modulePath' => $this->getPathUri(),
            'paymentOptions' => $paymentOptions,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayProductAdditionalInfo.tpl');
    }

    /**
     * Express checkout on the product page
     */
    public function hookDisplayFooterProduct()
    {
        return $this->display(__FILE__, 'views/templates/hook/displayFooterProduct.tpl');
    }

    /**
     * Pay in 4x banner in the product page
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, type: string, product: mixed} $params
     *
     * @return string
     */
    public function hookDisplayProductPriceBlock(array $params)
    {
        if ('product' !== Tools::getValue('controller')
            || !isset($params['type'])
            || $params['type'] !== 'weight'
            || empty($params['product'])
        ) {
            return '';
        }

        $price = 0;

        if (isset($params['product']->price_amount)) {
            // $params['product'] is a ProductLazyArray since PrestaShop 1.7.5.0
            $price = $params['product']->price_amount;
        } elseif ($params['product'] instanceof Product) {
            // $params['product'] is an instance of Product before 1.7.5.0
            $id_product_attribute = Tools::getValue('id_product_attribute', null);
            $price = $params['product']->getPrice(
                Product::$_taxCalculationMethod == PS_TAX_INC,
                null !== $id_product_attribute ? (int) $id_product_attribute : null
            );
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration $payIn4XService */
        $payIn4XService = $this->getService('ps_checkout.pay_in_4x.configuration');

        $this->context->smarty->assign([
            'totalCartPrice' => sprintf('%01.2f', $price),
            'payIn4XisProductPageEnabled' => $payIn4XService->isProductPageActive(),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayProductPriceBlock.tpl');
    }

    /**
     * Pay in 4x banner in the cart page for 1.6
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int} $params
     *
     * @return string|void
     */
    public function hookDisplayCartTotalPriceLabel(array $params)
    {
        $cart = $params['cart'];

        if (false === Validate::isLoadedObject($cart)) {
            return;
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration $payIn4XService */
        $payIn4XService = $this->getService('ps_checkout.pay_in_4x.configuration');

        $totalCartPrice = $cart->getSummaryDetails();
        $this->context->smarty->assign([
            'totalCartPrice' => $totalCartPrice['total_price'],
            'payIn4XisOrderPageEnabled' => $payIn4XService->isOrderPageActive(),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayCartTotalPriceLabel.tpl');
    }

    public function getContent()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccount */
        $paypalAccount = $this->getService('ps_checkout.repository.paypal.account');
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository $psAccount */
        $psAccount = $this->getService('ps_checkout.repository.prestashop.account');

        // update merchant status only if the merchant onboarding is completed
        if ($paypalAccount->onBoardingIsCompleted()
            && $psAccount->onBoardingIsCompleted()) {
            $paypalAccount = $paypalAccount->getOnboardedAccount();
            /** @var \PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater $accountUpdater */
            $accountUpdater = $this->getService('ps_checkout.updater.paypal.account');
            $accountUpdater->update($paypalAccount);
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Presenter\Store\StorePresenter $storePresenter */
        $storePresenter = $this->getService('ps_checkout.store.store');

        Media::addJsDef([
            'store' => $storePresenter->present(),
        ]);

        $this->context->controller->addJS(
            $this->getPathUri() . 'views/js/app.js?version=' . $this->version,
            false
        );

        return $this->display(__FILE__, 'views/templates/admin/configuration.tpl');
    }

    /**
     * This hook is called only since PrestaShop 1.7.0.0
     */
    public function hookActionObjectProductInCartDeleteAfter()
    {
        $this->hookActionCartUpdateQuantityBefore();
    }

    /**
     * This hook is called only in PrestaShop 1.6.1 to 1.6.1.24
     * Deprecated since PrestaShop 1.7.0.0
     */
    public function hookActionAfterDeleteProductInCart()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');

        if ($shopContext->isShop17()) {
            return;
        }

        $this->hookActionCartUpdateQuantityBefore();
    }

    /**
     * This hook is called only since PrestaShop 1.7.0.0
     */
    public function hookActionCartUpdateQuantityBefore()
    {
        if (false === Validate::isLoadedObject($this->context->cart)) {
            return;
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
        $psCheckoutCartRepository = $this->getService('ps_checkout.repository.pscheckoutcart');

        /** @var PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $this->context->cart->id);

        if (false === Validate::isLoadedObject($psCheckoutCart)) {
            return;
        }

        if ($psCheckoutCart->isExpressCheckout || !$this->context->cart->nbProducts()) {
            $this->context->cookie->__unset('paypalEmail');
        }
    }

    /**
     * This hook is called only in PrestaShop 1.6.1 to 1.6.1.24
     * Deprecated since PrestaShop 1.7.0.0
     */
    public function hookActionBeforeCartUpdateQty()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');

        if ($shopContext->isShop17()) {
            return;
        }

        $this->hookActionCartUpdateQuantityBefore();
    }

    /**
     * Add payment option at the checkout in the front office (prestashop 1.6)
     */
    public function hookDisplayPayment()
    {
        if (false === Validate::isLoadedObject($this->context->cart)
            || false === $this->checkCurrency($this->context->cart)
            || false === $this->merchantIsValid()
        ) {
            return '';
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccountRepository */
        $paypalAccountRepository = $this->getService('ps_checkout.repository.paypal.account');

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceProvider $fundingSourceProvider */
        $fundingSourceProvider = $this->getService('ps_checkout.funding_source.provider');
        $paymentOptions = [];

        foreach ($fundingSourceProvider->getAll() as $fundingSource) {
            $paymentOptions[$fundingSource->name] = $fundingSource->label;
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
        $psCheckoutCartRepository = $this->getService('ps_checkout.repository.pscheckoutcart');

        /** @var PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $this->context->cart->id);

        $isExpressCheckout = false !== $psCheckoutCart && $psCheckoutCart->isExpressCheckout;

        $this->context->smarty->assign([
            'is17' => $shopContext->isShop17(),
            'isExpressCheckout' => $isExpressCheckout,
            'modulePath' => $this->getPathUri(),
            'paymentOptions' => $paymentOptions,
            'isHostedFieldsAvailable' => $paypalAccountRepository->cardHostedFieldsIsAvailable(),
            'isOnePageCheckout16' => !$shopContext->isShop17() && (bool) Configuration::get('PS_ORDER_PROCESS_TYPE'),
            'spinnerPath' => $this->getPathUri() . 'views/img/tail-spin.svg',
            'loaderTranslatedText' => $this->l('Please wait, loading additional payment methods.'),
            'paypalLogoPath' => $this->getPathUri() . 'views/img/paypal_express.png',
            'translatedText' => strtr(
                $this->l('You have selected your [PAYPAL_ACCOUNT] PayPal account to proceed to the payment.'),
                [
                    '[PAYPAL_ACCOUNT]' => $this->context->cookie->__get('paypalEmail') ? $this->context->cookie->__get('paypalEmail') : '',
                ]
            ),
            'shoppingCartWarningPath' => $this->getPathUri() . 'views/img/shopping-cart-warning.svg',
            'warningTranslatedText' => $this->l('Warning'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayPayment.tpl');
    }

    /**
     * Add payment option at the checkout in the front office (prestashop 1.7)
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int} $params
     *
     * @return array
     */
    public function hookPaymentOptions(array $params)
    {
        /** @var Cart $cart */
        $cart = $params['cart'];

        if (false === Validate::isLoadedObject($cart)
            || false === $this->checkCurrency($cart)
            || false === $this->merchantIsValid()
        ) {
            return [];
        }
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccountRepository */
        $paypalAccountRepository = $this->getService('ps_checkout.repository.paypal.account');

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceProvider $fundingSourceProvider */
        $fundingSourceProvider = $this->getService('ps_checkout.funding_source.provider');

        $paymentOptions = [];

        foreach ($fundingSourceProvider->getAll() as $fundingSource) {
            $paymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
            $paymentOption->setModuleName($this->name . '-' . $fundingSource->name);
            $paymentOption->setCallToActionText($fundingSource->label);
            $paymentOption->setBinary(true);

            if ('card' === $fundingSource->name && $paypalAccountRepository->cardHostedFieldsIsAvailable()) {
                $this->context->smarty->assign('modulePath', $this->getPathUri());
                $paymentOption->setForm($this->context->smarty->fetch('module:ps_checkout/views/templates/hook/paymentOptions.tpl'));
            }

            $paymentOptions[] = $paymentOption;
        }

        return $paymentOptions;
    }

    /**
     * Hook executed at the order confirmation
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, order: Order, objOrder: Order} $params
     *
     * @return string
     */
    public function hookDisplayOrderConfirmation(array $params)
    {
        /** @var Order $order */
        $order = (isset($params['objOrder'])) ? $params['objOrder'] : $params['order'];

        if ($order->module !== $this->name) {
            return '';
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');
        $this->context->smarty->assign([
            'status' => $order->valid ? 'completed' : 'pending',
            'isShop17' => $shopContext->isShop17(),
            'isAuthorized' => 'AUTHORIZE' === Configuration::get('PS_CHECKOUT_INTENT'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayOrderConfirmation.tpl');
    }

    /**
     * Check if the module can process to a payment with the
     * current currency
     *
     * @param Cart $cart
     *
     * @return bool
     */
    public function checkCurrency($cart)
    {
        $currency_order = Currency::getCurrencyInstance($cart->id_currency);
        /** @var array $currencies_module */
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (empty($currencies_module)) {
            return false;
        }

        foreach ($currencies_module as $currency_module) {
            if ($currency_order->id == $currency_module['id_currency']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Hook used to display templates under BO header
     */
    public function hookDisplayAdminAfterHeader()
    {
        /** @var PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $this->getService('ps_checkout.paypal.configuration');
        /** @var PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository $psAccount */
        $psAccount = $this->getService('ps_checkout.repository.prestashop.account');
        /** @var PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccount */
        $paypalAccount = $this->getService('ps_checkout.repository.paypal.account');
        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');
        /** @var \PrestaShop\Module\PrestashopCheckout\Presenter\Store\Modules\ContextModule $moduleContext */
        $moduleContext = $this->getService('ps_checkout.store.module.context');
        $isShop17 = $shopContext->isShop17();
        $isFullyOnboarded = $psAccount->onBoardingIsCompleted() && $paypalAccount->onBoardingIsCompleted();

        if ('AdminPayment' === Tools::getValue('controller') && $isShop17) { // Display on PrestaShop 1.7.x.x only
            $params = [
                'imgPath' => $this->_path . 'views/img/',
                'configureLink' => (new PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter($this->context->link))->getAdminLink(
                    'AdminModules',
                    true,
                    [],
                    [
                        'configure' => 'ps_checkout',
                    ]
                ),
            ];
            $track = 'View Payment Methods PS Page';
            $template = 'views/templates/hook/adminAfterHeader/promotionBlock.tpl';
        } elseif ('AdminCountries' === Tools::getValue('controller') && $isFullyOnboarded) {
            $params = [
                'isShop17' => $isShop17,
                'codesType' => 'countries',
                'incompatibleCodes' => $paypalConfiguration->getIncompatibleCountryCodes(),
                'paypalLink' => 'https://developer.paypal.com/docs/api/reference/country-codes/#',
                'paymentPreferencesLink' => $moduleContext->getGeneratedLink($isShop17 ? 'AdminPaymentPreferences' : 'AdminPayment'),
            ];
            $track = 'View Countries PS Page';
            $template = 'views/templates/hook/adminAfterHeader/incompatibleCodes.tpl';
        } elseif ('AdminCurrencies' === Tools::getValue('controller') && $isFullyOnboarded) {
            $params = [
                'isShop17' => $isShop17,
                'codesType' => 'currencies',
                'incompatibleCodes' => $paypalConfiguration->getIncompatibleCurrencyCodes(),
                'paypalLink' => 'https://developer.paypal.com/docs/api/reference/currency-codes/#',
                'paymentPreferencesLink' => $moduleContext->getGeneratedLink($isShop17 ? 'AdminPaymentPreferences' : 'AdminPayment'),
            ];
            $track = 'View Currencies PS Page';
            $template = 'views/templates/hook/adminAfterHeader/incompatibleCodes.tpl';
        } else {
            return false;
        }

        return $this->displayAdminAfterHeader($params, $track, $template);
    }

    /**
     * Load asset on the back office
     */
    public function hookActionAdminControllerSetMedia()
    {
        if ('AdminPayment' === Tools::getValue('controller')) {
            $this->context->controller->addCss(
                $this->_path . 'views/css/adminAfterHeader.css?version=' . $this->version,
                'all',
                null,
                false
            );
        }

        if ('AdminCountries' === Tools::getValue('controller')) {
            $this->context->controller->addCss(
                $this->_path . 'views/css/incompatible-banner.css?version=' . $this->version,
                'all',
                null,
                false
            );
        }

        if ('AdminCurrencies' === Tools::getValue('controller')) {
            $this->context->controller->addCss(
                $this->_path . 'views/css/incompatible-banner.css?version=' . $this->version,
                'all',
                null,
                false
            );
        }

        if ('AdminOrders' === Tools::getValue('controller')) {
            $this->context->controller->addJS(
                $this->getPathUri() . 'views/js/adminOrderView.js?version=' . $this->version,
                false
            );
            $this->context->controller->addCss(
                $this->_path . 'views/css/adminOrderView.css?version=' . $this->version,
                'all',
                null,
                false
            );
        }
    }

    /**
     * Check if paypal and ps account are valid
     *
     * @return bool
     */
    public function merchantIsValid()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Validator\MerchantValidator $merchantValidator */
        $merchantValidator = $this->getService('ps_checkout.validator.merchant');

        return $merchantValidator->merchantIsValid();
    }

    /**
     * Load asset on the front office
     */
    public function hookActionFrontControllerSetMedia()
    {
        $controller = (string) Tools::getValue('controller');

        /** @var \PrestaShop\Module\PrestashopCheckout\Validator\FrontControllerValidator $frontControllerValidator */
        $frontControllerValidator = $this->getService('ps_checkout.validator.front_controller');

        if ($frontControllerValidator->shouldLoadFrontCss($controller)) {
            if (method_exists($this->context->controller, 'registerStylesheet')) {
                $this->context->controller->registerStylesheet(
                    'ps-checkout-css-paymentOptions',
                    $this->getPathUri() . 'views/css/payments.css?version=' . $this->version,
                    [
                        'server' => 'remote',
                    ]
                );
            } else {
                $this->context->controller->addCss(
                    $this->getPathUri() . 'views/css/payments16.css?version=' . $this->version,
                    'all',
                    null,
                    false
                );
            }
        }

        if (false === $frontControllerValidator->shouldLoadFrontJS($controller)) {
            return;
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Builder\PayPalSdkLink\PayPalSdkLinkBuilder $payPalSdkLinkBuilder */
        $payPalSdkLinkBuilder = $this->getService('ps_checkout.sdk.paypal.linkbuilder');

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccountRepository */
        $paypalAccountRepository = $this->getService('ps_checkout.repository.paypal.account');

        /** @var \PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration $expressCheckoutConfiguration */
        $expressCheckoutConfiguration = $this->getService('ps_checkout.express_checkout.configuration');

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration $payPalConfiguration */
        $payPalConfiguration = $this->getService('ps_checkout.paypal.configuration');

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceProvider $fundingSourceProvider */
        $fundingSourceProvider = $this->getService('ps_checkout.funding_source.provider');

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration $payIn4xConfiguration */
        $payIn4xConfiguration = $this->getService('ps_checkout.pay_in_4x.configuration');

        $fundingSourcesSorted = [];
        $payWithTranslations = [];
        $isCardAvailable = false;

        foreach ($fundingSourceProvider->getAll() as $fundingSource) {
            $fundingSourcesSorted[] = $fundingSource->name;
            $payWithTranslations[$fundingSource->name] = $fundingSource->label;

            if ('card' === $fundingSource->name) {
                $isCardAvailable = $fundingSource->isEnabled;
            }
        }

        // BEGIN To be refactored in services
        $payPalClientToken = '';
        $payPalOrderId = '';
        $cartFundingSource = 'paypal';
        $psCheckoutCart = false;
        $cartProductCount = 0;

        // Sometimes we can be in Front Office without a cart...
        if (Validate::isLoadedObject($this->context->cart)) {
            $cartProductCount = (int) $this->context->cart->nbProducts();
            /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->getService('ps_checkout.repository.pscheckoutcart');

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $this->context->cart->id);
        }

        // If we have a PayPal Order Id with a status CREATED or APPROVED and a not expired PayPal Client Token, we can use it
        // If paypal_token_expire is in future, token is not expired
        if (false !== $psCheckoutCart
            && false === empty($psCheckoutCart->paypal_order)
            && in_array($psCheckoutCart->paypal_status, [PsCheckoutCart::STATUS_CREATED, PsCheckoutCart::STATUS_APPROVED], true)
            && false === empty($psCheckoutCart->paypal_token_expire)
            && strtotime($psCheckoutCart->paypal_token_expire) > time()
        ) {
            $payPalOrderId = $psCheckoutCart->paypal_order;
            $payPalClientToken = $psCheckoutCart->paypal_token;
            $cartFundingSource = $psCheckoutCart->paypal_funding;
        }
        // END To be refactored in services

        Media::addJsDef([
            $this->name . 'Version' => self::VERSION,
            $this->name . 'AutoRenderDisabled' => (bool) Configuration::get('PS_CHECKOUT_AUTO_RENDER_DISABLED'),
            $this->name . 'LoaderImage' => $this->getPathUri() . 'views/img/loader.svg',
            $this->name . 'PayPalButtonConfiguration' => $payPalConfiguration->getButtonConfiguration(),
            $this->name . 'CardFundingSourceImg' => Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/payment-cards.png'),
            $this->name . 'GetTokenURL' => $this->context->link->getModuleLink($this->name, 'token', [], true),
            $this->name . 'CreateUrl' => $this->context->link->getModuleLink($this->name, 'create', [], true),
            $this->name . 'CheckUrl' => $this->context->link->getModuleLink($this->name, 'check', [], true),
            $this->name . 'ValidateUrl' => $this->context->link->getModuleLink($this->name, 'validate', [], true),
            $this->name . 'CancelUrl' => $this->context->link->getModuleLink($this->name, 'cancel', [], true),
            $this->name . 'ExpressCheckoutUrl' => $this->context->link->getModuleLink($this->name, 'ExpressCheckout', [], true),
            $this->name . 'CheckoutUrl' => $this->getCheckoutPageUrl(),
            $this->name . 'ConfirmUrl' => $this->context->link->getPageLink('order-confirmation', true, (int) $this->context->language->id),
            $this->name . 'PayPalSdkUrl' => $payPalSdkLinkBuilder->buildLink(),
            $this->name . 'PayPalClientToken' => $payPalClientToken,
            $this->name . 'PayPalOrderId' => $payPalOrderId,
            $this->name . 'FundingSource' => $cartFundingSource,
            $this->name . 'HostedFieldsEnabled' => $isCardAvailable && $payPalConfiguration->isCardPaymentEnabled() && $paypalAccountRepository->cardHostedFieldsIsAllowed(),
            $this->name . 'HostedFieldsSelected' => false !== $psCheckoutCart ? (bool) $psCheckoutCart->isHostedFields : false,
            $this->name . 'ExpressCheckoutSelected' => false !== $psCheckoutCart ? (bool) $psCheckoutCart->isExpressCheckout : false,
            $this->name . 'ExpressCheckoutProductEnabled' => $expressCheckoutConfiguration->isProductPageEnabled(),
            $this->name . 'ExpressCheckoutCartEnabled' => $expressCheckoutConfiguration->isOrderPageEnabled(),
            $this->name . 'ExpressCheckoutOrderEnabled' => $expressCheckoutConfiguration->isCheckoutPageEnabled(),
            $this->name . 'PayLaterProductPageButtonEnabled' => $payIn4xConfiguration->isProductPageButtonActive(),
            $this->name . 'PayLaterCartPageButtonEnabled' => $payIn4xConfiguration->isCartPageButtonActive(),
            $this->name . 'PayLaterOrderPageButtonEnabled' => $payIn4xConfiguration->isOrderPageButtonActive(),
            $this->name . '3dsEnabled' => $payPalConfiguration->is3dSecureEnabled(),
            $this->name . 'CspNonce' => $payPalConfiguration->getCSPNonce(),
            $this->name . 'CartProductCount' => $cartProductCount,
            $this->name . 'FundingSourcesSorted' => $fundingSourcesSorted,
            $this->name . 'PayWithTranslations' => $payWithTranslations,
            $this->name . 'CheckoutTranslations' => [
                'checkout.go.back.link.title' => $this->l('Go back to the Checkout'),
                'checkout.go.back.label' => $this->l('Checkout'),
                'checkout.card.payment' => $this->l('Card payment'),
                'checkout.page.heading' => $this->l('Order summary'),
                'checkout.cart.empty' => $this->l('Your shopping cart is empty.'),
                'checkout.page.subheading.card' => $this->l('Card'),
                'checkout.page.subheading.paypal' => $this->l('PayPal'),
                'checkout.payment.by.card' => $this->l('You have chosen to pay by Card.'),
                'checkout.payment.by.paypal' => $this->l('You have chosen to pay by PayPal.'),
                'checkout.order.summary' => $this->l('Here is a short summary of your order:'),
                'checkout.order.amount.total' => $this->l('The total amount of your order comes to'),
                'checkout.order.included.tax' => $this->l('(tax incl.)'),
                'checkout.order.confirm.label' => $this->l('Please confirm your order by clicking "I confirm my order".'),
                'paypal.hosted-fields.label.card-number' => $this->l('Card number'),
                'paypal.hosted-fields.placeholder.card-number' => $this->l('Card number'),
                'paypal.hosted-fields.label.expiration-date' => $this->l('Expiry date'),
                'paypal.hosted-fields.placeholder.expiration-date' => $this->l('MM/YY'),
                'paypal.hosted-fields.label.cvv' => $this->l('CVC'),
                'paypal.hosted-fields.placeholder.cvv' => $this->l('XXX'),
                'express-button.cart.separator' => $this->l('or'),
                'express-button.checkout.express-checkout' => $this->l('Express Checkout'),
                'error.paypal-sdk' => $this->l('No PayPal Javascript SDK Instance'),
                'checkout.payment.others.link.label' => $this->l('Other payment methods'),
                'checkout.payment.others.confirm.button.label' => $this->l('I confirm my order'),
                'checkout.form.error.label' => $this->l('There was an error during the payment. Please try again or contact the support.'),
                'loader-component.label.header' => $this->l('Thanks for your purchase!'),
                'loader-component.label.body' => $this->l('Please wait, we are processing your payment'),
                'error.paypal-sdk.contingency.cancel' => $this->l('Card holder authentication canceled, please choose another payment method or try again.'),
                'error.paypal-sdk.contingency.error' => $this->l('An error occurred on card holder authentication, please choose another payment method or try again.'),
                'error.paypal-sdk.contingency.failure' => $this->l('Card holder authentication failed, please choose another payment method or try again.'),
                'error.paypal-sdk.contingency.unknown' => $this->l('Card holder authentication cannot be checked, please choose another payment method or try again.'),
            ],
        ]);

        if (method_exists($this->context->controller, 'registerJavascript')) {
            $this->context->controller->registerJavascript(
                $this->name . 'Front',
                $this->getPathUri() . 'views/js/front.js?version=' . $this->version,
                [
                    'position' => 'bottom',
                    'priority' => 201,
                    'server' => 'remote',
                ]
            );
        } else {
            $this->context->controller->addJS(
                $this->getPathUri() . 'views/js/front.js?version=' . $this->version,
                false
            );
        }
    }

    /**
     * Override method to add "IGNORE" in the SQL Request to prevent duplicate entry and for getting All Carriers installed
     * Add checkbox carrier restrictions for a new module.
     *
     * @see PaymentModuleCore
     *
     * @param array $shopsList List of Shop identifier
     *
     * @return bool
     */
    public function addCheckboxCarrierRestrictionsForModule(array $shopsList = [])
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');
        if (false === $shopContext->isShop17()) {
            return true;
        }

        $shopsList = empty($shopsList) ? Shop::getShops(true, null, true) : $shopsList;
        $carriersList = Carrier::getCarriers((int) Context::getContext()->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
        $allCarriers = array_column($carriersList, 'id_reference');
        $dataToInsert = [];

        foreach ($shopsList as $idShop) {
            foreach ($allCarriers as $idCarrier) {
                $dataToInsert[] = [
                    'id_reference' => (int) $idCarrier,
                    'id_shop' => (int) $idShop,
                    'id_module' => (int) $this->id,
                ];
            }
        }

        return \Db::getInstance()->insert(
            'module_carrier',
            $dataToInsert,
            false,
            true,
            Db::INSERT_IGNORE
        );
    }

    /**
     * Override method to add "IGNORE" in the SQL Request to prevent duplicate entry.
     * Add checkbox country restrictions for a new module.
     * Associate with all countries allowed in geolocation management
     *
     * @see PaymentModuleCore
     *
     * @param array $shopsList List of Shop identifier
     *
     * @return bool
     */
    public function addCheckboxCountryRestrictionsForModule(array $shopsList = [])
    {
        parent::addCheckboxCountryRestrictionsForModule($shopsList);
        // Then add all countries allowed in geolocation management
        $db = \Db::getInstance();
        // Get active shop ids
        $shopsList = empty($shopsList) ? Shop::getShops(true, null, true) : $shopsList;
        // Get countries
        /** @var array $countries */
        $countries = $db->executeS('SELECT `id_country`, `iso_code` FROM `' . _DB_PREFIX_ . 'country`');
        $countryIdByIso = [];
        foreach ($countries as $country) {
            $countryIdByIso[$country['iso_code']] = $country['id_country'];
        }
        $dataToInsert = [];

        foreach ($shopsList as $idShop) {
            // Get countries allowed in geolocation management for this shop
            $activeCountries = \Configuration::get(
                'PS_ALLOWED_COUNTRIES',
                null,
                null,
                (int) $idShop
            );
            $explodedCountries = explode(';', $activeCountries);

            foreach ($explodedCountries as $isoCodeCountry) {
                if (isset($countryIdByIso[$isoCodeCountry])) {
                    $dataToInsert[] = [
                        'id_country' => (int) $countryIdByIso[$isoCodeCountry],
                        'id_shop' => (int) $idShop,
                        'id_module' => (int) $this->id,
                    ];
                }
            }
        }

        return $db->insert(
            'module_country',
            $dataToInsert,
            false,
            true,
            Db::INSERT_IGNORE
        );
    }

    /**
     * @todo to be removed
     *
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        if (null !== $this->logger) {
            return $this->logger;
        }

        /* @var \Psr\Log\LoggerInterface logger */
        $this->logger = $this->getService('ps_checkout.logger');

        return $this->logger;
    }

    /**
     * This hook allows to add PayPal OrderId and TransactionId on PDF invoice
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, order: Order} $params
     *
     * @return string HTML is not allowed in this hook
     */
    public function hookDisplayInvoiceLegalFreeText(array $params)
    {
        /** @var \Order $order */
        $order = $params['order'];

        // This order has not been paid with this module
        if (false === Validate::isLoadedObject($order)
            || $this->name !== $order->module
        ) {
            return '';
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
        $psCheckoutCartRepository = $this->getService('ps_checkout.repository.pscheckoutcart');

        /** @var PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $order->id_cart);

        /** @var \PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration $psConfiguration */
        $psConfiguration = $this->getService('ps_checkout.configuration');

        // No PayPal Order found for this Order
        if (false === $psCheckoutCart) {
            return '';
        }

        $legalFreeText = $psConfiguration->get(
            'PS_INVOICE_LEGAL_FREE_TEXT',
            [
                'id_lang' => (int) $order->id_lang,
                'id_shop' => (int) $order->id_shop,
                'default' => '',
            ]
        );

        // If a legal free text is found, we add blank lines after
        if (false === empty($legalFreeText)) {
            $legalFreeText .= PHP_EOL . PHP_EOL;
        }

        $legalFreeText .= $this->l('PayPal Order Id : ') . $psCheckoutCart->paypal_order . PHP_EOL;

        /** @var \OrderPayment[] $orderPayments */
        $orderPayments = $order->getOrderPaymentCollection();

        foreach ($orderPayments as $orderPayment) {
            if (false === empty($orderPayment->transaction_id)) {
                $legalFreeText .= $this->l('PayPal Transaction Id : ') . $orderPayment->transaction_id . PHP_EOL;
            }
        }

        return $legalFreeText;
    }

    /**
     * This hook called after a new Shop is created
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, object: Shop} $params
     */
    public function hookActionObjectShopAddAfter(array $params)
    {
        /** @var Shop $shop */
        $shop = $params['object'];

        (new PrestaShop\Module\PrestashopCheckout\ShopUuidManager())->generateForShop((int) $shop->id);
        $this->installConfiguration();
        $this->addCheckboxCarrierRestrictionsForModule([(int) $shop->id]);
        $this->addCheckboxCountryRestrictionsForModule([(int) $shop->id]);
        if ($this->currencies_mode === 'checkbox') {
            $this->addCheckboxCurrencyRestrictionsForModule([(int) $shop->id]);
        } elseif ($this->currencies_mode === 'radio') {
            $this->addRadioCurrencyRestrictionsForModule([(int) $shop->id]);
        }
    }

    /**
     * This hook called on BO Order view page before 1.7.7
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, id_order: int} $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderLeft(array $params)
    {
        $order = new Order((int) $params['id_order']);

        if ($order->module !== $this->name) {
            return '';
        }

        $this->context->smarty->assign([
            'moduleLogoUri' => $this->getPathUri() . 'logo.png',
            'moduleName' => $this->displayName,
            'orderPrestaShopId' => $order->id,
            'orderPayPalBaseUrl' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrderLeft.tpl');
    }

    /**
     * This hook called on BO Order view page after 1.7.7
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, id_order: int} $params
     *
     * @return string
     */
    public function hookDisplayAdminOrderMainBottom(array $params)
    {
        $order = new Order((int) $params['id_order']);

        if ($order->module !== $this->name) {
            return '';
        }

        $this->context->smarty->assign([
            'moduleLogoUri' => $this->getPathUri() . 'logo.png',
            'moduleName' => $this->displayName,
            'orderPrestaShopId' => $order->id,
            'orderPayPalBaseUrl' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayAdminOrderMainBottom.tpl');
    }

    /**
     * This hook display a block on top of PaymentOptions on PrestaShop 1.7
     *
     * @return string
     */
    public function hookDisplayPaymentTop()
    {
        if (false === Validate::isLoadedObject($this->context->cart)
            || false === $this->checkCurrency($this->context->cart)
            || false === $this->merchantIsValid()
        ) {
            return '';
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
        $psCheckoutCartRepository = $this->getService('ps_checkout.repository.pscheckoutcart');

        /** @var PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $this->context->cart->id);

        $isExpressCheckout = false !== $psCheckoutCart && $psCheckoutCart->isExpressCheckout;

        $this->context->smarty->assign([
            'is17' => $shopContext->isShop17(),
            'isExpressCheckout' => $isExpressCheckout,
            'isOnePageCheckout16' => !$shopContext->isShop17() && (bool) Configuration::get('PS_ORDER_PROCESS_TYPE'),
            'spinnerPath' => $this->getPathUri() . 'views/img/tail-spin.svg',
            'loaderTranslatedText' => $this->l('Please wait, loading additional payment methods.'),
            'paypalLogoPath' => $this->getPathUri() . 'views/img/paypal_express.png',
            'translatedText' => strtr(
                $this->l('You have selected your [PAYPAL_ACCOUNT] PayPal account to proceed to the payment.'),
                [
                    '[PAYPAL_ACCOUNT]' => $this->context->cookie->__get('paypalEmail') ? $this->context->cookie->__get('paypalEmail') : '',
                ]
            ),
            'shoppingCartWarningPath' => $this->getPathUri() . 'views/img/shopping-cart-warning.svg',
            'warningTranslatedText' => $this->l('Warning'),
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayPaymentTop.tpl');
    }

    /**
     * @param string $serviceName
     *
     * @return object|null
     */
    public function getService($serviceName)
    {
        if ($this->serviceContainer === null) {
            $this->serviceContainer = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name . str_replace('.', '', $this->version),
                $this->getLocalPath()
            );
        }

        return $this->serviceContainer->getService($serviceName);
    }

    /**
     * This hook displays form generated by binaries during the checkout
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int} $params
     *
     * @return string
     */
    public function hookDisplayPaymentByBinaries(array $params)
    {
        /** @var Cart $cart */
        $cart = $params['cart'];

        if (false === Validate::isLoadedObject($cart)
            || false === $this->checkCurrency($cart)
            || false === $this->merchantIsValid()
        ) {
            return '';
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceProvider $fundingSourceProvider */
        $fundingSourceProvider = $this->getService('ps_checkout.funding_source.provider');
        $paymentOptions = [];

        foreach ($fundingSourceProvider->getAll() as $fundingSource) {
            $paymentOptions[] = $fundingSource->name;
        }

        $this->context->smarty->assign([
            'paymentOptions' => $paymentOptions,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/displayPaymentByBinaries.tpl');
    }

    /**
     * @param string $action
     */
    private function trackModuleAction($action)
    {
        // We want to track only event appends on PrestaShop BO
        if (defined('_PS_ADMIN_DIR_') && 'View Countries PS Page' != $action && 'View Currencies PS Page' != $action) {
            try {
                /** @var \PrestaShop\Module\PrestashopCheckout\Segment\SegmentTracker $tracker */
                $tracker = $this->getService('ps_checkout.segment.tracker');
                $tracker->track($action);
            } catch (Exception $exception) {
                // Sometime on module enable after an upgrade .env data are not loaded
            }
        }
    }

    /**
     * @param array $params
     * @param string $track
     * @param string $template
     */
    private function displayAdminAfterHeader($params, $track, $template)
    {
        $this->context->smarty->assign($params);

        // track when payment method header is called
        $this->trackModuleAction($track);

        return $this->display(__FILE__, $template);
    }

    /**
     * Provide checkout page link
     *
     * @return string
     */
    private function getCheckoutPageUrl()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\ShopContext $shopContext */
        $shopContext = $this->getService('ps_checkout.context.shop');

        if ($shopContext->isShop17()) {
            return $this->context->link->getPageLink(
                'order',
                true,
                (int) $this->context->language->id
            );
        }

        // PrestaShop 1.6 legacy native one page checkout
        if (1 === (int) Configuration::get('PS_ORDER_PROCESS_TYPE')) {
            return $this->context->link->getPageLink(
                'order-opc',
                true,
                (int) $this->context->language->id
            );
        }

        // PrestaShop 1.6 standard checkout
        return $this->context->link->getPageLink(
            'order',
            true,
            (int) $this->context->language->id,
            [
                'step' => 1,
            ]
        );
    }

    /**
     * @return void
     *
     * @throws Raven_Exception
     */
    private function installSentryExceptionLogger()
    {
        $envFiles = [
            'prod' => '.env',
            'test' => '.env.test',
        ];

        $envLoader = new \PrestaShop\Module\PrestashopCheckout\Environment\EnvLoader();

        foreach ($envFiles as $environment => $fileName) {
            if (!file_exists(_PS_MODULE_DIR_ . 'ps_checkout/' . $fileName)) {
                continue;
            }

            $env = $envLoader->read(_PS_MODULE_DIR_ . 'ps_checkout/' . $fileName);

            break;
        }

        if (!empty($env) && isset($env['PS_CHECKOUT_SENTRY_DSN_MODULE'])) {
            $this->sentryClient = new PrestaShop\Module\PrestashopCheckout\Handler\ModuleFilteredRavenClient(
                $env['PS_CHECKOUT_SENTRY_DSN_MODULE'],
                [
                    'level' => 'error',
                    'error_types' => E_ERROR,
                    'tags' => [
                        'php_version' => phpversion(),
                        'module_version' => $this->version,
                        'prestashop_version' => _PS_VERSION_,
                    ],
                ]
            );

            $this->sentryClient->setAppPath(realpath(_PS_MODULE_DIR_ . 'ps_checkout/'));
            $this->sentryClient->setExcludedAppPaths([
                realpath(_PS_MODULE_DIR_ . 'ps_checkout/vendor/'),
            ]);
            $this->sentryClient->setExcludedDomains(['127.0.0.1', 'localhost', '.local']);

            if (version_compare(phpversion(), '7.4.0', '>=') && version_compare(_PS_VERSION_, '1.7.8.0', '<')) {
                return;
            }

            $this->sentryClient->install();
        }
    }

    /**
     * @return \PrestaShop\Module\PrestashopCheckout\Handler\ModuleFilteredRavenClient
     */
    public function getSentryClient()
    {
        return $this->sentryClient;
    }

    public function hookHeader()
    {
        if (false === $this->merchantIsValid()) {
            return '';
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Builder\PayPalSdkLink\PayPalSdkLinkBuilder $payPalSdkLinkBuilder */
        $payPalSdkLinkBuilder = $this->getService('ps_checkout.sdk.paypal.linkbuilder');

        $this->context->smarty->assign([
            'contentToPrefetch' => [
                [
                    'link' => $payPalSdkLinkBuilder->buildLink(),
                    'type' => 'script',
                ],
            ],
        ]);

        return $this->display(__FILE__, 'views/templates/hook/header.tpl');
    }

    /**
     * When an OrderPayment is created we should update fields payment_method and transaction_id
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, object: OrderPayment} $params
     */
    public function hookActionObjectOrderPaymentAddAfter(array $params)
    {
        $this->processHookActionObjectOrderPayment($params);
    }

    /**
     * When an OrderPayment is updated we should update fields payment_method and transaction_id
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, object: OrderPayment} $params
     */
    public function hookActionObjectOrderPaymentUpdateAfter(array $params)
    {
        $this->processHookActionObjectOrderPayment($params);
    }

    /**
     * When an OrderPayment is created or updated we should update fields payment_method and transaction_id
     *
     * @param array{cookie: Cookie, cart: Cart, altern: int, object: OrderPayment} $params
     *
     * @return void
     */
    private function processHookActionObjectOrderPayment($params)
    {
        if (!isset($params['object'])) {
            return;
        }

        /** @var \OrderPayment $orderPayment */
        $orderPayment = $params['object'];

        if (!Validate::isLoadedObject($orderPayment)
            || empty($orderPayment->order_reference)
            || !empty($orderPayment->transaction_id)
            || 1 !== count(OrderPayment::getByOrderReference($orderPayment->order_reference))
        ) {
            return;
        }

        /** @var Order[] $orderCollection */
        $orderCollection = Order::getByReference($orderPayment->order_reference);
        $id_cart = 0;

        foreach ($orderCollection as $order) {
            if ($this->name !== $order->module) {
                return;
            }
            $id_cart = (int) $order->id_cart;
        }

        if (!$id_cart) {
            return;
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $repository */
        $repository = $this->getService('ps_checkout.repository.pscheckoutcart');

        $psCheckoutCart = $repository->findOneByCartId($id_cart);

        if (!$psCheckoutCart) {
            return;
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\PayPal\PayPalOrderProvider $paypalOrderProvider */
        $paypalOrderProvider = $this->getService('ps_checkout.paypal.provider.order');

        $paypalOrder = $paypalOrderProvider->getById($psCheckoutCart->paypal_order);

        if (!empty($paypalOrder['purchase_units'][0]['payments']['captures'])) {
            $transactionId = $paypalOrder['purchase_units'][0]['payments']['captures'][0]['id'];
        } elseif (!empty($paypalOrder['purchase_units'][0]['payments']['authorizations'])) {
            $transactionId = $paypalOrder['purchase_units'][0]['payments']['authorizations'][0]['id'];
        } else {
            return;
        }

        $cardNumber = '';
        $cardBrand = '';

        if (!empty($paypalOrder['payment_source']['card'])) {
            $cardNumber = sprintf('#### #### #### %d', $paypalOrder['payment_source']['card']['last_digits']);
            $cardBrand = $paypalOrder['payment_source']['card']['brand'];
        }

        /** @var \PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceTranslationProvider $fundingSourceTranslationProvider */
        $fundingSourceTranslationProvider = $this->getService('ps_checkout.funding_source.translation');

        \Db::getInstance()->update(
            'order_payment',
            [
                'payment_method' => pSQL($fundingSourceTranslationProvider->getPaymentMethodName($psCheckoutCart->paypal_funding)),
                'transaction_id' => pSQL($transactionId),
                'card_number' => pSQL($cardNumber),
                'card_brand' => pSQL($cardBrand),
            ],
            'id_order_payment = ' . (int) $orderPayment->id
        );
    }
}
