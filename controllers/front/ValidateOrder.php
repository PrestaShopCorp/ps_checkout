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

use PrestaShop\Module\PrestashopPayment\Api\Maasland;

class prestashoppaymentsValidateOrderModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        // TODO : add some check
        $orderId = Tools::getValue('orderId');

        $cart = $this->context->cart;

        if ($cart->id_customer == 0 ||
            $cart->id_address_delivery == 0 ||
            $cart->id_address_invoice == 0 ||
            !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        // Check that this payment option is still available in case the customer changed
        // his address just before the end of the checkout process
        $authorized = false;
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'prestashoppayments') {
                $authorized = true;
                break;
            }
        }

        if (!$authorized) {
            die($this->l('This payment method is not available.'));
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        $this->module->validateOrder(
            (int)$cart->id,
            Configuration::get('PS_OS_CHEQUE'),
            $total,
            $this->module->displayName,
            null,
            array('transaction_id' => $orderId),
            (int)$currency->id,
            false,
            $customer->secure_key
        );

        // TODO : patch the order in order to update the order id with the order id of the prestashop order

        $responseCaptureOrder = (new Maasland)->captureOrder($orderId);

        $order = new OrderHistory();
        $order->id_order = $this->module->currentOrder;

        if ($responseCaptureOrder['status'] === 'COMPLETED') {
            $order->changeIdOrderState(_PS_OS_PAYMENT_, $this->module->currentOrder);
        } else {
            $order->changeIdOrderState(_PS_OS_ERROR_, $this->module->currentOrder);
        }

        $order->save();

        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
    }
}