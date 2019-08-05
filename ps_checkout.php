<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or ad dto this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
use Ramsey\Uuid\Uuid;
use PrestaShop\Module\PrestashopCheckout\Refund;
use PrestaShop\Module\PrestashopCheckout\Merchant;
use PrestaShop\Module\PrestashopCheckout\Api\Order;
use PrestaShop\Module\PrestashopCheckout\Environment;
use PrestaShop\Module\PrestashopCheckout\OrderStates;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\Module\PrestashopCheckout\HostedFieldsErrors;
use PrestaShop\Module\PrestashopCheckout\MerchantRepository;
use PrestaShop\Module\PrestashopCheckout\Entity\OrderMatrice;
use PrestaShop\Module\PrestashopCheckout\Database\TableManager;
use PrestaShop\Module\PrestashopCheckout\GenerateJsonPaypalOrder;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;
use PrestaShop\Module\PrestashopCheckout\Store\Presenter\StorePresenter;

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class ps_checkout extends PaymentModule
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

    public $configurationList = array(
        'PS_CHECKOUT_INTENT' => 'CAPTURE',
        'PS_CHECKOUT_MODE' => 'LIVE',
        'PS_CHECKOUT_PAYMENT_METHODS_ORDER' => '',
        'PS_CHECKOUT_PAYPAL_ID_MERCHANT' => '',
        'PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT' => '',
        'PS_CHECKOUT_PAYPAL_EMAIL_STATUS' => '',
        'PS_CHECKOUT_PAYPAL_PAYMENT_STATUS' => '',
        'PS_CHECKOUT_CARD_PAYMENT_STATUS' => '',
        'PS_CHECKOUT_FIREBASE_EMAIL' => '',
        'PS_CHECKOUT_FIREBASE_ID_TOKEN' => '',
        'PS_CHECKOUT_FIREBASE_LOCAL_ID' => '',
        'PS_CHECKOUT_FIREBASE_REFRESH_TOKEN' => '',
        'PS_CHECKOUT_SHOP_UUID_V4' => '',
    );

    public function __construct()
    {
        $this->name = 'ps_checkout';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.5';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '95646b26789fa27cde178690e033f9ef';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PrestaShop Checkout');
        $this->description = $this->l('Provide every payment method to your customer with one module, and manage every sale where your business happens.');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
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
            (new TableManager())->createTable();
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
            (new TableManager())->dropTable();
    }

    public function getContent()
    {
        // update merchant status
        $merchant = (new Merchant((new MerchantRepository())->getMerchantId()));
        $merchant->update();

        $translations = (new Translations($this))->getTranslations();

        Media::addJsDef(array(
            'prestashopCheckoutAjax' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
            'contextLocale' => $this->context->language->locale,
            'translations' => json_encode($translations),
            'store' => json_encode((new StorePresenter())->present()),
        ));

        $this->context->controller->addCss($this->_path . 'views/css/index.css');

        return $this->display(__FILE__, '/views/templates/admin/configuration.tpl');
    }

    /**
     * Add payment option at the checkout in the front office
     *
     * @param array params return by the hook
     *
     * @return array all payment option available
     */
    public function hookPaymentOptions($params)
    {
        if (false === $this->active) {
            return false;
        }

        $merchantRepository = new MerchantRepository();
        if (false === $merchantRepository->merchantIsValid()) {
            return false;
        }

        if (false === $this->checkCurrency($params['cart'])) {
            return false;
        }

        $payload = (new GenerateJsonPaypalOrder())->create($this->context);
        $paypalOrder = (new Order($this->context->link))->create($payload);

        if (false === $paypalOrder) {
            return false;
        }

        $this->context->smarty->assign(array(
            'merchantId' => $merchantRepository->getMerchantId(),
            'paypalClientId' => (new Environment())->getPaypalClientId(),
            'clientToken' => $paypalOrder['client_token'],
            'paypalOrderId' => $paypalOrder['id'],
            'orderValidationLink' => $this->context->link->getModuleLink($this->name, 'ValidateOrder', array(), true),
            'cardIsActive' => $merchantRepository->cardPaymentMethodIsValid(),
            'paypalIsActive' => $merchantRepository->paypalPaymentMethodIsValid(),
            'intent' => strtolower(Configuration::get('PS_CHECKOUT_INTENT')),
            'currencyIsoCode' => $this->context->currency->iso_code,
        ));

        $paymentMethods = \Configuration::get('PS_CHECKOUT_PAYMENT_METHODS_ORDER');

        $payment_options = array();

        // if no paymentMethods position is set, by default put credit card (hostedFields) as first position
        if (empty($paymentMethods)) {
            if (true === $merchantRepository->cardPaymentMethodIsValid()) {
                array_push($payment_options, $this->getHostedFieldsPaymentOption());
            }
            if (true === $merchantRepository->paypalPaymentMethodIsValid()) {
                array_push($payment_options, $this->getPaypalPaymentOption());
            }
        } else {
            $paymentMethods = json_decode($paymentMethods, true);

            foreach ($paymentMethods as $position => $paymentMethod) {
                if ($paymentMethod['name'] === 'card') {
                    if (true === $merchantRepository->cardPaymentMethodIsValid()) {
                        array_push($payment_options, $this->getHostedFieldsPaymentOption());
                    }
                } else {
                    if (true === $merchantRepository->paypalPaymentMethodIsValid()) {
                        array_push($payment_options, $this->getPaypalPaymentOption());
                    }
                }
            }
        }

        return $payment_options;
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
     * @param array params return by the hook
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

        if (false === (new MerchantRepository())->merchantIsValid()) {
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

        $refund = new Refund($totalRefund, $paypalOrderId, $currencyIsoCode);
        $refundResponse = $refund->refundPaypalOrder();

        if (true === $refundResponse['error']) {
            $this->context->controller->errors = array_merge($this->context->controller->errors, $refundResponse['messages']);
            $refund->cancelPsRefund($params['order']->id);

            return false;
        }

        // change the order state to partial refund
        $orderHistory = new \OrderHistory();
        $orderHistory->id_order = $params['order']->id;

        $orderHistory->changeIdOrderState(Configuration::get('PS_CHECKOUT_STATE_PARTIAL_REFUND'), $params['order']->id);

        if (false === $orderHistory->save()) {
            return false;
        }

        $refund->addOrderPayment($params['order']);

        return true;
    }

    public function hookActionOrderStatusUpdate($params)
    {
        $order = new PrestaShopCollection('Order');
        $order->where('id_order', '=', $params['id_order']);
        $order = $order->getFirst();

        $paypalOrderId = (new OrderMatrice())->getOrderPaypalFromPrestashop($order->id);

        // if the order is not an order pay with paypal stop the process
        if (false === $paypalOrderId) {
            return false;
        }

        if (false === (new MerchantRepository())->merchantIsValid()) {
            $this->context->controller->errors[] = $this->l('You are not connected to PrestaShop Checkout. Cannot process to a refund.');

            return false;
        }

        // if the new order state is not "Refunded" stop the refund process
        if ($params['newOrderStatus']->id !== (int) _PS_OS_REFUND_) {
            return false;
        }

        $currency = Currency::getCurrency($order->id_currency);
        $currencyIsoCode = $currency['iso_code'];

        $totalRefund = $order->getTotalPaid();

        $refund = new Refund($totalRefund, $paypalOrderId, $currencyIsoCode);
        $refundResponse = $refund->refundPaypalOrder();

        if (true === $refundResponse['error']) {
            $this->context->controller->errors = array_merge($this->context->controller->errors, $refundResponse['messages']);

            return false;
        }

        return $refund->doTotalRefund($order, $order->getProducts());
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
                            ->setAction($this->context->link->getModuleLink($this->name, 'CreateOrder', array(), true))
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
                    ->setAction($this->context->link->getModuleLink($this->name, 'ValidateOrder', array(), true))
                    ->setForm($this->generateHostedFieldsForm())
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/payement-cards.png'));

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
            $this->context->smarty->assign(array(
                'status' => 'ok', 'id_order' => $params['order']->id,
            ));
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

        if (false === (new MerchantRepository())->merchantIsValid()) {
            return false;
        }

        if ('AdminPayment' !== $currentController) {
            return false;
        }

        $link = $this->context->link->getAdminLink(
            'AdminModules',
            true,
            array(),
            array(
                'configure' => 'ps_checkout',
            )
        );

        $this->context->smarty->assign(array(
            'imgPath' => $this->_path . 'views/img/',
            'configureLink' => $link,
        ));

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
     * Load asset on the front office
     */
    public function hookActionFrontControllerSetMedia()
    {
        $currentPage = $this->context->controller->php_self;

        if ($currentPage != 'order') {
            return false;
        }

        Media::addJsDef(array(
            'paypalPaymentOption' => $this->name . '_paypal',
            'hostedFieldsErrors' => (new HostedFieldsErrors($this))->getHostedFieldsErrors(),
        ));

        $this->context->controller->registerJavascript(
            'ps-checkout-paypal-api',
            'modules/' . $this->name . '/views/js/api-paypal.js'
        );

        $this->context->controller->registerStylesheet(
            'ps-checkout-css-paymentOptions',
            'modules/' . $this->name . '/views/css/paymentOptions.css'
        );
    }
}
