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
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use PrestaShop\Module\PrestashopPayments\Api\Maasland;
use PrestaShop\Module\PrestashopPayments\GenerateJsonPaypalOrder;
use PrestaShop\Module\PrestashopPayments\Payment;
use PrestaShop\Module\PrestashopPayments\HostedFieldsErrors;
use PrestaShop\Module\PrestashopPayments\Translations\Translations;

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class PrestashopPayments extends PaymentModule
{
    // hook list used by the module
    public $hookList = [
        'paymentOptions',
        'paymentReturn',
        'actionFrontControllerSetMedia',
        'actionOrderSlipAdd'
    ];

    public $configurationList = array(
        'PS_PAY_INTENT' => 'CAPTURE',
        'PS_PAY_FIREBASE_UID' => '',
        'PS_PAY_FIREBASE_REFRESH_TOKEN' => '',
    );

    public function __construct()
    {
        $this->name = 'prestashoppayments';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '95646b26789fa27cde178690e033f9ef';

        $this->bootstrap = true;

        $this->controllers = array('AdminAjaxPrestashopPayments');

        parent::__construct();

        $this->displayName = $this->l('Prestashop payments');
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
            Configuration::updateValue($name, $value);
        }

        return parent::install() && $this->registerHook($this->hookList);
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

        $firebaseAccount = array(
            'uid' => Configuration::get('PS_PAY_FIREBASE_UID'),
            'refreshToken' => Configuration::get('PS_PAY_FIREBASE_REFRESH_TOKEN')
        );

        Media::addJsDef(array(
            'prestashopPaymentsAjax' => $this->context->link->getAdminLink('AdminAjaxPrestashopPayments'),
            'translations' => json_encode($translations),
            'firebaseAccount' => json_encode($firebaseAccount)
        ));

        return $this->display(__FILE__, '/views/app/app.tpl');
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

        $payload = (new GenerateJsonPaypalOrder)->create($this->context);
        $paypalOrder = (new Maasland)->createOrder($payload);

        if (false === $paypalOrder) {
            return false;
        }

        $this->context->smarty->assign(array(
            'clientToken' => $paypalOrder['client_token'],
            'paypalOrderId' => $paypalOrder['id'],
            'orderValidationLink' => $this->context->link->getModuleLink($this->name, 'ValidateOrder', array(), true),
            'intent' => strtolower(Configuration::get('PS_PAY_INTENT'))
        ));

        $payment_options = [
            $this->getPaypalPaymentOption(),
            $this->getHostedFieldsPaymentOption()
        ];

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
        if (false === Tools::isSubmit('partialRefund')) {
            return false;
        }

        $refunds = $params['productList'];

        $totalRefund = 0;

        foreach ($refunds as $idOrderDetails => $amountDetail) {
            $totalRefund = $totalRefund + $amountDetail['quantity'] * $amountDetail['amount'];
        }

        $orderPayment = OrderPayment::getByOrderId($params['order']->id);

        if (false === is_array($orderPayment)) {
            return false;
        }

        $orderPayment = current($orderPayment);

        $paypalOrderId = $orderPayment->transaction_id;

        if (false === isset($paypalOrderId)) {
            return false;
        }

        $currency = \Currency::getCurrency(Configuration::get('PS_CURRENCY_DEFAULT'));
        $currencyIsoCode = $currency['iso_code'];

        $payment = new Payment($paypalOrderId);

        $refund = $payment->refundOrder($totalRefund, $currencyIsoCode);

        if (false === $refund) {
            die('cannot process to a refund. Error form paypal');
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
        $paypalPaymentOption->setModuleName($this->name.'_paypal')
                            ->setCallToActionText($this->l('and other payment methods'))
                            ->setAction($this->context->link->getModuleLink($this->name, 'CreateOrder', array(), true))
                            ->setAdditionalInformation($this->generatePaypalForm())
                            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/paypal.png'));

        return $paypalPaymentOption;
    }

    /**
     * Create the pay by paypal button
     *
     * @return string tpl that include the paypal button
     */
    public function generatePaypalForm()
    {
        return $this->context->smarty->fetch('module:prestashoppayments/views/templates/front/paypal.tpl');
    }

    /**
     * Generate hostfields payment option
     *
     * @return object PaymentOption
     */
    public function getHostedFieldsPaymentOption()
    {
        $hostedFieldsPaymentOption = new PaymentOption();
        $hostedFieldsPaymentOption->setModuleName($this->name.'_hostedFields')
                    ->setCallToActionText($this->l('100% secure payments'))
                    ->setAction($this->context->link->getModuleLink($this->name, 'ValidateOrder', array(), true))
                    ->setForm($this->generateHostedFieldsForm())
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/payement-cards.png'));

        return $hostedFieldsPaymentOption;
    }

    /**
     * Create the hosted fields form
     *
     * @return string tpl that include hosted fields
     */
    public function generateHostedFieldsForm()
    {
        return $this->context->smarty->fetch('module:prestashoppayments/views/templates/front/hosted-fields.tpl');
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
     *
     * @return void
     */
    public function hookActionFrontControllerSetMedia()
    {
        $currentPage = $this->context->controller->php_self;

        if ($currentPage != 'order') {
            return false;
        }

        Media::addJsDef(array(
            'paypalPaymentOption' => $this->name.'_paypal',
            'hostedFieldsErrors' => (new HostedFieldsErrors($this))->getHostedFieldsErrors()
        ));

        $this->context->controller->registerJavascript(
            'prestashoppayments-paypal-api',
            'modules/'.$this->name.'/views/js/api-paypal.js'
        );

        $this->context->controller->registerStylesheet(
            'prestashoppayments-css-paymentOptions',
            'modules/'.$this->name.'/views/css/paymentOptions.css'
        );
    }
}
