<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\ValidateOrder;

/**
 * This controller receive ajax call to capture/authorize payment and create a PrestaShop Order
 */
class Ps_CheckoutValidateModuleFrontController extends AbstractFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @var string
     */
    private $paypalOrderId;

    /**
     * @see FrontController::postProcess()
     *
     * @todo Move logic to a Service
     */
    public function postProcess()
    {
        try {
            if (false === $this->checkIfContextIsValid()) {
                throw new PsCheckoutException('The context is not valid', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            if (false === $this->checkIfPaymentOptionIsAvailable()) {
                throw new PsCheckoutException('This payment method is not available.', PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE);
            }

            $cart = $this->context->cart;

            $customer = new Customer($cart->id_customer);

            if (false === Validate::isLoadedObject($customer)) {
                throw new PsCheckoutException('Unable to load Customer', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            $bodyContent = file_get_contents('php://input');

            if (empty($bodyContent)) {
                throw new PsCheckoutException('Body cannot be empty', PsCheckoutException::PSCHECKOUT_VALIDATE_BODY_EMPTY);
            }

            $bodyValues = json_decode($bodyContent, true);

            if (empty($bodyValues)) {
                throw new PsCheckoutException('Body cannot be empty', PsCheckoutException::PSCHECKOUT_VALIDATE_BODY_EMPTY);
            }

            if (empty($bodyValues['orderID']) || false === Validate::isGenericName($bodyValues['orderID'])) {
                throw new PsCheckoutException('PayPal Order identifier invalid', PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING);
            }

            $this->paypalOrderId = $bodyValues['orderID'];

            /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->module->getService('ps_checkout.repository.pscheckoutcart');

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartRepository->findOneByCartId((int) $this->context->cart->id);

            if (false !== $psCheckoutCart) {
                $psCheckoutCart->paypal_funding = $bodyValues['fundingSource'];
                $psCheckoutCart->isExpressCheckout = isset($bodyValues['isExpressCheckout']) ? (bool) $bodyValues['isExpressCheckout'] : false;
                $psCheckoutCart->isHostedFields = isset($bodyValues['isHostedFields']) ? (bool) $bodyValues['isHostedFields'] : false;
                $psCheckoutCartRepository->save($psCheckoutCart);
            }

            $this->module->getLogger()->info(
                'ValidateOrder',
                [
                    'paypal_order' => $this->paypalOrderId,
                    'id_cart' => (int) $this->context->cart->id,
                ]
            );

            $currency = $this->context->currency;
            $total = (float) $cart->getOrderTotal(true, Cart::BOTH);

            /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $accountRepository */
            $accountRepository = $this->module->getService('ps_checkout.repository.paypal.account');
            $merchandId = $accountRepository->getMerchantId();
            $payment = new ValidateOrder($bodyValues['orderID'], $merchandId);

            $dataOrder = [
                'cartId' => (int) $cart->id,
                'amount' => $total,
                'currencyId' => (int) $currency->id,
                'secureKey' => $customer->secure_key,
            ];

            // If the payment is rejected redirect the client to the last checkout step (422 error)
            // API call here
            $response = $payment->validateOrder($dataOrder);

            $this->context->cookie->__unset('paypalEmail');

            $this->sendOkResponse($response);
        } catch (Exception $exception) {
            $this->handleException($exception);
        }
    }

    /**
     * Redirect to checkout page
     *
     * @param string $exceptionMessageForCustomer
     * @param Exception $exception
     */
    private function sendBadRequestError($exceptionMessageForCustomer, Exception $exception)
    {
        $this->exitWithResponse([
            'status' => false,
            'httpCode' => 400,
            'body' => [
                'error' => [
                    'code' => $exception->getCode(),
                    'message' => $exceptionMessageForCustomer,
                ],
            ],
            'exceptionCode' => $exception->getCode(),
            'exceptionMessage' => $exception->getMessage(),
        ]);
    }

    /**
     * Redirect to order confirmation page
     *
     * @param array $response
     */
    private function sendOkResponse($response)
    {
        $this->exitWithResponse([
            'status' => true,
            'httpCode' => 200,
            'body' => [
                'paypal_status' => $response['status'],
                'paypal_order' => $response['paypalOrderId'],
                'paypal_transaction' => $response['transactionIdentifier'],
                'id_cart' => (int) $this->context->cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => (int) $this->module->currentOrder,
                'secure_key' => $this->context->customer->secure_key,
            ],
            'exceptionCode' => null,
            'exceptionMessage' => null,
        ]);
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
     *
     * @todo Move to main module class
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
        $exceptionMessageForCustomer = $this->module->l('Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.');
        $notifyCustomerService = true;
        $paypalOrder = $this->paypalOrderId;
        $exceptionClass = get_class($exception);

        if (false === Validate::isGenericName($paypalOrder)) {
            $paypalOrder = 'invalid';
        }

        if ('PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException' === $exceptionClass) {
            switch ($exception->getCode()) {
                case PsCheckoutException::PAYPAL_PAYMENT_CARD_ERROR:
                    $exceptionMessageForCustomer = $this->module->l('The transaction failed. Please try a different card.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED:
                    $exceptionMessageForCustomer = $this->module->l('The transaction was refused.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE:
                    $exceptionMessageForCustomer = $this->module->l('This payment method is unavailable');
                    break;
                case PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION:
                    $exceptionMessageForCustomer = $this->module->l('Unable to call API');
                    break;
                case PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING:
                    $exceptionMessageForCustomer = $this->module->l('PayPal order identifier is missing');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_METHOD_MISSING:
                    $exceptionMessageForCustomer = $this->module->l('PayPal payment method is missing');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::PRESTASHOP_CONTEXT_INVALID:
                    $exceptionMessageForCustomer = $this->module->l('Cart is invalid');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::PRESTASHOP_VALIDATE_ORDER:
                    $exceptionMessageForCustomer = $this->module->l('Order cannot be saved');
                    break;
                case PsCheckoutException::PRESTASHOP_ORDER_STATE_ERROR:
                    $exceptionMessageForCustomer = $this->module->l('OrderState cannot be saved');
                    break;
                case PsCheckoutException::PRESTASHOP_ORDER_PAYMENT:
                    $exceptionMessageForCustomer = $this->module->l('OrderPayment cannot be saved');
                    break;
                case PsCheckoutException::DIFFERENCE_BETWEEN_TRANSACTION_AND_CART:
                    $exceptionMessageForCustomer = $this->module->l('The transaction amount doesn\'t match with the cart amount.');
                    $notifyCustomerService = false;
                    break;
            }
        } elseif ('PrestaShop\Module\PrestashopCheckout\Exception\PayPalException' === $exceptionClass) {
            switch ($exception->getCode()) {
                case PayPalException::CARD_TYPE_NOT_SUPPORTED:
                    $exceptionMessageForCustomer = $this->module->l('Processing of this card type is not supported. Use another card type.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::INVALID_SECURITY_CODE_LENGTH:
                    $exceptionMessageForCustomer = $this->module->l('The CVC code length is invalid for the specified card type.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::CURRENCY_NOT_SUPPORTED_FOR_CARD_TYPE:
                    $exceptionMessageForCustomer = $this->module->l('Your card cannot be used to pay in this currency, please try another payment method.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::CURRENCY_NOT_SUPPORTED_FOR_COUNTRY:
                    $exceptionMessageForCustomer = $this->module->l('Your card cannot be used to pay in our country, please try another payment method.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::INSTRUMENT_DECLINED:
                    $exceptionMessageForCustomer = $this->module->l('This payment method declined transaction, please try another.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED:
                    $exceptionMessageForCustomer = $this->module->l('You have exceeded the maximum number of payment attempts.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::PAYER_ACCOUNT_LOCKED_OR_CLOSED:
                    $exceptionMessageForCustomer = $this->module->l('Your PayPal account is locked or closed, please try another.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::PAYER_ACCOUNT_RESTRICTED:
                    $exceptionMessageForCustomer = $this->module->l('You are not allowed to pay with this PayPal account, please try another.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::PAYER_CANNOT_PAY:
                    $exceptionMessageForCustomer = $this->module->l('You are not allowed to pay with this payment method, please try another.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::PAYER_COUNTRY_NOT_SUPPORTED:
                    $exceptionMessageForCustomer = $this->module->l('Your country is not supported by this payment method, please try to select another.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::REDIRECT_PAYER_FOR_ALTERNATE_FUNDING:
                    $exceptionMessageForCustomer = $this->module->l('The transaction failed. Please try a different payment method.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::TRANSACTION_BLOCKED_BY_PAYEE:
                    $exceptionMessageForCustomer = $this->module->l('The transaction was blocked by Fraud Protection settings.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::TRANSACTION_REFUSED:
                    $exceptionMessageForCustomer = $this->module->l('The transaction was refused.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::NO_EXTERNAL_FUNDING_DETAILS_FOUND:
                    $exceptionMessageForCustomer = $this->module->l('This payment method seems not working currently, please try another.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::ORDER_ALREADY_CAPTURED:
                    $exceptionMessageForCustomer = $this->module->l('Order cannot be saved');
                    break;
            }
        }

        if (true === $notifyCustomerService) {
            $this->notifyCustomerService($exception);

            $this->module->getLogger()->error(
                'ValidateOrder - Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                    'paypal_order' => $paypalOrder,
                ]
            );

            $this->sentryExceptionHandler->handle($exception, false);
        } else {
            $this->module->getLogger()->notice(
                'ValidateOrder - Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                    'paypal_order' => $paypalOrder,
                ]
            );

            $this->sendBadRequestError($exceptionMessageForCustomer, $exception);
        }

        $psCheckoutCartCollection = new PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('paypal_order', '=', (int) $paypalOrder);

        /** @var PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        // Preserve current cart from customer changes to allow merchant to see whats wrong
        if (false !== $psCheckoutCart && false === (bool) Order::getOrderByCartId($psCheckoutCart->id_cart)) {
            $this->generateNewCart();
        }

        $this->exitWithResponse([
            'status' => false,
            'httpCode' => 500,
            'body' => [
                'error' => [
                    'message' => $exceptionMessageForCustomer,
                ],
            ],
            'exceptionCode' => $exception->getCode(),
            'exceptionMessage' => $exception->getMessage(),
        ]);
    }

    /**
     * @param Exception $exception
     *
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     *
     * @todo To be refactored with Service Container in v2.0.0
     */
    private function notifyCustomerService(Exception $exception)
    {
        $paypalOrderId = $this->paypalOrderId;
        $contacts = Contact::getContacts((int) $this->context->language->id);

        if (empty($contacts)) {
            return;
        }

        // Cannot use id_cart because we create a new cart to preserve current cart from customer changes
        $token = Tools::substr(
            Tools::encrypt(implode(
                '|',
                [
                    (int) $this->context->customer->id,
                    (int) $this->context->shop->id,
                    (int) $this->context->language->id,
                    (int) $exception->getCode(),
                    $paypalOrderId,
                    get_class($exception),
                ]
            )),
            0,
            12
        );

        $isThreadAlreadyCreated = (bool) Db::getInstance()->getValue('
            SELECT 1
            FROM ' . _DB_PREFIX_ . 'customer_thread
            WHERE id_customer = ' . (int) $this->context->customer->id . '
            AND id_shop = ' . (int) $this->context->shop->id . '
            AND status = "open"
            AND token = "' . pSQL($token) . '"
        ');

        // Prevent spam Customer Service on case of page refresh
        if (true === $isThreadAlreadyCreated) {
            return;
        }

        $message = $this->module->l('This message is sent automatically by module PrestaShop Checkout') . PHP_EOL . PHP_EOL;
        $message .= $this->module->l('A customer encountered a processing payment error :') . PHP_EOL;
        $message .= $this->module->l('Customer identifier:') . ' ' . (int) $this->context->customer->id . PHP_EOL;
        $message .= $this->module->l('Cart identifier:') . ' ' . (int) $this->context->cart->id . PHP_EOL;
        $message .= $this->module->l('PayPal order identifier:') . ' ' . Tools::safeOutput($paypalOrderId) . PHP_EOL;
        $message .= $this->module->l('Exception identifier:') . ' ' . (int) $exception->getCode() . PHP_EOL;
        $message .= $this->module->l('Exception detail:') . ' ' . Tools::safeOutput($exception->getMessage())
            . ($exception->getPrevious() !== null ? ': ' . Tools::safeOutput($exception->getPrevious()->getMessage()) : '')
            . PHP_EOL . PHP_EOL;
        $message .= $this->module->l('If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.') . PHP_EOL;

        $customerThread = new CustomerThread();
        $customerThread->id_customer = (int) $this->context->customer->id;
        $customerThread->id_shop = (int) $this->context->shop->id;
        $customerThread->id_order = (int) $this->module->currentOrder;
        $customerThread->id_lang = (int) $this->context->language->id;
        $customerThread->id_contact = (int) $contacts[0]['id_contact']; // Should be configurable
        $customerThread->email = $this->context->customer->email;
        $customerThread->status = 'open';
        $customerThread->token = $token;
        $customerThread->add();

        $customerMessage = new CustomerMessage();
        $customerMessage->id_customer_thread = $customerThread->id;
        $customerMessage->message = $message;
        $customerMessage->ip_address = (int) ip2long(Tools::getRemoteAddr());
        $customerMessage->user_agent = $_SERVER['HTTP_USER_AGENT'];
        $customerMessage->private = true;
        $customerMessage->read = false;
        $customerMessage->add();
    }

    /**
     * @see FrontController::init()
     */
    private function generateNewCart()
    {
        $cart = clone $this->context->cart;
        $cart->id = 0;
        $cart->add();

        $this->context->cart = $cart;
    }
}
