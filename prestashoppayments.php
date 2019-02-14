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

if (!defined('_PS_VERSION_')) {
    exit;
}

class Prestashoppayments extends PaymentModule
{
    public $hookList = [
        'paymentOptions',
        'paymentReturn'
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

    public function install()
    {
        return parent::install() && $this->registerHook($this->hookList);
    }

    public function uninstall()
    {
        return parent::uninstall();
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = [
            $this->getPaypalPaymentOption(),
            $this->getHostedFieldPaymentOption()
        ];

        return $payment_options;
    }

    public function getPaypalPaymentOption()
    {
        $paypalPaymentOption = new PaymentOption();
        $paypalPaymentOption->setCallToActionText($this->l(''))
                            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
                            ->setInputs([
                                'token' => [
                                    'name' =>'token',
                                    'type' =>'hidden',
                                    'value' =>'12345689',
                                ],
                            ])
                            ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/mastercard.jpg'));

        return $paypalPaymentOption;
    }

    public function getHostedFieldPaymentOption()
    {
        $offlineOption = new PaymentOption();
        $offlineOption->setCallToActionText($this->l('Hosted fields'))
                      ->setAction('https://payment-webinit.sogenactif.com/paymentInit')
                      ->setForm($this->generateHostedFieldsForm())
                      ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/mastercard.jpg'));

        return $offlineOption;
    }

    public function generateHostedFieldsForm()
    {
        return $this->context->smarty->fetch('module:prestashoppayments/views/templates/front/payment_form.tpl');
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }
}
