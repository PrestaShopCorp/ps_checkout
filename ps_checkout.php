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
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\Module\PrestashopCheckout\Api\Maasland;
use PrestaShop\Module\PrestashopCheckout\GenerateJsonPaypalOrder;
use PrestaShop\Module\PrestashopCheckout\HostedFieldsErrors;
use PrestaShop\Module\PrestashopCheckout\Translations\Translations;
use PrestaShop\Module\PrestashopCheckout\Refund;
use PrestaShop\Module\PrestashopCheckout\PaypalOrderRepository;
use PrestaShop\Module\PrestashopCheckout\OrderStates;
use PrestaShop\Module\PrestashopCheckout\StorePresenter;
use Ramsey\Uuid\Uuid;

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class ps_checkout extends PaymentModule
{
    // hook list used by the module
    public $hookList = [
        'paymentOptions',
        'paymentReturn',
        'actionFrontControllerSetMedia',
        'actionOrderSlipAdd',
        'orderConfirmation',
    ];

    public $configurationList = array(
        'PS_CHECKOUT_INTENT' => 'CAPTURE',
        'PS_CHECKOUT_PAYMENT_METHODS_ORDER' => '',
        'PS_CHECKOUT_PAYPAL_ID_MERCHANT' => '',
        'PS_CHECKOUT_FIREBASE_PUBLIC_API_KEY' => 'AIzaSyASHFE2F08ncoOH9NhoCF8_6z7qnoLVKSA',
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
        $this->version = '1.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '95646b26789fa27cde178690e033f9ef';

        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('PrestaShop Checkout');
        $this->description = $this->l('New prestashop payment system');

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
            $this->registerHook($this->hookList) &&
            (new OrderStates())->installPaypalStates();
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

        return parent::uninstall();
    }

    public function getContent()
    {
        $translations = (new Translations($this))->getTranslations();

        Media::addJsDef(array(
            'prestashopCheckoutAjax' => $this->context->link->getAdminLink('AdminAjaxPrestashopCheckout'),
            'contextLocale' => $this->context->language->locale,
            'translations' => json_encode($translations),
            'store' => json_encode((new StorePresenter)->present())
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

        if (false === $this->checkCurrency($params['cart'])) {
            return false;
        }

        $payload = (new GenerateJsonPaypalOrder())->create($this->context);
        $paypalOrder = (new Maasland($this->context->link))->createOrder($payload);

        if (false === $paypalOrder) {
            return false;
        }

        $this->context->smarty->assign(array(
            'clientToken' => $paypalOrder['client_token'],
            'paypalOrderId' => $paypalOrder['id'],
            'orderValidationLink' => $this->context->link->getModuleLink($this->name, 'ValidateOrder', array(), true),
            'intent' => strtolower(Configuration::get('PS_CHECKOUT_INTENT')),
        ));

        $paymentMethods = \Configuration::get('PS_CHECKOUT_PAYMENT_METHODS_ORDER');

        $payment_options = array();

        // if no paymentMethods position is set, by default put credit card (hostedFields) as first position
        if (true === empty($paymentMethods)) {
            $payment_options = array(
                $this->getPaypalPaymentOption(),
                $this->getHostedFieldsPaymentOption(),
            );
        } else {
            $paymentMethods = json_decode($paymentMethods, true);

            foreach ($paymentMethods as $position => $paymentMethod) {
                if ($paymentMethod['name'] === 'card') {
                    array_push($payment_options, $this->getHostedFieldsPaymentOption());
                } else {
                    array_push($payment_options, $this->getPaypalPaymentOption());
                }
            }
        }

        return $payment_options;
    }

    /**
     * Hook executed when a slip order is created
     * Used when a partial refund is made in order to refund the patpal order
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

        $refunds = $params['productList'];

        $totalRefund = 0;

        foreach ($refunds as $idOrderDetails => $amountDetail) {
            $totalRefund = $totalRefund + $amountDetail['quantity'] * $amountDetail['amount'];
        }

        $paypalOrderId = (new PaypalOrderRepository())->getPaypalOrderIdByPsOrderRef($params['order']->reference);

        if (false === $paypalOrderId) {
            $this->context->controller->errors[] = $this->l('Impossible to refund. Cannot find the PayPal Order associated to this order.');

            return false;
        }

        $currency = Currency::getCurrency($params['order']->id_currency);
        $currencyIsoCode = $currency['iso_code'];

        $refund = new Refund($paypalOrderId, $currencyIsoCode, $totalRefund);
        $refund = $refund->refundOrder();

        if (true === $refund['error']) {
            foreach ($refund['messages'] as $message) {
                $this->context->controller->errors[] = $message;
            }

            return false;
        }
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
                            ->setCallToActionText($this->l('and other payment methods'))
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
                    ->setCallToActionText($this->l('100% secure payments'))
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
