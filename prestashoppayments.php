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
use PrestaShop\Module\PrestashopPayments\PaypalOrder;

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Prestashoppayments extends PaymentModule
{
    // hook list used by the module
    public $hookList = [
        'paymentOptions',
        'paymentReturn',
        'actionFrontControllerSetMedia'
    ];

    public function __construct()
    {
        $this->name = 'prestashoppayments';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'PrestaShop';
        $this->need_instance = 0;

        $this->module_key = '95646b26789fa27cde178690e033f9ef';

        $this->bootstrap = true;

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
        return parent::install() && $this->registerHook($this->hookList);
    }

    /**
     * Function executed at the uninstall of the module
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
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
        if (!$this->active) {
            return false;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return false;
        }

        $payment_options = [
            $this->getPaypalPaymentOption(),
            $this->getHostedFieldsPaymentOption()
        ];

        return $payment_options;
    }

    /**
     * Generate paypal payment option
     *
     * @return object PaymentOption
     */
    public function getPaypalPaymentOption()
    {
        $paypalPaymentOption = new PaymentOption();
        $paypalPaymentOption->setCallToActionText($this->l('and other payment methods'))
                            ->setAction($this->context->link->getModuleLink($this->name, 'CreateOrder', array(), true))
                            ->setAdditionalInformation($this->generatePaypalForm())
                            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/paypal.png'));

        return $paypalPaymentOption;
    }

    /**
     * Create the pay by paypal button
     *
     * @return void tpl that include the paypal button
     */
    public function generatePaypalForm()
    {
        // $paypalOrderDetail = json_decode((new PaypalOrder )->createJsonPaypalOrder($this->context->cart));
        // $paypalOrderId = (new Maasland)->createOrder($paypalOrderDetail);

        // $this->context->smarty->assign(array(
        //     'clientToken' => (new Maasland)->getClientToken(),
        //     'paypalOrderId' => $paypalOrderId, // media:addJsDef not working
        //     'orderValidationLink' => $this->context->link->getModuleLink($this->name, 'CreateOrder', array(), true)
        // ));

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
        $hostedFieldsPaymentOption->setCallToActionText($this->l('100% secure payments'))
                    ->setAction($this->context->link->getModuleLink($this->name, 'ValidateOrder', array(), true))
                    ->setForm($this->generateHostedFieldsForm())
                    ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/payement-cards.png'));

        return $hostedFieldsPaymentOption;
    }

    /**
     * Create the hosted fields form
     *
     * @return void tpl that include hosted fields
     */
    public function generateHostedFieldsForm()
    {
        $paypalOrderDetail = json_decode((new PaypalOrder )->createJsonPaypalOrder($this->context->cart));
        $paypalOrder = (new Maasland)->createOrder($paypalOrderDetail);

        $this->context->smarty->assign(array(
            'clientToken' => $paypalOrder['client_token'],
            'paypalOrderId' => $paypalOrder['id'], // media:addJsDef not working
            'orderValidationLink' => $this->context->link->getModuleLink($this->name, 'ValidateOrder', array(), true)
        ));

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

        $this->context->controller->registerJavascript(
            'prestashoppayments-paypal-api',
            'modules/'.$this->name.'/views/js/api-paypal.js'
        );

        $this->context->controller->registerStylesheet(
            'prestashoppayments-css-hostedfields',
            'modules/'.$this->name.'/views/css/hostedFields.css'
        );
    }
}
