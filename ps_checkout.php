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
        'actionOrderSlipAdd',
        'orderConfirmation',
        'actionOrderStatusUpdate',
        'actionObjectShopAddAfter',
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
        'ActionAdminControllerSetMedia',
        'displayExpressCheckout',
        'DisplayFooterProduct',
        'displayPersonalInformationTop',
        'actionBeforeCartUpdateQty',
        'header',
        'displayInvoiceLegalFreeText',
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
    public $controllers;

    // Needed in order to retrieve the module version easier (in api call headers) than instanciate
    // the module each time to get the version
    const VERSION = '1.3.0';

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
        $this->version = '1.3.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '82bc76354cfef947e06f1cc78f5efe2e';

        $this->bootstrap = false;

        parent::__construct();

        $this->displayName = $this->l('PrestaShop Checkout');
        $this->description = $this->l('Provide the most commonly used payment methods to your customers in this all-in-one module, and manage all your sales in a centralized interface.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.6.1', 'max' => _PS_VERSION_];
        $this->controllers = [
            'AdminAjaxPrestashopCheckout',
            'AdminPaypalOnboardingPrestashopCheckout',
        ];
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
            $this->addCheckoutPaymentForAllActivatedCountries() &&
            $this->installTabs();

        if (!$defaultInstall) {
            return false;
        }

        // Install specific to prestashop 1.7
        if ((new PrestaShop\Module\PrestashopCheckout\ShopContext())->isShop17()) {
            return $this->registerHook(self::HOOK_LIST_17) &&
                $this->updatePosition(\Hook::getIdByName('paymentOptions'), false, 1) &&
                $this->addCheckboxCarrierRestrictionsForModule();
        } else { // Install specific to prestashop 1.6
            return $this->registerHook(self::HOOK_LIST_16) &&
                $this->updatePosition(\Hook::getIdByName('payment'), false, 1);
        }

        return true;
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
                    $result = $result && Configuration::updateValue(
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
        $tab = new Tab();

        foreach ($this->controllers as $controllerName) {
            if (Tab::getIdFromClassName($controllerName)) {
                continue;
            }

            $tab->class_name = $controllerName;
            $tab->active = true;
            $tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $this->name;
            }
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

        foreach ($this->controllers as $controllerName) {
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
     * Add payment option at the checkout in the front office (prestashop 1.7)
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
            'cardIsActive' => $paypalAccountRepository->cardPaymentMethodIsAvailable(),
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

        $language = (new PrestaShop\Module\PrestashopCheckout\Adapter\LanguageAdapter())->getLanguage($this->context->language->id);

        $this->context->smarty->assign([
            'merchantId' => $paypalAccountRepository->getMerchantId(),
            'paypalClientId' => (new PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv())->getPaypalClientId(),
            'clientToken' => $paypalOrder['body']['client_token'],
            'paypalOrderId' => $paypalOrder['body']['id'],
            'validateOrderLinkByCard' => $this->getValidateOrderLink($paypalOrder['body']['id'], 'card'),
            'validateOrderLinkByPaypal' => $this->getValidateOrderLink($paypalOrder['body']['id'], 'paypal'),
            'cardIsActive' => $paypalAccountRepository->cardPaymentMethodIsAvailable(),
            'paypalIsActive' => $paypalAccountRepository->paypalPaymentMethodIsValid(),
            'intent' => strtolower(Configuration::get(
                'PS_CHECKOUT_INTENT',
                null,
                null,
                (int) \Context::getContext()->shop->id
            )),
            'locale' => $language['locale'],
            'currencyIsoCode' => $this->context->currency->iso_code,
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
                && true === $paypalAccountRepository->cardPaymentMethodIsAvailable()
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
     * Hook executed when a slip order is created
     * Used when a partial refund is made in order to refund the patpal order
     *
     * Info : We are not using the hook actionObjectOrderSlipAddBefore due to some limitation
     * If we use this hook we will not be able to assign errors in the context->>controller to display
     * the potentially errors returned by the api. Moreover, there is also some changes triggered in other table
     * like ps_order_detail to set the quantity refunded. So even if we stop the creation of an order slip, these
     * changes will be applied.
     * Solution for now: see methods cancelPsRefund() from refund class
     *
     * @param array $params return by the hook
     */
    public function hookActionOrderSlipAdd($params)
    {
        // Check if a partial refund is made
        if (false === Tools::isSubmit('partialRefund')) {
            return false;
        }

        // Check if the order was made with the ps_checkout module
        if ($params['order']->module !== $this->name) {
            return false;
        }

        // Stop the paypal refund if the merchant want to generate a discount
        if (true === Tools::isSubmit('generateDiscountRefund')) {
            return false;
        }

        if (false === $this->merchantIsValid()) {
            $this->context->controller->errors[] = $this->l('You are not connected to PrestaShop Checkout. Cannot process to a refund.');
            //TODO: cancel refund ?

            return false;
        }

        $refunds = $params['productList'];

        $totalRefund = 0;

        foreach ($refunds as $idOrderDetails => $amountDetail) {
            $totalRefund = $totalRefund + $amountDetail['amount'];
        }

        $paypalOrderId = (new \OrderMatrice())->getOrderPaypalFromPrestashop($params['order']->id);

        if (false === $paypalOrderId) {
            $this->context->controller->errors[] = $this->l('Impossible to refund. Cannot find the PayPal Order associated to this order.');

            return false;
        }

        $currency = Currency::getCurrency($params['order']->id_currency);
        $currencyIsoCode = $currency['iso_code'];

        $refund = new PrestaShop\Module\PrestashopCheckout\Refund(false, $totalRefund, $paypalOrderId, $currencyIsoCode);
        $refundResponse = $refund->refundPaypalOrder();

        if (true === $refundResponse['error']) {
            $this->context->controller->errors = array_merge($this->context->controller->errors, $refundResponse['messages']);
            $refund->cancelPsRefund($params['order']->id);

            return false;
        }

        // @todo Add a new negative OrderPayment is wrong !
        $addOrderPayment = $refund->addOrderPayment($params['order'], $refundResponse['body']['id']);

        if (false === $addOrderPayment) {
            return false;
        }

        // change the order state to partial refund
        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = (int) $params['order']->id;

        $orderHistory->changeIdOrderState(
            (int) \Configuration::getGlobalValue('PS_CHECKOUT_STATE_PARTIAL_REFUND'),
            (int) $params['order']->id
        );

        return $orderHistory->addWithemail();
    }

    /**
     * Hook called on OrderState change
     *
     * @todo Do not perform Refund here !
     *
     * @param array $params
     *
     * @return bool
     */
    public function hookActionOrderStatusUpdate(array $params)
    {
        /** @var \Order $order */
        $order = new \Order((int) $params['id_order']);
        /** @var \OrderState $newOrderState */
        $newOrderState = $params['newOrderStatus'];
        $newOrderStateId = (int) $newOrderState->id;
        $refundOrderStateId = (int) \Configuration::get('PS_OS_REFUND');

        // if the new order state is not "Refunded" stop the refund process
        if ($newOrderStateId !== $refundOrderStateId) {
            return false;
        }

        $paypalOrderId = (new \OrderMatrice())->getOrderPaypalFromPrestashop($order->id);

        // if the order is not an order pay with paypal stop the process
        if (false === $paypalOrderId) {
            return false;
        }

        if (false === $this->merchantIsValid()) {
            // @todo This hook can be called outside of a controller...
            $this->context->controller->errors[] = $this->l('You are not connected to PrestaShop Checkout. Cannot process to a refund.');

            return false;
        }

        $currency = Currency::getCurrency($order->id_currency);
        $currencyIsoCode = $currency['iso_code'];

        $totalRefund = $order->getTotalPaid();

        $refund = new PrestaShop\Module\PrestashopCheckout\Refund(false, $totalRefund, $paypalOrderId, $currencyIsoCode);
        // @todo Do not perform Refund in this hook !
        $refundResponse = $refund->refundPaypalOrder();

        if (isset($refundResponse['error'])) {
            if (isset($refundResponse['messages']) && is_array($refundResponse['messages'])) {
                // @todo This hook can be called outside of a controller...
                $this->context->controller->errors = array_merge(
                    $this->context->controller->errors,
                    $refundResponse['messages']
                );
            } else {
                // @todo This hook can be called outside of a controller...
                $this->context->controller->errors[] = $refundResponse['message'];
            }

            return false;
        }

        // @todo Do not perform Refund in this hook !
        return $refund->doTotalRefund($order, $order->getProducts(), $refundResponse['body']['id']);
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
                    ))
                    ->setAdditionalInformation($this->generateExpressCheckoutForm());

        return $expressCheckoutPaymentOption;
    }

    public function generateExpressCheckoutForm()
    {
        $this->context->smarty->assign([
            'paypalEmail' => $this->context->cookie->__get('paypalEmail'),
            'jsHideOtherPaymentOptions' => $this->_path . 'views/js/hideOtherPaymentOptions.js?v=' . $this->version,
            'paypalLogoPath' => $this->_path . 'views/img/paypal_express.png',
        ]);

        return $this->context->smarty->fetch(
            'module:ps_checkout/views/templates/front/paymentOptions/expressCheckout.tpl'
        );
    }

    /**
     * Hook executed at the order confirmation
     */
    public function hookOrderConfirmation($params)
    {
        if ((new PrestaShop\Module\PrestashopCheckout\ShopContext())->isShop17()) {
            $order = $params['order'];
        } else {
            $order = $params['objOrder'];
        }

        if ($order->module !== $this->name) {
            return false;
        }

        if ($order->valid) {
            $this->context->smarty->assign([
                'status' => 'ok',
                'id_order' => $order->id,
                'shopIs17' => (new PrestaShop\Module\PrestashopCheckout\ShopContext())->isShop17(),
            ]);
        } else {
            $this->context->smarty->assign('status', 'failed');
        }

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
        $currentController = $this->context->controller->controller_name;

        if ('AdminPayment' !== $currentController) {
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
        $currentController = $this->context->controller->controller_name;

        if ('AdminPayment' !== $currentController) {
            return false;
        }

        $this->context->controller->addCss($this->_path . 'views/css/adminAfterHeader.css');
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
        if (false === $this->merchantIsValid()) {
            return false;
        }

        $currentPage = $this->context->controller->php_self;

        if ($currentPage != 'order') {
            return false;
        }

        $this->context->controller->registerStylesheet(
            'ps-checkout-css-paymentOptions',
            'modules/' . $this->name . '/views/css/payments.css'
        );
    }

    /**
     * Override method for addind "IGNORE" in the SQL Request to prevent duplicate entry and for getting All Carriers installed
     * Add checkbox carrier restrictions for a new module.
     *
     * @param array $shopsList
     *
     * @return bool
     */
    public function addCheckboxCarrierRestrictionsForModule(array $shopsList = [])
    {
        if (empty($shopsList)) {
            $shopsList = Shop::getShops(true, null, true);
        }

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
     * Associate with all countries allowed in geolocation management
     *
     * @return bool
     */
    public function addCheckoutPaymentForAllActivatedCountries()
    {
        $db = \Db::getInstance();
        // Get active shop ids
        $shopsList = Shop::getShops(true, null, true);
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
    }
}
