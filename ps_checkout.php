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
require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_checkout extends PaymentModule
{
    /**
     * Default hook to install
     * 1.6 and 1.7
     *
     * @var array
     */
    const HOOK_LIST = [
        'displayOrderConfirmation',
        'displayAdminOrderLeft',
        'displayAdminOrderMainBottom',
        'actionObjectShopAddAfter',
        'actionAdminControllerSetMedia',
        'displayPaymentTop',
    ];

    /**
     * Hook to install for 1.7
     *
     * @var array
     */
    const HOOK_LIST_17 = [
        'paymentOptions',
        'actionFrontControllerSetMedia',
        'displayAdminAfterHeader',
        'displayExpressCheckout',
        'DisplayFooterProduct',
        'displayPersonalInformationTop',
        'actionBeforeCartUpdateQty',
        'header',
        'displayInvoiceLegalFreeText',
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
        'payment',
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
        'PS_PSX_FIREBASE_EMAIL' => '',
        'PS_PSX_FIREBASE_ID_TOKEN' => '',
        'PS_PSX_FIREBASE_LOCAL_ID' => '',
        'PS_PSX_FIREBASE_REFRESH_TOKEN' => '',
        'PS_PSX_FIREBASE_REFRESH_DATE' => '',
        'PS_CHECKOUT_PSX_FORM' => '',
    ];

    public $confirmUninstall;
    public $bootstrap;

    // Needed in order to retrieve the module version easier (in api call headers) than instanciate
    // the module each time to get the version
    const VERSION = '1.5.2';

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    public function __construct()
    {
        $this->name = 'ps_checkout';
        $this->tab = 'payments_gateways';

        // We cannot use the const VERSION because the const is not computed by addons marketplace
        // when the zip is uploaded
        $this->version = '1.5.2';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';

        $this->module_key = '82bc76354cfef947e06f1cc78f5efe2e';

        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->l('PrestaShop Checkout');
        $this->description = $this->l('Provide the most commonly used payment methods to your customers in this all-in-one module, and manage all your sales in a centralized interface.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.6.1', 'max' => _PS_VERSION_];
    }

    /**
     * Function executed at the install of the module
     *
     * @return bool
     */
    public function install()
    {
        // Install for both 1.7 and 1.6
        $defaultInstall = parent::install() &&
            (new PrestaShop\Module\PrestashopCheckout\ShopUuidManager())->generateForAllShops() &&
            $this->installConfiguration() &&
            $this->registerHook(self::HOOK_LIST) &&
            (new PrestaShop\Module\PrestashopCheckout\OrderStates())->installPaypalStates() &&
            (new PrestaShop\Module\PrestashopCheckout\Database\TableManager())->createTable() &&
            $this->installTabs();

        if (!$defaultInstall) {
            return false;
        }

        // Install specific to prestashop 1.7
        if ((new PrestaShop\Module\PrestashopCheckout\ShopContext())->isShop17()) {
            return $this->registerHook(self::HOOK_LIST_17) &&
                $this->updatePosition(\Hook::getIdByName('paymentOptions'), false, 1);
        }

        // Install specific to prestashop 1.6
        return $this->registerHook(self::HOOK_LIST_16) &&
            $this->updatePosition(\Hook::getIdByName('payment'), false, 1);
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
     * Function executed at the uninstall of the module
     *
     * @return bool
     */
    public function uninstall()
    {
        foreach (array_keys($this->configurationList) as $name) {
            Configuration::deleteByName($name);
        }

        return parent::uninstall() &&
            (new PrestaShop\Module\PrestashopCheckout\Database\TableManager())->dropTable() &&
            $this->uninstallTabs();
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
     * Used before 1.7.6 - hook DisplayPersonalInformationTop not available
     */
    public function hookHeader()
    {
        if (version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            return false;
        }

        $currentPage = $this->context->controller->php_self;

        if ($currentPage != 'order') {
            return false;
        }

        return $this->displayECOnCheckout();
    }

    /**
     * Express checkout on the first step of the checkout
     */
    public function hookDisplayPersonalInformationTop()
    {
        if (!version_compare(_PS_VERSION_, '1.7.6.0', '>=')) {
            return false;
        }

        return $this->displayECOnCheckout();
    }

    /**
     * Render express checkout for checkout page
     */
    private function displayECOnCheckout()
    {
        $displayOnCheckout = (bool) Configuration::get(
            'PS_CHECKOUT_EC_CHECKOUT_PAGE',
            null,
            null,
            (int) \Context::getContext()->shop->id
        );

        if (!$displayOnCheckout) {
            return false;
        }

        // Check if we are already in an express checkout
        if (isset($this->context->cookie->paypalOrderId)) {
            return false;
        }

        if (true === $this->isPaymentStep()) {
            return false;
        }

        $expressCheckout = new PrestaShop\Module\PrestashopCheckout\ExpressCheckout($this, $this->context);
        $expressCheckout->setDisplayMode(PrestaShop\Module\PrestashopCheckout\ExpressCheckout::CHECKOUT_MODE);

        return $expressCheckout->render();
    }

    /**
     * Express checkout on the cart page
     */
    public function hookDisplayExpressCheckout()
    {
        $displayExpressCheckout = (bool) Configuration::get(
            'PS_CHECKOUT_EC_ORDER_PAGE',
            null,
            null,
            (int) \Context::getContext()->shop->id
        );

        if (!$displayExpressCheckout) {
            return false;
        }

        $expressCheckout = new PrestaShop\Module\PrestashopCheckout\ExpressCheckout($this, $this->context);
        $expressCheckout->setDisplayMode(PrestaShop\Module\PrestashopCheckout\ExpressCheckout::CART_MODE);

        return $expressCheckout->render();
    }

    /**
     * Express checkout on the product page
     */
    public function hookDisplayFooterProduct($params)
    {
        $displayOnProductPage = (bool) Configuration::get(
            'PS_CHECKOUT_EC_PRODUCT_PAGE',
            null,
            null,
            (int) \Context::getContext()->shop->id
        );

        if (!$displayOnProductPage) {
            return false;
        }

        $expressCheckout = new PrestaShop\Module\PrestashopCheckout\ExpressCheckout($this, $this->context);
        $expressCheckout->setDisplayMode(PrestaShop\Module\PrestashopCheckout\ExpressCheckout::PRODUCT_MODE);

        return $expressCheckout->render();
    }

    public function getContent()
    {
        $paypalAccount = new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository();
        $psAccount = new PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository();

        // update merchant status only if the merchant onboarding is completed
        if ($paypalAccount->onbardingIsCompleted()
            && $psAccount->onbardingIsCompleted()) {
            $paypalAccount = $paypalAccount->getOnboardedAccount();
            (new PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater($paypalAccount))->update();
        }

        $this->context->smarty->assign([
            'pathApp' => $this->_path . 'views/js/app.js?v=' . $this->version,
        ]);

        Media::addJsDef([
            'store' => (new PrestaShop\Module\PrestashopCheckout\Presenter\Store\StorePresenter($this, $this->context))->present(),
        ]);

        return $this->display(__FILE__, '/views/templates/admin/configuration.tpl');
    }

    public function hookActionBeforeCartUpdateQty()
    {
        if (isset($this->context->cookie->paypalOrderId)) {
            $this->context->cookie->__unset('paypalOrderId');
            $this->context->cookie->__unset('paypalEmail');
        }
    }

    /**
     * Add payment option at the checkout in the front office (prestashop 1.6)
     */
    public function hookPayment()
    {
        $cart = $this->context->cart;

        if (false === $this->active) {
            return false;
        }

        if (false === $this->merchantIsValid()) {
            return false;
        }

        if (false === $this->checkCurrency($cart)) {
            return false;
        }

        $paypalAccountRepository = new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository();

        $this->context->smarty->assign([
            'path' => $this->_path . 'views/img/',
            'cardIsActive' => $paypalAccountRepository->cardHostedFieldsIsAvailable(),
            'paypalIsActive' => $paypalAccountRepository->paypalPaymentMethodIsValid(),
            'paymentOrder' => $this->getPaymentMethods(),
        ]);

        $this->context->controller->addCss($this->_path . 'views/css/payments16.css');

        return $this->display(__FILE__, '/views/templates/hook/payment.tpl');
    }

    /**
     * Add payment option at the checkout in the front office (prestashop 1.7)
     *
     * @param array $params return by the hook
     *
     * @return array|false all payment option available
     */
    public function hookPaymentOptions($params)
    {
        if (false === $this->active) {
            return false;
        }

        if (false === $this->merchantIsValid()) {
            return false;
        }

        if (false === $this->checkCurrency($params['cart'])) {
            return false;
        }

        if (false === $this->isPaymentStep()) {
            return false;
        }

        // Check if we are in an express checkout mode
        if (isset($this->context->cookie->paypalOrderId)) {
            $payment_options[] = $this->getExpressCheckoutPaymentOption();
            // if yes, return only one payment option (express checkout)
            return $payment_options;
        }

        $paypalOrder = new PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler($this->context);
        $paypalOrder = $paypalOrder->handle();

        if (false === $paypalOrder['status']) {
            return false;
        }

        $paypalAccountRepository = new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository();

        $termsAndConditionsLinkCms = new \CMS(
            (int) Configuration::get(
                'PS_CONDITIONS_CMS_ID',
                null,
                null,
                (int) \Context::getContext()->shop->id
            ),
            (int) $this->context->language->id
        );
        $termsAndConditionsLink = $this->context->link->getCMSLink(
            $termsAndConditionsLinkCms,
            $termsAndConditionsLinkCms->link_rewrite
        );

        $paypalSdkLink = new PrestaShop\Module\PrestashopCheckout\Builder\PayPalSdkLink\PayPalSdkLinkBuilder();

        $this->context->smarty->assign([
            'paypalSdkLink' => $paypalSdkLink->buildLink(),
            'clientToken' => $paypalOrder['body']['client_token'],
            'paypalOrderId' => $paypalOrder['body']['id'],
            'validateOrderLinkByCard' => $this->getValidateOrderLink($paypalOrder['body']['id'], 'card'),
            'validateOrderLinkByPaypal' => $this->getValidateOrderLink($paypalOrder['body']['id'], 'paypal'),
            'cardIsActive' => $paypalAccountRepository->cardHostedFieldsIsAvailable(),
            'paypalIsActive' => $paypalAccountRepository->paypalPaymentMethodIsValid(),
            'isCardPaymentError' => (bool) Tools::getValue('hferror'),
            'modulePath' => $this->getPathUri(),
            'paypalPaymentOption' => $this->name . '_paypal',
            'hostedFieldsErrors' => (new PrestaShop\Module\PrestashopCheckout\HostedFieldsErrors($this))->getHostedFieldsErrors(),
            'termsAndConditionsLink' => $termsAndConditionsLink,
            'jsPathInitPaypalSdk' => $this->_path . 'views/js/initPaypalAndCard.js?v=' . $this->version,
        ]);

        $paymentMethods = $this->getPaymentMethods();

        $payment_options = [];

        foreach ($paymentMethods as $position => $paymentMethod) {
            if ($paymentMethod['name'] === 'card'
                && true === $paypalAccountRepository->cardHostedFieldsIsAvailable()
            ) {
                $payment_options[] = $this->getHostedFieldsPaymentOption();
            } elseif ($paymentMethod['name'] === 'paypal'
                && true === $paypalAccountRepository->paypalPaymentMethodIsValid()) {
                $payment_options[] = $this->getPaypalPaymentOption();
            }
        }

        return $payment_options;
    }

    /**
     * Get payment methods order
     *
     * @return array
     */
    public function getPaymentMethods()
    {
        $paymentMethods = \Configuration::get(
            'PS_CHECKOUT_PAYMENT_METHODS_ORDER',
            null,
            null,
            (int) \Context::getContext()->shop->id
        );

        // if no paymentMethods position is set, by default put credit card (hostedFields) as first position
        if (empty($paymentMethods)) {
            return [
                ['name' => 'card'],
                ['name' => 'paypal'],
            ];
        }

        return json_decode($paymentMethods, true);
    }

    /**
     * Tells if we are in the Payment step from the order tunnel.
     * We use the ReflectionObject because it only exists from Prestashop 1.7.7
     *
     * @return bool
     */
    private function isPaymentStep()
    {
        $checkoutSteps = $this->getAllOrderSteps();

        /* Get the checkoutPaymentKey from the $checkoutSteps array */
        foreach ($checkoutSteps as $stepObject) {
            if ($stepObject instanceof CheckoutPaymentStep) {
                return (bool) $stepObject->isCurrent();
            }
        }

        return false;
    }

    /**
     * Get all existing Payment Steps from front office.
     * Use ReflectionObject before Prestashop 1.7.7
     * From Prestashop 1.7.7 object checkoutProcess is now public
     *
     * @return array
     */
    private function getAllOrderSteps()
    {
        $isPrestashop177 = version_compare(_PS_VERSION_, '1.7.7.0', '>=');

        if (true === $isPrestashop177) {
            return $this->context->controller->getCheckoutProcess()->getSteps();
        }

        /* Reflect checkoutProcess object */
        $reflectedObject = (new ReflectionObject($this->context->controller))->getProperty('checkoutProcess');
        $reflectedObject->setAccessible(true);

        /* Get Checkout steps data */
        $checkoutProcessClass = $reflectedObject->getValue($this->context->controller);

        return $checkoutProcessClass->getSteps();
    }

    /**
     * Generate paypal payment option
     *
     * @return object PaymentOption
     */
    public function getPaypalPaymentOption()
    {
        $paypalPaymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $paypalPaymentOption->setModuleName($this->name . '_paypal')
                            ->setCallToActionText($this->l('Pay with a PayPal account or other payment methods'))
                            ->setAction($this->context->link->getModuleLink($this->name, 'CreateOrder', [], true))
                            ->setAdditionalInformation($this->generatePaypalForm())
                            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/paypal.png'));

        return $paypalPaymentOption;
    }

    /**
     * Create the pay by paypal button
     *
     * @return string tpl that include the paypal button
     */
    public function generatePaypalForm()
    {
        $this->smarty->assign([
            'imgPath' => $this->_path . '/views/img/',
        ]);

        return $this->context->smarty->fetch('module:ps_checkout/views/templates/front/paymentOptions/paypal.tpl');
    }

    /**
     * Generate hostfields payment option
     *
     * @return object PaymentOption
     */
    public function getHostedFieldsPaymentOption()
    {
        $hostedFieldsPaymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $hostedFieldsPaymentOption->setModuleName($this->name . '_hostedFields')
                    ->setCallToActionText($this->l('Pay by Card'))
                    ->setAction($this->context->link->getModuleLink($this->name, 'ValidateOrder', [], true))
                    ->setForm($this->generateHostedFieldsForm())
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/payment-cards.png'));

        return $hostedFieldsPaymentOption;
    }

    /**
     * Create the hosted fields form
     *
     * @return string tpl that include hosted fields
     */
    public function generateHostedFieldsForm()
    {
        return $this->context->smarty->fetch(
            'module:ps_checkout/views/templates/front/paymentOptions/hosted-fields.tpl'
        );
    }

    /**
     * Generate express checkout payment option
     *
     * @return object PaymentOption
     */
    public function getExpressCheckoutPaymentOption()
    {
        $expressCheckoutPaymentOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $expressCheckoutPaymentOption->setModuleName($this->name . '_expressCheckout')
                    ->setCallToActionText($this->l('Pay by Paypal using express checkout'))
                    ->setAction($this->context->link->getModuleLink(
                        $this->name,
                        'ValidateOrder',
                        [
                            'orderId' => $this->context->cookie->__get('paypalOrderId'),
                            'paymentMethod' => 'paypal',
                            'isExpressCheckout' => true,
                        ],
                        true
                    ));

        return $expressCheckoutPaymentOption;
    }

    /**
     * Hook executed at the order confirmation
     *
     * @param array $params
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

        $this->context->smarty->assign([
            'status' => $order->valid ? 'ok' : 'failed',
            'id_order' => $order->id,
            'shopIs17' => (new PrestaShop\Module\PrestashopCheckout\ShopContext())->isShop17(),
        ]);

        return $this->display(__FILE__, '/views/templates/hook/orderConfirmation.tpl');
    }

    /**
     * Check if the module can process to a payment with the
     * current currency
     *
     * @param object $cart
     *
     * @return bool
     */
    public function checkCurrency($cart)
    {
        $currencyOrder = new \Currency($cart->id_currency);
        $currenciesModule = $this->getCurrency($cart->id_currency);

        if (is_array($currenciesModule)) {
            foreach ($currenciesModule as $currencyModule) {
                if ($currencyOrder->id == $currencyModule['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Display promotion block in the admin payment controller
     */
    public function hookDisplayAdminAfterHeader()
    {
        if ('AdminPayment' !== Tools::getValue('controller')) {
            return false;
        }

        $link = (new PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter($this->context->link))->getAdminLink(
            'AdminModules',
            true,
            [],
            [
                'configure' => 'ps_checkout',
            ]
        );

        $this->context->smarty->assign([
            'imgPath' => $this->_path . 'views/img/',
            'configureLink' => $link,
        ]);

        return $this->display(__FILE__, '/views/templates/hook/adminAfterHeader.tpl');
    }

    /**
     * Load asset on the back office
     */
    public function hookActionAdminControllerSetMedia()
    {
        if ('AdminPayment' === Tools::getValue('controller')) {
            $this->context->controller->addCss($this->_path . 'views/css/adminAfterHeader.css');
        }

        if ('AdminOrders' === Tools::getValue('controller')) {
            $this->context->controller->addJS($this->getPathUri() . 'views/js/adminOrderView.js?version=' . $this->version);
        }
    }

    /**
     * Generate the url to the order validation controller
     *
     * @param string $orderId order id paypal
     * @param string $paymentMethod can be 'card' or 'paypal'
     *
     * @return string
     */
    public function getValidateOrderLink($orderId, $paymentMethod)
    {
        return $this->context->link->getModuleLink(
            $this->name,
            'ValidateOrder',
            [
                'orderId' => $orderId,
                'paymentMethod' => $paymentMethod,
            ],
            true,
            null,
            (int) $this->context->shop->id
        );
    }

    /**
     * Check if paypal and ps account are valid
     *
     * @return bool
     */
    public function merchantIsValid()
    {
        return (new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository())->onbardingIsCompleted()
            && (new PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository())->paypalEmailIsValid()
            && (new PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository())->onbardingIsCompleted();
    }

    /**
     * Load asset on the front office
     */
    public function hookActionFrontControllerSetMedia()
    {
        if (Tools::getValue('controller') !== 'order') {
            return;
        }

        if (false === $this->merchantIsValid()) {
            return;
        }

        $this->context->controller->registerStylesheet(
            'ps-checkout-css-paymentOptions',
            'modules/' . $this->name . '/views/css/payments.css'
        );
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
        if (false === (new PrestaShop\Module\PrestashopCheckout\ShopContext())->isShop17()) {
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
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        if (null !== $this->logger) {
            return $this->logger;
        }

        $this->logger = PrestaShop\Module\PrestashopCheckout\Factory\CheckoutLogger::create();

        return $this->logger;
    }

    /**
     * This hook allows to add PayPal OrderId and TransactionId on PDF invoice
     *
     * @param array $params
     *
     * @return string HTML is not allowed in this hook
     */
    public function hookDisplayInvoiceLegalFreeText(array $params)
    {
        /** @var \Order $order */
        $order = $params['order'];

        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $paypalOrderId = (new OrderMatrice())->getOrderPaypalFromPrestashop($order->id);

        // This order has not been paid with this module
        if (empty($paypalOrderId)) {
            return '';
        }

        // Do not display wrong data to invoice
        if (OrderMatrice::hasInconsistencies($order->id)) {
            return '';
        }

        $legalFreeText = $this->l('PayPal Order Id : ', 'translations') . $paypalOrderId . PHP_EOL;

        /** @var \OrderPayment[] $orderPayments */
        $orderPayments = $order->getOrderPaymentCollection();

        foreach ($orderPayments as $orderPayment) {
            $legalFreeText .= $this->l('PayPal Transaction Id : ', 'translations') . $orderPayment->transaction_id . PHP_EOL;
        }

        return $legalFreeText;
    }

    /**
     * This hook called after a new Shop is created
     *
     * @param array $params
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
     * @param array $params
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

        return $this->display(__FILE__, '/views/templates/hook/displayAdminOrderLeft.tpl');
    }

    /**
     * This hook called on BO Order view page after 1.7.7
     *
     * @param array $params
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

        return $this->display(__FILE__, '/views/templates/hook/displayAdminOrderMainBottom.tpl');
    }

    /**
     * This hook display a block on top of PaymentOptions on PrestaShop 1.7
     *
     * @return string
     */
    public function hookDisplayPaymentTop()
    {
        $paymentError = (int) Tools::getValue('paymentError');
        $paymentErrorMessage = '';
        $isExpressCheckout = $this->context->cookie->__isset('paypalOrderId');

        if (0 < $paymentError) {
            switch ($paymentError) {
                case \PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException::PAYPAL_PAYMENT_CARD_ERROR:
                    $paymentErrorMessage = $this->l('The transaction failed. Please try a different card.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::CARD_TYPE_NOT_SUPPORTED:
                    $paymentErrorMessage = $this->l('Processing of this card type is not supported. Use another card type.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::INVALID_SECURITY_CODE_LENGTH:
                    $paymentErrorMessage = $this->l('The CVC code length is invalid for the specified card type.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::CURRENCY_NOT_SUPPORTED_FOR_CARD_TYPE:
                    $paymentErrorMessage = $this->l('Your card cannot be used to pay in this currency, please try another payment method.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::CURRENCY_NOT_SUPPORTED_FOR_COUNTRY:
                    $paymentErrorMessage = $this->l('Your card cannot be used to pay in our country, please try another payment method.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::INSTRUMENT_DECLINED:
                    $paymentErrorMessage = $this->l('This payment method declined transaction, please try another.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED:
                    $paymentErrorMessage = $this->l('You have exceeded the maximum number of payment attempts.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::PAYER_ACCOUNT_LOCKED_OR_CLOSED:
                    $paymentErrorMessage = $this->l('Your PayPal account is locked or closed, please try another.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::PAYER_ACCOUNT_RESTRICTED:
                    $paymentErrorMessage = $this->l('You are not allowed to pay with this PayPal account, please try another.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::PAYER_CANNOT_PAY:
                    $paymentErrorMessage = $this->l('You are not allowed to pay with this payment method, please try another.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::PAYER_COUNTRY_NOT_SUPPORTED:
                    $paymentErrorMessage = $this->l('Your country is not supported by this payment method, please try to select another.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::REDIRECT_PAYER_FOR_ALTERNATE_FUNDING:
                    $paymentErrorMessage = $this->l('The transaction failed. Please try a different payment method.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::TRANSACTION_BLOCKED_BY_PAYEE:
                    $paymentErrorMessage = $this->l('The transaction was blocked by Fraud Protection settings.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED:
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::TRANSACTION_REFUSED:
                    $paymentErrorMessage = $this->l('The transaction was refused.', 'translations');
                    break;
                case \PrestaShop\Module\PrestashopCheckout\Exception\PayPalException::NO_EXTERNAL_FUNDING_DETAILS_FOUND:
                    $paymentErrorMessage = $this->l('This payment method seems not working currently, please try another.', 'translations');
                    break;
                default:
                    $paymentErrorMessage = $this->l('Please try a different payment method or try again later.', 'translations');
            }
        }

        $this->context->smarty->assign([
            'paymentError' => $paymentError,
            'paymentErrorMessage' => $paymentErrorMessage,
            'isExpressCheckout' => $isExpressCheckout,
        ]);

        if (true === $isExpressCheckout) {
            $this->context->smarty->assign([
                'paypalLogoPath' => $this->getPathUri() . 'views/img/paypal_express.png',
                'translatedText' => strtr(
                    $this->l('You have selected your [PAYPAL_ACCOUNT] PayPal account to proceed to the payment.', 'translations'),
                    [
                        '[PAYPAL_ACCOUNT]' => $this->context->cookie->__get('paypalEmail'),
                    ]
                ),
            ]);
        }

        return $this->display(__FILE__, '/views/templates/hook/displayPaymentTop.tpl');
    }
}
