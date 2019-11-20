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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;

class ExpressCheckout
{
    const PRODUCT_MODE = 'product';
    const CART_MODE = 'cart';
    const CHECKOUT_MODE = 'checkout';

    /**
     * @var \Context
     */
    private $context;

    private $displayMode = self::PRODUCT_MODE;

    public function __construct(\Context $context)
    {
        $this->context = $context;
    }

    public function setDisplayMode($mode)
    {
        $this->displayMode = $mode;
    }

    public function render()
    {
        // dump($params);
        // dump($params['product']);
        // dump($params['product']->getId());
        // dump($params['product']->getAttributes());

        // dump($paypalOrder);
        // die()

        // if (false === $paypalOrder) {
        //     return false;
        // }
        $module = \Module::getInstanceByName('ps_checkout');

        if (false === $module->merchantIsValid()) {
            return false;
        }

        $paypalAccountRepository = new PaypalAccountRepository();

        $this->context->smarty->assign([
            'displayMode' => $this->displayMode,
            'merchantId' => $paypalAccountRepository->getMerchantId(),
            'paypalClientId' => (new PaypalEnv())->getPaypalClientId(),
            'jsExpressCheckoutPath' => \Tools::getShopDomain(true) . __PS_BASE_URI__ . 'modules/' . $module->name . '/views/js/expressCheckout.js',
            'checkoutLink' => $this->context->link->getPageLink('order', true, $this->context->language->id, ['paymentMethod' => 'paypal']),
            'tptp' => $this->context->link->getModuleLink($module->name, 'ExpressCheckout'),
            'paypalIsActive' => $paypalAccountRepository->paypalPaymentMethodIsValid(),
            'intent' => strtolower(\Configuration::get('PS_CHECKOUT_INTENT')),
            'currencyIsoCode' => $this->context->currency->iso_code,
            'isCardPaymentError' => (bool) \Tools::getValue('hferror'),
        ]);

        return $module->display($module->getPathUri(), '/views/templates/front/expressCheckout.tpl');
    }
}
