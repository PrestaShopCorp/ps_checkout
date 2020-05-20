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
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\ValidateOrder;

class ps_checkoutValidateOrderModuleFrontController extends ModuleFrontController
{
    /** @var Ps_checkout */
    public $module;

    public function postProcess()
    {
        try {
            if (false === $this->checkIfContextIsValid()) {
                throw new PsCheckoutException('The context is not valid', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            if (false === $this->checkIfPaymentOptionIsAvailable()) {
                throw new PsCheckoutException('This payment method is not available.', PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE);
            }

            $paypalOrderId = Tools::getValue('orderId');
            $paymentMethod = Tools::getValue('paymentMethod');

            if (true === empty($paypalOrderId) || false === Validate::isGenericName($paypalOrderId)) {
                throw new PsCheckoutException('Paypal order id is missing.', PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING);
            }

            if (true === empty($paymentMethod) || false === Validate::isGenericName($paymentMethod)) {
                throw new PsCheckoutException('Paypal payment method is missing.', PsCheckoutException::PAYPAL_PAYMENT_METHOD_MISSING);
            }

            $isExpressCheckout = (bool) Tools::getValue('isExpressCheckout');

            $this->module->getLogger()->info(sprintf(
                'ValidateOrder PayPal Order Id : %s Payment Method : %s Express Checkout : %s Cart : %s',
                $paypalOrderId,
                $paymentMethod,
                $isExpressCheckout ? 'true' : 'false',
                Validate::isLoadedObject($this->context->cart) ? (int) $this->context->cart->id : 0
            ));

            if ($isExpressCheckout) {
                // API call here
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
                'currencyId' => (int) $currency->id,
                'secureKey' => $customer->secure_key,
            ];

            // If the payment is rejected redirect the client to the last checkout step (422 error)
            // API call here
            $payment->validateOrder($dataOrder);

            $this->redirectToOrderConfirmation();
        } catch (Exception $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * @see FrontController::initContent()
     *
     * @throws PrestaShopException
     */
    public function initContent()
    {
        parent::initContent();

        $this->setTemplate('module:ps_checkout/views/templates/front/validateOrder.tpl');
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
        $paypalOrder = new CreatePaypalOrderHandler($this->context);
        $response = $paypalOrder->handle(false, true, $paypalOrderId);

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
     * Redirect to order confirmation page
     */
    private function redirectToOrderConfirmation()
    {
        Tools::redirect(
            $this->context->link->getPageLink(
                'order-confirmation',
                true,
                $this->context->language->id,
                [
                    'id_cart' => $this->context->cart->id,
                    'id_module' => $this->module->id,
                    'id_order' => $this->module->currentOrder,
                    'key' => $this->context->customer->secure_key,
                ]
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
        return true === (bool) $this->module->active
            && true === Validate::isLoadedObject($this->context->cart)
            && true === Validate::isUnsignedInt($this->context->cart->id_customer)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_delivery)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_invoice);
    }

    /**
     * Check that this payment option is still available in case the customer changed
     * his address just before the end of the checkout process
     *
     * @return bool
     */
    private function checkIfPaymentOptionIsAvailable()
    {
        $modules = Module::getPaymentModules();

        if (empty($modules)) {
            return false;
        }

        foreach ($modules as $module) {
            if (isset($module['name']) && $this->module->name === $module['name']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Exception $exception
     */
    private function handleException(Exception $exception)
    {
        $exceptionMessageForCustomer = $this->l('Error processing payment', 'translations');
        $exceptionCode = $exception->getCode();
        $notifyCustomerService = true;

        switch ($exception->getCode()) {
            case PsCheckoutException::PAYPAL_PAYMENT_CARD_ERROR:
            case PayPalException::CARD_TYPE_NOT_SUPPORTED:
            case PayPalException::INVALID_SECURITY_CODE_LENGTH:
            case PayPalException::CURRENCY_NOT_SUPPORTED_FOR_CARD_TYPE:
            case PayPalException::CURRENCY_NOT_SUPPORTED_FOR_COUNTRY:
            case PayPalException::INSTRUMENT_DECLINED:
            case PayPalException::MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED:
            case PayPalException::PAYER_ACCOUNT_LOCKED_OR_CLOSED:
            case PayPalException::PAYER_ACCOUNT_RESTRICTED:
            case PayPalException::PAYER_CANNOT_PAY:
            case PayPalException::PAYER_COUNTRY_NOT_SUPPORTED:
            case PayPalException::REDIRECT_PAYER_FOR_ALTERNATE_FUNDING:
            case PayPalException::TRANSACTION_BLOCKED_BY_PAYEE:
            case PayPalException::TRANSACTION_REFUSED:
                $this->redirectToCheckout(['step' => 4, 'paymentError' => $exception->getCode()]);
                break;
            case PayPalException::ORDER_ALREADY_CAPTURED:
                $this->module->currentOrder = (new \OrderMatrice())->getOrderPrestashopFromPaypal(Tools::getValue('orderId'));

                if (false === empty($this->module->currentOrder)) {
                    $this->redirectToOrderConfirmation();
                }

                $exceptionMessageForCustomer = $this->l('Order cannot be saved', 'translations');
                break;
            case PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE:
                $exceptionMessageForCustomer = $this->l('This payment method is unavailable', 'translations');
                break;
            case PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING:
                $exceptionMessageForCustomer = $this->l('PayPal order identifier is missing', 'translations');
                $notifyCustomerService = false;
                break;
            case PsCheckoutException::PAYPAL_PAYMENT_METHOD_MISSING:
                $exceptionMessageForCustomer = $this->l('PayPal payment method is missing', 'translations');
                $notifyCustomerService = false;
                break;
            case PsCheckoutException::PRESTASHOP_CONTEXT_INVALID:
                $exceptionMessageForCustomer = $this->l('Cart is invalid', 'translations');
                $notifyCustomerService = false;
                break;
        }

        if (true === $notifyCustomerService) {
            $this->notifyCustomerService($exception);
        }



        $this->context->smarty->assign([
            'alertClass' => 'danger',
            'exceptionCode' => $exceptionCode,
            'exceptionMessageForCustomer' => $exceptionMessageForCustomer,
        ]);
    }

    private function notifyCustomerService(Exception $exception)
    {
        $contacts = Contact::getContacts((int) $this->context->language->id);

        if (empty($contacts)) {
            return;
        }

        $message = $this->l('This message is sent automatically by module PrestaShop Checkout', 'translations') . PHP_EOL . PHP_EOL;
        $message .= $this->l('A customer encountered a processing payment error :', 'translations') . PHP_EOL;
        $message .= $this->l('Customer identifier:', 'translations') . ' ' . $this->context->customer->id . PHP_EOL;
        $message .= $this->l('Cart identifier:', 'translations') . ' ' . $this->context->cart->id . PHP_EOL;
        $message .= $this->l('PayPal order identifier:', 'translations') . ' ' . Tools::getValue('orderId') . PHP_EOL;
        $message .= $this->l('Exception identifier:', 'translations') . ' ' . $exception->getCode() . PHP_EOL;
        $message .= $this->l('Exception detail:', 'translations') . ' ' . $exception->getMessage() . PHP_EOL . PHP_EOL;
        $message .= $this->l('If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.', 'translations') . PHP_EOL;

        $customerThread = new CustomerThread();
        $customerThread->id_customer = (int) $this->context->customer->id;
        $customerThread->id_shop = (int) $this->context->shop->id;
        $customerThread->id_order = (int) $this->module->currentOrder;
        $customerThread->id_lang = (int) $this->context->language->id;
        $customerThread->id_contact = (int) $contacts[0]['id_contact']; // Should be configurable
        $customerThread->email = $this->context->customer->email;
        $customerThread->status = 'open';
        $customerThread->token = Tools::passwdGen(12);
        $customerThread->add();

        $customerMessage = new CustomerMessage();
        $customerMessage->id_customer_thread = $customerThread->id;
        $customerMessage->message = $message;
        $customerMessage->ip_address = (int) ip2long(Tools::getRemoteAddr());
        $customerMessage->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $customerMessage->private = 1;
        $customerMessage->read = false;
        $customerMessage->add();
    }
}
