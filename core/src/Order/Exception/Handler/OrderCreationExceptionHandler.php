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

namespace PsCheckout\Core\Order\Exception\Handler;

use PsCheckout\Api\Http\Exception\PayPalException;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Action\CustomerNotifyActionInterface;
use PsCheckout\Presentation\TranslatorInterface;
use Psr\Log\LoggerInterface;

class OrderCreationExceptionHandler implements OrderCreationExceptionHandlerInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomerNotifyActionInterface
     */
    private $customerNotifyAction;

    public function __construct(
        TranslatorInterface $translator,
        LoggerInterface $logger,
        CustomerNotifyActionInterface $customerNotifyAction
    ) {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->customerNotifyAction = $customerNotifyAction;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(\Exception $exception, string $paypalOrderId)
    {
        $exceptionMessageForCustomer = $this->translator->trans('Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.');
        $notifyCustomerService = true;
        $exceptionClass = get_class($exception);
        $httpCode = 500;

        if (PsCheckoutException::class === $exceptionClass) {
            switch ($exception->getCode()) {
                case PsCheckoutException::PAYPAL_PAYMENT_CARD_ERROR:
                    $exceptionMessageForCustomer = $this->translator->trans('The transaction failed. Please try a different card.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_CAPTURE_DECLINED:
                    $exceptionMessageForCustomer = $this->translator->trans('The transaction was refused.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE:
                    $exceptionMessageForCustomer = $this->translator->trans('This payment method is unavailable');

                    break;
                case PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION:
                    $exceptionMessageForCustomer = $this->translator->trans('Unable to call API');

                    break;
                case PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING:
                    $exceptionMessageForCustomer = $this->translator->trans('PayPal order identifier is missing');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_METHOD_MISSING:
                    $exceptionMessageForCustomer = $this->translator->trans('PayPal payment method is missing');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::PRESTASHOP_CONTEXT_INVALID:
                    $exceptionMessageForCustomer = $this->translator->trans('Cart is invalid');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::PRESTASHOP_VALIDATE_ORDER:
                    $exceptionMessageForCustomer = $this->translator->trans('Order cannot be saved');

                    break;
                case PsCheckoutException::PRESTASHOP_ORDER_STATE_ERROR:
                    $exceptionMessageForCustomer = $this->translator->trans('OrderState cannot be saved');

                    break;
                case PsCheckoutException::PRESTASHOP_ORDER_PAYMENT:
                    $exceptionMessageForCustomer = $this->translator->trans('OrderPayment cannot be saved');

                    break;
                case PsCheckoutException::DIFFERENCE_BETWEEN_TRANSACTION_AND_CART:
                    $exceptionMessageForCustomer = $this->translator->trans('The transaction amount doesn\'t match with the cart amount.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_FAILURE:
                    $exceptionMessageForCustomer = $this->translator->trans('Card holder authentication failed, please choose another payment method or try again.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN:
                    $exceptionMessageForCustomer = $this->translator->trans('Card holder authentication cannot be checked, please choose another payment method or try again.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_CANCELED:
                    $exceptionMessageForCustomer = $this->translator->trans('Card holder authentication canceled, please choose another payment method or try again.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::CART_PRODUCT_MISSING:
                    $exceptionMessageForCustomer = $this->translator->trans('Cart doesn\'t contains product.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::CART_PRODUCT_UNAVAILABLE:
                    $exceptionMessageForCustomer = $this->translator->trans('Cart contains product unavailable.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::CART_ADDRESS_INVOICE_INVALID:
                    $exceptionMessageForCustomer = $this->translator->trans('Cart invoice address is invalid.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::CART_ADDRESS_DELIVERY_INVALID:
                    $exceptionMessageForCustomer = $this->translator->trans('Cart delivery address is invalid.');
                    $notifyCustomerService = false;

                    break;
                case PsCheckoutException::CART_DELIVERY_OPTION_INVALID:
                    $exceptionMessageForCustomer = $this->translator->trans('Cart delivery option is unavailable.');
                    $notifyCustomerService = false;

                    break;
            }
        } elseif (PayPalException::class === $exceptionClass) {
            switch ($exception->getCode()) {
                case PayPalException::CARD_BRAND_NOT_SUPPORTED:
                case PayPalException::CARD_TYPE_NOT_SUPPORTED:
                    $exceptionMessageForCustomer = $this->translator->trans('Processing of this card type is not supported. Use another card type.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::INVALID_SECURITY_CODE_LENGTH:
                    $exceptionMessageForCustomer = $this->translator->trans('The CVC code length is invalid for the specified card type.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::CURRENCY_NOT_SUPPORTED_FOR_CARD_TYPE:
                    $exceptionMessageForCustomer = $this->translator->trans('Your card cannot be used to pay in this currency, please try another payment method.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::CURRENCY_NOT_SUPPORTED_FOR_COUNTRY:
                    $exceptionMessageForCustomer = $this->translator->trans('Your card cannot be used to pay in our country, please try another payment method.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::COMPLIANCE_VIOLATION:
                case PayPalException::INSTRUMENT_DECLINED:
                    $exceptionMessageForCustomer = $this->translator->trans('This payment method declined transaction, please try another.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::MAX_NUMBER_OF_PAYMENT_ATTEMPTS_EXCEEDED:
                    $exceptionMessageForCustomer = $this->translator->trans('You have exceeded the maximum number of payment attempts.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::PAYER_ACCOUNT_LOCKED_OR_CLOSED:
                    $exceptionMessageForCustomer = $this->translator->trans('Your PayPal account is locked or closed, please try another.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::PAYER_ACCOUNT_RESTRICTED:
                    $exceptionMessageForCustomer = $this->translator->trans('You are not allowed to pay with this PayPal account, please try another.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::PAYER_CANNOT_PAY:
                    $exceptionMessageForCustomer = $this->translator->trans('You are not allowed to pay with this payment method, please try another.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::PAYER_COUNTRY_NOT_SUPPORTED:
                    $exceptionMessageForCustomer = $this->translator->trans('Your country is not supported by this payment method, please try to select another.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::REDIRECT_PAYER_FOR_ALTERNATE_FUNDING:
                    $exceptionMessageForCustomer = $this->translator->trans('The transaction failed. Please try a different payment method.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::TRANSACTION_BLOCKED_BY_PAYEE:
                    $exceptionMessageForCustomer = $this->translator->trans('The transaction was blocked by Fraud Protection settings.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::TRANSACTION_REFUSED:
                    $exceptionMessageForCustomer = $this->translator->trans('The transaction was refused.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::NO_EXTERNAL_FUNDING_DETAILS_FOUND:
                    $exceptionMessageForCustomer = $this->translator->trans('This payment method seems not working currently, please try another.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::ORDER_ALREADY_CAPTURED:
                    $exceptionMessageForCustomer = $this->translator->trans('Order is already captured.');

                    break;
                case PayPalException::PAYMENT_DENIED:
                    $exceptionMessageForCustomer = $this->translator->trans('This payment method has been refused by the payment platform, please use another payment method.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::NOT_ENABLED_FOR_CARD_PROCESSING:
                case PayPalException::PAYEE_NOT_ENABLED_FOR_CARD_PROCESSING:
                    $exceptionMessageForCustomer = $this->translator->trans('Card payment cannot be processed at the moment, please use another payment method.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::RESOURCE_NOT_FOUND:
                    $exceptionMessageForCustomer = $this->translator->trans('Transaction expired, please try again.');
                    $notifyCustomerService = false;

                    break;
                case PayPalException::PAYMENT_SOURCE_CANNOT_BE_USED:
                    $exceptionMessageForCustomer = $this->translator->trans('The selected payment method does not support this type of transaction. Please choose another payment method or contact support for assistance.');
                    $notifyCustomerService = false;

                    break;
            }
        }

        if ($notifyCustomerService) {
            $this->customerNotifyAction->execute($exception, $paypalOrderId);

            $this->logger->error(
                'ValidateOrder - Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                    'paypal_order' => $paypalOrderId,
                ]
            );
        } else {
            $this->logger->notice(
                'ValidateOrder - Exception ' . $exception->getCode(),
                [
                    'exception' => $exception,
                    'paypal_order' => $paypalOrderId,
                ]
            );
            $httpCode = 400; // Change HTTP code for non-critical errors
        }

        return [
            'status' => false,
            'httpCode' => $httpCode,
            'body' => [
                'error' => [
                    'message' => $exceptionMessageForCustomer,
                    'code' => (int) $exception->getCode() < 400 && $exception->getPrevious() !== null
                        ? (int) $exception->getPrevious()->getCode()
                        : (int) $exception->getCode(),
                ],
            ],
        ];
    }
}
