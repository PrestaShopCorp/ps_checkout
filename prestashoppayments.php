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
use PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopPayment\Api\Maasland;

require_once __DIR__ . '/vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Prestashoppayments extends PaymentModule
{
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

    public function getPaypalPaymentOption()
    {
        $paypalPaymentOption = new PaymentOption();
        $paypalPaymentOption->setCallToActionText($this->l('Pay with paypal'))
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

    public function getHostedFieldsPaymentOption()
    {
        $hostedFieldsPaymentOption = new PaymentOption();
        $hostedFieldsPaymentOption->setCallToActionText($this->l('Hosted fields'))
                      ->setAction('https://payment-webinit.sogenactif.com/paymentInit')
                      ->setForm($this->generateHostedFieldsForm())
                      ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/views/img/mastercard.jpg'));

        return $hostedFieldsPaymentOption;
    }

    public function generateHostedFieldsForm()
    {
        $this->context->smarty->assign(array(
            'clientToken' => (new Maasland)->getClientToken()
        ));

        return $this->context->smarty->fetch('module:prestashoppayments/views/templates/front/hosted-fields.tpl');
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

    public function createJsonOrder()
    {
        // TODO : payment by a guest customer ?
        $context = $this->context;

        if (!$this->context) {
            $context = Context::getContext();
        }

        if (!Validate::isLoadedObject($context->cart)) {
            throw new PrestaShopException('Cart is not a valid object');
        }

        $cart = $context->cart;

        $cartPresenter = new CartPresenter();
        $cartPresenter = $cartPresenter->present($cart);

        dump($cartPresenter);

        // dump($this->context);
        // dump($this->context);
        dump($this->context->cart);

        // dump($this->context->cart->getProducts());
        dump($cartPresenter['products']);

        // dump(Address::initialize($cart->id_address_delivery));
        // dump(Address::initialize($cart->id_address_invoice));

        $shippingAddress = Address::initialize($cartPresenter['id_address_delivery']);
        $invoiceAddress = Address::initialize($cartPresenter['id_address_invoice']);

        dump($shippingAddress->city);
        // dump($shippingAddress->state != '0' ? State::getNameById($shippingAddress->state) : '');

        $currency = Currency::getCurrency($cart->id_currency);
        $isoCurrency = $currency['iso_code'];

        $products = $cart->getProducts();

        $items = [];

        foreach ($products as $product => $value) {
            $item = [];

            $item['name'] = $value['name'];
            $item['description'] = $value['description_short'];
            $item['sku'] = $value['unity'];
            $item['url'] = $this->context->link->getProductLink(new Product($value['id_product']));
            $item['unit_amount']['currency_code'] = $isoCurrency;
            $item['unit_amount']['value'] = $value['price'];
            $item['tax']['currency_code'] = $isoCurrency;
            $item['tax']['value'] = $value['price'] * $value['rate'] / 100;
            $item['quantity'] = $value['quantity'];
            $item['category'] = $value['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS' ;

            dump($value);
            $items[] = $item;
        }

        $payload = json_encode([
            'mode'=> 'paypal', // paypal or card
            'intent' => 'capture', // capture or authorize
            'custom_id' => $cart->id, // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => '',
            'description' => 'Order sponsorized by PS Payments',
            'soft_descriptor' => 'MR '.$shippingAddress->lastname.' '.$shippingAddress->firstname,
            'amount' => [
                'currency_code' => $isoCurrency,
                'value' => $cartPresenter['totals']['total']['amount'],
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => $isoCurrency,
                        'value' => $cartPresenter['totals']['total_excluding_tax']['amount']
                    ],
                    'shipping' => [
                        'currency_code' => $isoCurrency,
                        'value' => $cartPresenter['subtotals']['shipping']['amount']
                    ],
                    'tax_total' => [
                        'currency_code' => $isoCurrency,
                        'value' => $cartPresenter['totals']['total_including_tax']['amount'] - $cartPresenter['totals']['total_excluding_tax']['amount']
                    ],
                ]
            ],
            'items' => $items,
            'shipping' => [
                'name' => [
                    'prefix' => '', // Mr / Ms
                    'given_name' => $shippingAddress->lastname,
                    'surname' => $shippingAddress->firstname
                ],
                'address' => [
                    'address_line_1' => $shippingAddress->address1,
                    'address_line_2' => $shippingAddress->address2,
                    'admin_area_1' => State::getNameById($shippingAddress->state),
                    'admin_area_2' => $shippingAddress->city,
                    'country_code' => Country::getIsoById($shippingAddress->id_country),
                    'postal_code' => $shippingAddress->postcode
                ]
            ],
            'payer' => [
                'name' => [
                    'given_name' => $invoiceAddress->lastname,
                    'surname' => $invoiceAddress->firstname
                ],
                'email_address' => ,
                'payer_id' => '',
                'phone' => ,
                'birth_date' => ,
                'address' => [
                    'address_line_1' => $invoiceAddress->address1,
                    'address_line_2' => $invoiceAddress->address1,
                    'admin_area_1' => State::getNameById($invoiceAddress->state), //The highest level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision.
                    'admin_area_2' => $invoiceAddress->city, // A city, town, or village. Smaller than admin_area_level_1
                    'country_code' => Country::getIsoById($invoiceAddress->id_country),
                    'postal_code' => $invoiceAddress->postcode,
                ]
            ],
            'payee' => [
                'merchant_id' => '' // merchant id which is return at the end of the onboarding
            ]
        ]);

        dump(json_decode($payload));
        die();

        return $payload;
    }

    public function hookActionFrontControllerSetMedia()
    {
        (new Maasland)->createOrder($this->createJsonOrder());
        die('test');

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
