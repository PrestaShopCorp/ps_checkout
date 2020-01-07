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
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Builder\Payload\OrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Presenter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\ValidateOrder;

class ps_checkoutValidateOrderModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if ($this->checkIfContextIsValid()) {
            throw new \PrestaShopException('The context is not valid');
        }

        if ($this->checkIfPaymentOptionIsAvailable()) {
            throw new \PrestaShopException('This payment method is not available.');
        }

        parent::initContent();
    }

    public function postProcess()
    {
        $paypalOrderId = Tools::getValue('orderId');
        $paymentMethod = Tools::getValue('paymentMethod');

        if (false === $paypalOrderId) {
            throw new \PrestaShopException('Paypal order id is missing.');
        }

        if (false === $paymentMethod) {
            throw new \PrestaShopException('Paypal payment method is missing.');
        }

        $isExpressCheckout = (bool) Tools::getValue('isExpressCheckout');

        if ($isExpressCheckout) {
            $this->updatePaypalOrder($paypalOrderId);
        }

        $cart = $this->context->cart;

        $customer = new Customer($cart->id_customer);

        if (false === Validate::isLoadedObject($customer)) {
            $this->redirectToCheckout(['step' => 1]);
        }

        $currency = $this->context->currency;
        $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

        $payment = new ValidateOrder($paypalOrderId, (new PaypalAccountRepository())->getMerchantId());

        $dataOrder = [
            'cartId' => (int) $cart->id,
            'amount' => $total,
            'paymentMethod' => $paymentMethod,
            'extraVars' => ['transaction_id' => $paypalOrderId],
            'currencyId' => (int) $currency->id,
            'secureKey' => $customer->secure_key,
        ];

        // If the payment is rejected redirect the client to the last checkout step (422 error)
        if (false === $payment->validateOrder($dataOrder)) {
            $this->redirectToCheckout(['hferror' => 1]);
        }

        /** @var PaymentModule $module */
        $module = $this->module;

        Tools::redirect(
            $this->context->link->getPageLink(
                'order-confirmation',
                true,
                $this->context->language->id,
                [
                    'id_cart' => $cart->id,
                    'id_module' => $module->id,
                    'id_order' => $module->currentOrder,
                    'key' => $customer->secure_key,
                ]
            )
        );
    }

    /**
     * Update paypal order
     *
     * @param string $paypalOrderId
     *
     * @return void
     */
    private function updatePaypalOrder($paypalOrderId)
    {
        $cartPresenter = new CartPresenter($this->context);
        $cartPresenter = $cartPresenter->present();

        $builder = new OrderPayloadBuilder($cartPresenter);
        $builder->setIsUpdate(true);
        $builder->setPaypalOrderId($paypalOrderId);
        $builder->buildFullPayload();
        $payload = $builder->presentPayload()->getJson();

        $response = (new Order($this->context->link))->patch($payload);

        // Retry with minimal payload when full payload failed
        if (substr((string) $response['httpCode'], 0, 1) === '4') {
            $builder->buildMinimalPayload();
            $payload = $builder->presentPayload()->getJson();

            $response = (new Order($this->context->link))->patch($payload);
        }

        if (false === $response['status']) {
            $this->redirectToCheckout();
        }
    }

    /**
     * Redirect to checkout page
     *
     * @param array $params
     */
    private function redirectToCheckout(array $params = [])
    {
        Tools::redirect(
            $this->context->link->getPageLink(
                'order',
                true,
                $this->context->language->id,
                $params
            )
        );
    }

    /**
     * Check if the context is valid and if the module is active
     *
     * @return bool
     */
    private function checkIfContextIsValid()
    {
        $cart = $this->context->cart;

        // check if customer and adresses are correctly set in the cart
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0) {
            return false;
        }

        // check if module is active
        if (false === $this->module->active) {
            return false;
        }

        return true;
    }

    /**
     * Check that this payment option is still available in case the customer changed
     * his address just before the end of the checkout process
     *
     * @return bool
     */
    private function checkIfPaymentOptionIsAvailable()
    {
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'ps_checkout') {
                return true;
            }
        }

        return false;
    }
}
