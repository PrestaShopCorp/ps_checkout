<?php
/**
 * 2007-2019 PrestaShop and Contributors
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
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Database\TableManager;
use PrestaShop\Module\PrestashopCheckout\Entity\OrderMatrice;
use PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv;
use PrestaShop\Module\PrestashopCheckout\HostedFieldsErrors;
use PrestaShop\Module\PrestashopCheckout\OrderStates;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\Presenter\Store\StorePresenter;
use PrestaShop\Module\PrestashopCheckout\Refund;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Ramsey\Uuid\Uuid;

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_checkout extends PaymentModule
{
    // hook list used by the module
    const HOOK_LIST = [
        'paymentOptions',
        'paymentReturn',
        'actionFrontControllerSetMedia',
        'actionOrderSlipAdd',
        'orderConfirmation',
        'displayAdminAfterHeader',
        'ActionAdminControllerSetMedia',
        'actionOrderStatusUpdate',
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
        'PS_PSX_FIREBASE_EMAIL' => '',
        'PS_PSX_FIREBASE_ID_TOKEN' => '',
        'PS_PSX_FIREBASE_LOCAL_ID' => '',
        'PS_PSX_FIREBASE_REFRESH_TOKEN' => '',
        'PS_PSX_FIREBASE_REFRESH_DATE' => '',
        'PS_CHECKOUT_PSX_FORM' => '',
        'PS_CHECKOUT_SHOP_UUID_V4' => '',
    ];

    public $confirmUninstall;
    public $bootstrap;
    public $controllers;

    // Needed in order to retrieve the module version easier (in api call headers) than instanciate
    // the module each time to get the version
    const VERSION = '1.2.8';

    public function __construct()
    {
        $this->name = 'ps_checkout';
        $this->tab = 'payments_gateways';

        // We cannot use the const VERSION because the const is not computed by addons marketplace
        // when the zip is uploaded
        $this->version = '1.2.8';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '82bc76354cfef947e06f1cc78f5efe2e';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PrestaShop Checkout');
        $this->description = $this->l('Provide every payment method to your customer with one module, and manage every sale where your business happens.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
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
        foreach ($this->configurationList as $name => $value) {
            if ($name === 'PS_CHECKOUT_SHOP_UUID_V4') {
                $uuid4 = Uuid::uuid4();
                $value = $uuid4->toString();
            }
            Configuration::updateValue($name, $value);
        }

        return parent::install() &&
            $this->registerHook(self::HOOK_LIST) &&
            (new OrderStates())->installPaypalStates() &&
            (new TableManager())->createTable() &&
            $this->updatePosition(\Hook::getIdByName('paymentOptions'), false, 1) &&
            $this->addCheckboxCarrierRestrictionsForModule() &&
            $this->installTabs();
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
            (new TableManager())->dropTable() &&
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

    public function getContent()
    {
        $paypalAccount = new PaypalAccountRepository();
        $psAccount = new PsAccountRepository();

        // update merchant status only if the merchant onboarding is completed
        if ($paypalAccount->onbardingIsCompleted()
            && $psAccount->onbardingIsCompleted()) {
            $paypalAccount = $paypalAccount->getOnboardedAccount();
            (new PaypalAccountUpdater($paypalAccount))->update();
        }

        Media::addJsDef([
            'store' => json_encode((new StorePresenter($this, $this->context))->present()),
        ]);

        $this->context->controller->addCss($this->_path . 'views/css/index.css');

        return $this->display(__FILE__, '/views/templates/admin/configuration.tpl');
    }

    /**
     * Add payment option at the checkout in the front office
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

        // Present an improved cart in order to create the payload
        $cartPresenter = new CartPresenter($this->context);
        $cartPresenter = $cartPresenter->present();

        // Create the payload
        $builder = new OrderPayloadBuilder($cartPresenter);
        $builder->buildFullPayload();
        $payload = $builder->presentPayload()->getJson();

        // Create the paypal order
        $paypalOrder = (new Order($this->context->link))->create($payload);

        // Retry with minimal payload when full payload failed
        if (substr((string) $paypalOrder['httpCode'], 0, 1) === '4') {
            $builder->buildMinimalPayload();
            $payload = $builder->presentPayload()->getJson();
            $paypalOrder = (new Order($this->context->link))->create($payload);
        }

        if (false === $paypalOrder['status']) {
            return false;
        }

        $paypalAccountRepository = new PaypalAccountRepository();

        $this->context->smarty->assign([
            'merchantId' => $paypalAccountRepository->getMerchantId(),
            'paypalClientId' => (new PaypalEnv())->getPaypalClientId(),
            'clientToken' => $paypalOrder['body']['client_token'],
            'paypalOrderId' => $paypalOrder['body']['id'],
            'validateOrderLinkByCard' => $this->getValidateOrderLink($paypalOrder['body']['id'], 'card'),
            'validateOrderLinkByPaypal' => $this->getValidateOrderLink($paypalOrder['body']['id'], 'paypal'),
            'cardIsActive' => $paypalAccountRepository->cardPaymentMethodIsValid(),
            'paypalIsActive' => $paypalAccountRepository->paypalPaymentMethodIsValid(),
            'intent' => strtolower(Configuration::get('PS_CHECKOUT_INTENT')),
            'currencyIsoCode' => $this->context->currency->iso_code,
            'isCardPaymentError' => (bool) Tools::getValue('hferror'),
        ]);

        $paymentMethods = \Configuration::get('PS_CHECKOUT_PAYMENT_METHODS_ORDER');

        $payment_options = [];

        // if no paymentMethods position is set, by default put credit card (hostedFields) as first position
        if (empty($paymentMethods)) {
            if (true === $paypalAccountRepository->cardPaymentMethodIsValid()) {
                array_push($payment_options, $this->getHostedFieldsPaymentOption());
            }
            if (true === $paypalAccountRepository->paypalPaymentMethodIsValid()) {
                array_push($payment_options, $this->getPaypalPaymentOption());
            }
        } else {
            $paymentMethods = json_decode($paymentMethods, true);

            foreach ($paymentMethods as $position => $paymentMethod) {
                if ($paymentMethod['name'] === 'card') {
                    if (true === $paypalAccountRepository->cardPaymentMethodIsValid()) {
                        array_push($payment_options, $this->getHostedFieldsPaymentOption());
                    }
                } else {
                    if (true === $paypalAccountRepository->paypalPaymentMethodIsValid()) {
                        array_push($payment_options, $this->getPaypalPaymentOption());
                    }
                }
            }
        }

        return $payment_options;
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

        $paypalOrderId = (new OrderMatrice())->getOrderPaypalFromPrestashop($params['order']->id);

        if (false === $paypalOrderId) {
            $this->context->controller->errors[] = $this->l('Impossible to refund. Cannot find the PayPal Order associated to this order.');

            return false;
        }

        $currency = Currency::getCurrency($params['order']->id_currency);
        $currencyIsoCode = $currency['iso_code'];

        $refund = new Refund(false, $totalRefund, $paypalOrderId, $currencyIsoCode);
        $refundResponse = $refund->refundPaypalOrder();

        if (true === $refundResponse['error']) {
            $this->context->controller->errors = array_merge($this->context->controller->errors, $refundResponse['messages']);
            $refund->cancelPsRefund($params['order']->id);

            return false;
        }

        $addOrderPayment = $refund->addOrderPayment($params['order'], $refundResponse['body']['id']);

        if (false === $addOrderPayment) {
            return false;
        }

        // change the order state to partial refund
        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = $params['order']->id;

        $orderHistory->changeIdOrderState(intval(\Configuration::get('PS_CHECKOUT_STATE_PARTIAL_REFUND')), $params['order']->id);

        if (false === $orderHistory->save()) {
            return false;
        }

        return true;
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $order = new PrestaShopCollection('Order');
        $order->where('id_order', '=', $params['id_order']);
        /** @var \Order $order */
        $order = $order->getFirst();

        $paypalOrderId = (new OrderMatrice())->getOrderPaypalFromPrestashop($order->id);

        // if the order is not an order pay with paypal stop the process
        if (false === $paypalOrderId) {
            return false;
        }

        if (false === $this->merchantIsValid()) {
            $this->context->controller->errors[] = $this->l('You are not connected to PrestaShop Checkout. Cannot process to a refund.');

            return false;
        }

        // if the new order state is not "Refunded" stop the refund process
        if ($params['newOrderStatus']->id !== intval(_PS_OS_REFUND_)) {
            return false;
        }

        $currency = Currency::getCurrency($order->id_currency);
        $currencyIsoCode = $currency['iso_code'];

        $totalRefund = $order->getTotalPaid();

        $refund = new Refund(false, $totalRefund, $paypalOrderId, $currencyIsoCode);
        $refundResponse = $refund->refundPaypalOrder();

        if (isset($refundResponse['error'])) {
            if (isset($refundResponse['messages']) && is_array($refundResponse['messages'])) {
                $this->context->controller->errors = array_merge($this->context->controller->errors, $refundResponse['messages']);
            } else {
                $this->context->controller->errors[] = $refundResponse['message'];
            }

            return false;
        }

        return $refund->doTotalRefund($order, $order->getProducts(), $refundResponse['body']['id']);
    }

    /**
     * Generate paypal payment option
     *
     * @return object PaymentOption
     */
    public function getPaypalPaymentOption()
    {
        $paypalPaymentOption = new PaymentOption();
        $paypalPaymentOption->setModuleName($this->name . '_paypal')
                            ->setCallToActionText($this->l('Pay by PayPal or other payment methods'))
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
        return $this->context->smarty->fetch('module:ps_checkout/views/templates/front/paypal.tpl');
    }

    /**
     * Generate hostfields payment option
     *
     * @return object PaymentOption
     */
    public function getHostedFieldsPaymentOption()
    {
        $hostedFieldsPaymentOption = new PaymentOption();
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
        return $this->context->smarty->fetch('module:ps_checkout/views/templates/front/hosted-fields.tpl');
    }

    /**
     * Hook executed at the order confirmation
     */
    public function hookOrderConfirmation($params)
    {
        if ($params['order']->module !== $this->name) {
            return false;
        }

        if ($params['order']->valid) {
            $this->context->smarty->assign([
                'status' => 'ok', 'id_order' => $params['order']->id,
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

        $link = $this->context->link->getAdminLink(
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
    private function getValidateOrderLink($orderId, $paymentMethod)
    {
        return $this->context->link->getModuleLink(
            $this->name,
            'ValidateOrder',
            [
                'orderId' => $orderId,
                'paymentMethod' => $paymentMethod,
            ],
            true
        );
    }

    /**
     * Check if paypal and ps account are valid
     *
     * @return bool
     */
    private function merchantIsValid()
    {
        return (new PaypalAccountRepository())->onbardingIsCompleted()
            && (new PaypalAccountRepository())->paypalEmailIsValid()
            && (new PsAccountRepository())->onbardingIsCompleted();
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

        Media::addJsDef([
            'paypalPaymentOption' => $this->name . '_paypal',
            'hostedFieldsErrors' => (new HostedFieldsErrors($this))->getHostedFieldsErrors(),
        ]);

        $this->context->controller->registerJavascript(
            'ps-checkout-paypal-api',
            'modules/' . $this->name . '/views/js/api-paypal.js'
        );

        $this->context->controller->registerStylesheet(
            'ps-checkout-css-paymentOptions',
            'modules/' . $this->name . '/views/css/paymentOptions.css'
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
        if (!$shopsList) {
            $shopsList = Shop::getShops(true, null, true);
        }

        $carriersList = Carrier::getCarriers((int) Context::getContext()->language->id, false, false, false, null, Carrier::ALL_CARRIERS);
        $allCarriers = [];

        foreach ($carriersList as $carrier) {
            $allCarriers[] = $carrier['id_reference'];
        }

        foreach ($shopsList as $idShop) {
            foreach ($allCarriers as $idCarrier) {
                $addModuleInCarrier = Db::getInstance()->execute('INSERT IGNORE
                    INTO `' . _DB_PREFIX_ . 'module_carrier` (`id_module`, `id_shop`, `id_reference`)
                    VALUES (' . (int) $this->id . ', "' . (int) $idShop . '", ' . (int) $idCarrier . ')');

                if (!$addModuleInCarrier) {
                    return false;
                }
            }
        }

        return true;
    }
}
