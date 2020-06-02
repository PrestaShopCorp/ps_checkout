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
use PrestaShop\Module\PrestashopCheckout\Adapter\LanguageAdapter;
use PrestaShop\Module\PrestashopCheckout\Builder\PayPalSdkLink\PayPalSdkLinkBuilder;
use PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv;
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;
use PrestaShop\Module\PrestashopCheckout\HostedFieldsErrors;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

class ps_checkoutPaymentPaypal16ModuleFrontController extends ModuleFrontController
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

        /** @var \Ps_checkout */
        $module = $this->module;

        if (false === $module->active) {
            $this->redirectToHomePage();
        }

        if (false === $module->merchantIsValid()) {
            $this->redirectToHomePage();
        }

        if (false === $module->checkCurrency($cart)) {
            $this->redirectToHomePage();
        }

        $paypalAccountRepository = new PaypalAccountRepository();

        if (false === $paypalAccountRepository->paypalPaymentMethodIsValid()) {
            $this->redirectToHomePage();
        }

        $paypalOrder = new CreatePaypalOrderHandler($this->context);
        $paypalOrder = $paypalOrder->handle();

        $language = (new LanguageAdapter())->getLanguage($this->context->language->id);
        $paypalSdkLink = new PayPalSdkLinkBuilder();
        $paypalSdkLink->enableDisplayOnlySmartButtons();

        $this->context->smarty->assign([
            'paypalSdkLink' => $paypalSdkLink->buildLink(),
            'nbProducts' => $cart->nbProducts(),
            'total' => $cart->getOrderTotal(true, Cart::BOTH),
            'merchantId' => $paypalAccountRepository->getMerchantId(),
            'paypalClientId' => (new PaypalEnv())->getPaypalClientId(),
            'clientToken' => $paypalOrder['body']['client_token'],
            'paypalOrderId' => $paypalOrder['body']['id'],
            'validateOrderLinkByPaypal' => $module->getValidateOrderLink($paypalOrder['body']['id'], 'paypal'),
            'intent' => strtolower(Configuration::get(
                'PS_CHECKOUT_INTENT',
                null,
                null,
                (int) \Context::getContext()->shop->id
            )),
            'locale' => $language['locale'],
            'currencyIsoCode' => $this->context->currency->iso_code,
            'isCardPaymentError' => (bool) Tools::getValue('hferror'),
            'modulePath' => $module->getPathUri(),
            'paypalPaymentOption' => $module->name . '_paypal',
            'hostedFieldsErrors' => (new HostedFieldsErrors($module))->getHostedFieldsErrors(),
        ]);

        $this->context->controller->addJS(
            $module->getPathUri() . 'views/js/initPaypalPayment.js?v=' . $this->module->version,
            false
        );
        $this->context->controller->addCSS(
            $module->getPathUri() . 'views/css/payments16.css?v=' . $this->module->version,
            'all',
            null,
            false
        );

        $this->setTemplate('paymentPaypalConfirmation.tpl');
    }

    /**
     * Redirect to the home page
     */
    private function redirectToHomePage()
    {
        Tools::redirect(
            $this->context->link->getPageLink(
                'index',
                true,
                $this->context->language->id
            )
        );
    }
}
