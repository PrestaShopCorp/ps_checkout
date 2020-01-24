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

use PrestaShop\Module\PrestashopCheckout\HostedFieldsErrors;
use PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv;
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

class ps_checkoutPaymentCard16ModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_column_left = false;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;

        if (false === $this->module->active) {
            return false;
        }

        if (false === $this->module->merchantIsValid()) {
            return false;
        }

        if (false === $this->module->checkCurrency($cart)) {
            return false;
        }

        $paypalAccountRepository = new PaypalAccountRepository();

        if (false === $paypalAccountRepository->cardPaymentMethodIsValid()) {
            return false;
        }

        $paypalOrder = new CreatePaypalOrderHandler($this->context);
        $paypalOrder = $paypalOrder->handle();

        $this->context->smarty->assign([
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'merchantId' => $paypalAccountRepository->getMerchantId(),
            'paypalClientId' => (new PaypalEnv())->getPaypalClientId(),
            'clientToken' => $paypalOrder['body']['client_token'],
            'paypalOrderId' => $paypalOrder['body']['id'],
            'validateOrderLinkByCard' => $this->module->getValidateOrderLink($paypalOrder['body']['id'], 'card'),
            'intent' => strtolower(\Configuration::get('PS_CHECKOUT_INTENT')),
            'currencyIsoCode' => $this->context->currency->iso_code,
            'isCardPaymentError' => (bool) Tools::getValue('hferror'),
            'modulePath' => $this->module->getPathUri(),
            'paypalPaymentOption' => $this->module->name . '_paypal',
            'hostedFieldsErrors' => (new HostedFieldsErrors($this->module))->getHostedFieldsErrors(),
            'jsPathInitPaypalSdk' => $this->module->getPathUri() . 'views/js/initPaypalAndCard.js',
        ]);

        $this->context->controller->addJS($this->module->getPathUri() . 'views/js/initCardPayment.js');
        $this->context->controller->addCSS($this->module->getPathUri() . 'views/css/payments16.css');

        $this->setTemplate('paymentCardConfirmation.tpl');
    }
}
