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

use PrestaShop\Module\PrestashopCheckout\Checkout\Event\CheckoutCompletedEvent;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Controller\AbstractFrontController;
use PrestaShop\Module\PrestashopCheckout\Event\EventDispatcherInterface;
use PrestaShop\Module\PrestashopCheckout\Event\SymfonyEventDispatcherAdapter;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForOrderConfirmationQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Query\GetPayPalOrderForOrderConfirmationQueryResult;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;

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
     */
    public function postProcess()
    {
        try {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $bodyContent = file_get_contents('php://input');

                if (empty($bodyContent)) {
                    $this->exitWithResponse([
                        'httpCode' => 400,
                        'body' => 'Payload invalid',
                    ]);
                }

                $bodyValues = json_decode($bodyContent, true);

                if (empty($bodyValues)) {
                    $this->exitWithResponse([
                        'httpCode' => 400,
                        'body' => 'Payload invalid',
                    ]);
                }
            } else {
                $bodyValues = [
                    'orderID' => Tools::getValue('token'),
                    'payerID' => Tools::getValue('PayerID'),
                ];
            }

            if (empty($bodyValues['orderID']) || false === Validate::isGenericName($bodyValues['orderID'])) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'Missing PayPal Order Id',
                ]);
            }

            $this->paypalOrderId = $bodyValues['orderID'];

            /** @var PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->module->getService(PsCheckoutCartRepository::class);
            $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($this->paypalOrderId);

            if (!Validate::isLoadedObject($psCheckoutCart)) {
                $this->exitWithResponse([
                    'httpCode' => 400,
                    'body' => 'No cart found.',
                ]);
            }

            /** @var EventDispatcherInterface $eventDispatcher */
            $eventDispatcher = $this->module->getService(SymfonyEventDispatcherAdapter::class);

            $eventDispatcher->dispatch(new CheckoutCompletedEvent(
                $psCheckoutCart->getIdCart(),
                $this->paypalOrderId,
                (isset($bodyValues['fundingSource']) && Validate::isGenericName($bodyValues['fundingSource'])) ? $bodyValues['fundingSource'] : $psCheckoutCart->getPaypalFundingSource(),
                isset($bodyValues['isExpressCheckout']) && $bodyValues['isExpressCheckout'] || $psCheckoutCart->isExpressCheckout(),
                isset($bodyValues['isHostedFields']) && $bodyValues['isHostedFields'] || $psCheckoutCart->isHostedFields()
            ));

            $this->sendOkResponse($this->generateResponse());
        } catch (Exception $exception) {
            $response = $this->generateResponse();

            if (!empty($response)) {
                $this->sendOkResponse($response);
            }

            $this->handleException($exception);
        }
    }

    private function generateResponse()
    {
        if (empty($this->paypalOrderId)) {
            return null;
        }

        try {
            /** @var CommandBusInterface $commandBus */
            $commandBus = $this->module->getService('ps_checkout.bus.command');

            /** @var PsCheckoutCartRepository $psCheckoutCartRepository */
            $psCheckoutCartRepository = $this->module->getService(PsCheckoutCartRepository::class);
            $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($this->paypalOrderId);

            if (!Validate::isLoadedObject($psCheckoutCart)) {
                return null;
            }

            $paypalOrder = null;

            try {
                /** @var GetPayPalOrderForOrderConfirmationQueryResult $paypalOrder */
                $paypalOrder = $commandBus->handle(new GetPayPalOrderForOrderConfirmationQuery(
                    $psCheckoutCart->paypal_order
                ));
            } catch (Exception $exception) {
            }

            $response = [
                'status' => $psCheckoutCart->paypal_status,
                'paypalOrderId' => $psCheckoutCart->paypal_order,
                'transactionIdentifier' => $paypalOrder && isset($paypalOrder->getOrderPayPal()['purchase_units'][0]['payments']['captures'][0]) ? $paypalOrder->getOrderPayPal()['purchase_units'][0]['payments']['captures'][0]['id'] : null,
            ];

            return $response;
        } catch (Exception $exception) {
            $this->handleException($exception);
        }

        return null;
    }

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
        if (empty($this->paypalOrderId)) {
            return null;
        }

        /** @var PsCheckoutCartRepository $psCheckoutCartRepository */
        $psCheckoutCartRepository = $this->module->getService(PsCheckoutCartRepository::class);
        $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($this->paypalOrderId);

        if (!Validate::isLoadedObject($psCheckoutCart)) {
            return null;
        }

        $orders = new PrestaShopCollection(Order::class);
        $orders->where('id_cart', '=', $psCheckoutCart->getIdCart());

        if (!$orders->count()) {
            return null;
        }

        /** @var Order $order */
        $order = $orders->getFirst();

        $cart = new Cart($psCheckoutCart->getIdCart());

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            Tools::redirect($this->context->link->getPageLink(
                'order-confirmation',
                true,
                (int) $order->id_lang,
                [
                    'paypal_status' => $response['status'],
                    'paypal_order' => $response['paypalOrderId'],
                    'paypal_transaction' => $response['transactionIdentifier'],
                    'id_cart' => $psCheckoutCart->getIdCart(),
                    'id_module' => (int) $this->module->id,
                    'id_order' => (int) $order->id,
                    'key' => $cart->secure_key,
                ]
            ));
        }

        $this->exitWithResponse([
            'status' => true,
            'httpCode' => 200,
            'body' => [
                'paypal_status' => $response['status'],
                'paypal_order' => $response['paypalOrderId'],
                'paypal_transaction' => $response['transactionIdentifier'],
                'id_cart' => $psCheckoutCart->getIdCart(),
                'id_module' => (int) $this->module->id,
                'id_order' => (int) $order->id,
                'secure_key' => $cart->secure_key,
            ],
            'exceptionCode' => null,
            'exceptionMessage' => null,
        ]);
    }

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
                case PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_FAILURE:
                    $exceptionMessageForCustomer = $this->module->l('Card holder authentication failed, please choose another payment method or try again.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN:
                    $exceptionMessageForCustomer = $this->module->l('Card holder authentication cannot be checked, please choose another payment method or try again.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_CANCELED:
                    $exceptionMessageForCustomer = $this->module->l('Card holder authentication canceled, please choose another payment method or try again.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::CART_PRODUCT_MISSING:
                    $exceptionMessageForCustomer = $this->module->l('Cart doesn\'t contains product.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::CART_PRODUCT_UNAVAILABLE:
                    $exceptionMessageForCustomer = $this->module->l('Cart contains product unavailable.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::CART_ADDRESS_INVOICE_INVALID:
                    $exceptionMessageForCustomer = $this->module->l('Cart invoice address is invalid.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::CART_ADDRESS_DELIVERY_INVALID:
                    $exceptionMessageForCustomer = $this->module->l('Cart delivery address is invalid.');
                    $notifyCustomerService = false;
                    break;
                case PsCheckoutException::CART_DELIVERY_OPTION_INVALID:
                    $exceptionMessageForCustomer = $this->module->l('Cart delivery option is unavailable.');
                    $notifyCustomerService = false;
                    break;
            }
        } elseif ('PrestaShop\Module\PrestashopCheckout\Exception\PayPalException' === $exceptionClass) {
            switch ($exception->getCode()) {
                case PayPalException::CARD_BRAND_NOT_SUPPORTED:
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
                case PayPalException::COMPLIANCE_VIOLATION:
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
                    $exceptionMessageForCustomer = $this->module->l('Order is already captured.');
                    break;
                case PayPalException::PAYMENT_DENIED:
                    $exceptionMessageForCustomer = $this->module->l('This payment method has been refused by the payment platform, please use another payment method.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::NOT_ENABLED_FOR_CARD_PROCESSING:
                case PayPalException::PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING:
                    $exceptionMessageForCustomer = $this->module->l('Card payment cannot be processed at the moment, please use another payment method.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::RESOURCE_NOT_FOUND:
                    $exceptionMessageForCustomer = $this->module->l('Transaction expired, please try again.');
                    $notifyCustomerService = false;
                    break;
                case PayPalException::PAYMENT_SOURCE_CANNOT_BE_USED:
                    $exceptionMessageForCustomer = $this->module->l('The selected payment method does not support this type of transaction. Please choose another payment method or contact support for assistance.');
                    $notifyCustomerService = false;
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

        $this->exitWithResponse([
            'status' => false,
            'httpCode' => 500,
            'body' => [
                'error' => [
                    'message' => $exceptionMessageForCustomer,
                    'code' => (int) $exception->getCode() < 400 && $exception->getPrevious() !== null
                        ? (int) $exception->getPrevious()->getCode()
                        : (int) $exception->getCode(),
                ],
            ],
            'exceptionCode' => $exception->getCode(),
            'exceptionMessage' => $exception->getMessage(),
        ]);
    }

    private function notifyCustomerService(Exception $exception)
    {
        /** @var PsCheckoutCartRepository $psCheckoutCartRepository */
        $psCheckoutCartRepository = $this->module->getService(PsCheckoutCartRepository::class);
        $psCheckoutCart = $psCheckoutCartRepository->findOneByPayPalOrderId($this->paypalOrderId);

        if (!Validate::isLoadedObject($psCheckoutCart)) {
            return null;
        }

        $cart = new Cart($psCheckoutCart->getIdCart());

        if (!Validate::isLoadedObject($cart)) {
            return null;
        }

        $contacts = Contact::getContacts((int) $cart->id_lang);

        if (empty($contacts)) {
            return;
        }

        // Cannot use id_cart because we create a new cart to preserve current cart from customer changes
        $token = Tools::substr(
            Tools::encrypt(implode(
                '|',
                [
                    (int) $cart->id_customer,
                    (int) $cart->id_shop,
                    (int) $cart->id_lang,
                    (int) $exception->getCode(),
                    $this->paypalOrderId,
                    get_class($exception),
                ]
            )),
            0,
            12
        );

        $isThreadAlreadyCreated = (bool) Db::getInstance()->getValue('
            SELECT 1
            FROM ' . _DB_PREFIX_ . 'customer_thread
            WHERE id_customer = ' . (int) $cart->id_customer . '
            AND id_shop = ' . (int) $cart->id_shop . '
            AND status = "open"
            AND token = "' . pSQL($token) . '"
        ');

        // Prevent spam Customer Service on case of page refresh
        if (true === $isThreadAlreadyCreated) {
            return;
        }

        $message = $this->module->l('This message is sent automatically by module PrestaShop Checkout') . PHP_EOL . PHP_EOL;
        $message .= $this->module->l('A customer encountered a processing payment error :') . PHP_EOL;
        $message .= $this->module->l('Customer identifier:') . ' ' . (int) $cart->id_customer . PHP_EOL;
        $message .= $this->module->l('Cart identifier:') . ' ' . (int) $cart->id . PHP_EOL;
        $message .= $this->module->l('PayPal order identifier:') . ' ' . Tools::safeOutput($this->paypalOrderId) . PHP_EOL;
        $message .= $this->module->l('Exception identifier:') . ' ' . (int) $exception->getCode() . PHP_EOL;
        $message .= $this->module->l('Exception detail:') . ' ' . Tools::safeOutput($exception->getMessage())
            . ($exception->getPrevious() !== null ? ': ' . Tools::safeOutput($exception->getPrevious()->getMessage()) : '')
            . PHP_EOL . PHP_EOL;
        $message .= $this->module->l('If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.') . PHP_EOL;

        $customer = new Customer((int) $cart->id_customer);

        $customerThread = new CustomerThread();
        $customerThread->id_customer = (int) $cart->id_customer;
        $customerThread->id_shop = (int) $cart->id_shop;
        $customerThread->id_order = (int) $this->module->currentOrder;
        $customerThread->id_lang = (int) $cart->id_lang;
        $customerThread->id_contact = (int) $contacts[0]['id_contact']; // Should be configurable
        $customerThread->email = $customer->email;
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
}
