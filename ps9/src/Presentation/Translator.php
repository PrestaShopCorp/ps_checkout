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

namespace PsCheckout\Module\Presentation;

if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Translation\TranslatorInterface as PrestaShopTranslator;
use PsCheckout\Presentation\TranslatorInterface;

class Translator implements TranslatorInterface
{
    /**
     * @var PrestaShopTranslator
     */
    private $translator;

    public function __construct(PrestaShopTranslator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function trans(string $key, array $parameters = []): string
    {
        switch ($key) {
            case 'Total':
                return $this->translator->trans('Total', $parameters, 'Modules.Checkout.Pscheckout');
            case 'If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.':
                return $this->translator->trans('If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.', $parameters, 'Modules.Checkout.Pscheckout.support');
            case 'Checkout':
                return $this->translator->trans('Checkout', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Go back to the Checkout':
                return $this->translator->trans('Go back to the Checkout', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Card payment':
                return $this->translator->trans('Card payment', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Order summary':
                return $this->translator->trans('Order summary', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your shopping cart is empty.':
                return $this->translator->trans('Your shopping cart is empty.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal':
                return $this->translator->trans('PayPal', $parameters, 'Modules.Checkout.Pscheckout');
            case 'You have chosen to pay by Card.':
                return $this->translator->trans('You have chosen to pay by Card.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'You have chosen to pay by PayPal.':
                return $this->translator->trans('You have chosen to pay by PayPal.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Here is a short summary of your order:':
                return $this->translator->trans('Here is a short summary of your order:', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The total amount of your order comes to':
                return $this->translator->trans('The total amount of your order comes to', $parameters, 'Modules.Checkout.Pscheckout');
            case '(tax incl.)':
                return $this->translator->trans('(tax incl.)', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Please confirm your order by clicking "I confirm my order".':
                return $this->translator->trans('Please confirm your order by clicking "I confirm my order".', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Delete this payment method?':
                return $this->translator->trans('Delete this payment method?', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The following payment method will be deleted from your account:':
                return $this->translator->trans('The following payment method will be deleted from your account:', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Delete payment method':
                return $this->translator->trans('Delete payment method', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Please wait, we are processing your request':
                return $this->translator->trans('Please wait, we are processing your request', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Other payment methods':
                return $this->translator->trans('Other payment methods', $parameters, 'Modules.Checkout.Pscheckout');
            case 'I confirm my order':
                return $this->translator->trans('I confirm my order', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Thanks for your purchase!':
                return $this->translator->trans('Thanks for your purchase!', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Please wait, we are processing your payment':
                return $this->translator->trans('Please wait, we are processing your payment', $parameters, 'Modules.Checkout.Pscheckout');
            case 'This is taking longer than expected. Please wait...':
                return $this->translator->trans('This is taking longer than expected. Please wait...', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Ok':
                return $this->translator->trans('Ok', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Cancel':
                return $this->translator->trans('Cancel', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Secure payments':
                return $this->translator->trans('Secure payments', $parameters, 'Modules.Checkout.Pscheckout');
            case 'or':
                return $this->translator->trans('or', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Express Checkout':
                return $this->translator->trans('Express Checkout', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Please wait, loading additional payment methods.':
                return $this->translator->trans('Please wait, loading additional payment methods.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'You have selected your %s PayPal account to proceed to the payment.':
                return $this->translator->trans('You have selected your %s PayPal account to proceed to the payment.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Warning':
                return $this->translator->trans('Warning', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Card':
                return $this->translator->trans('Card', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Pay by Card - Secure payments':
                return $this->translator->trans('Pay by Card - Secure payments', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Pay with a PayPal account':
                return $this->translator->trans('Pay with a PayPal account', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Pay in installments with PayPal Pay Later':
                return $this->translator->trans('Pay in installments with PayPal Pay Later', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Pay by %s':
                return $this->translator->trans('Pay by %s', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Pay with %s':
                return $this->translator->trans('Pay with %s', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Pay later with invoice':
                return $this->translator->trans('Pay later with invoice', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Pay upon Invoice':
                return $this->translator->trans('Pay upon Invoice', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Contact customer service via %s':
                return $this->translator->trans('Contact customer service via %s', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Card holder name':
                return $this->translator->trans('Card holder name', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Card number':
                return $this->translator->trans('Card number', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Expiry date':
                return $this->translator->trans('Expiry date', $parameters, 'Modules.Checkout.Pscheckout');
            case 'MM/YY':
                return $this->translator->trans('MM/YY', $parameters, 'Modules.Checkout.Pscheckout');
            case 'CVC':
                return $this->translator->trans('CVC', $parameters, 'Modules.Checkout.Pscheckout');
            case 'XXX':
                return $this->translator->trans('XXX', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Phone Number':
                return $this->translator->trans('Phone Number', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Date of Birth':
                return $this->translator->trans('Date of Birth', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Not required':
                return $this->translator->trans('Not required', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Created':
                return $this->translator->trans('Created', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Saved':
                return $this->translator->trans('Saved', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Approved':
                return $this->translator->trans('Approved', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Voided':
                return $this->translator->trans('Voided', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Completed':
                return $this->translator->trans('Completed', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Declined':
                return $this->translator->trans('Declined', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Pending':
                return $this->translator->trans('Pending', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Partially refunded':
                return $this->translator->trans('Partially refunded', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Refunded':
                return $this->translator->trans('Refunded', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Failed':
                return $this->translator->trans('Failed', $parameters, 'Modules.Checkout.Pscheckout');
            case 'There was an error during the payment. Please try again or contact the support.':
                return $this->translator->trans('There was an error during the payment. Please try again or contact the support.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'No PayPal Javascript SDK Instance':
                return $this->translator->trans('No PayPal Javascript SDK Instance', $parameters, 'Modules.Checkout.Pscheckout');
            case 'No Google Pay Javascript SDK Instance':
                return $this->translator->trans('No Google Pay Javascript SDK Instance', $parameters, 'Modules.Checkout.Pscheckout');
            case 'No Apple Pay Javascript SDK Instance':
                return $this->translator->trans('No Apple Pay Javascript SDK Instance', $parameters, 'Modules.Checkout.Pscheckout');
            case 'An error occurred fetching Google Pay transaction info':
                return $this->translator->trans('An error occurred fetching Google Pay transaction info', $parameters, 'Modules.Checkout.Pscheckout');
            case 'An error occurred fetching Apple Pay payment request':
                return $this->translator->trans('An error occurred fetching Apple Pay payment request', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Card holder authentication canceled, please choose another payment method or try again.':
                return $this->translator->trans('Card holder authentication canceled, please choose another payment method or try again.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'An error occurred on card holder authentication, please choose another payment method or try again.':
                return $this->translator->trans('An error occurred on card holder authentication, please choose another payment method or try again.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Card holder authentication failed, please choose another payment method or try again.':
                return $this->translator->trans('Card holder authentication failed, please choose another payment method or try again.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Card holder authentication cannot be checked, please choose another payment method or try again.':
                return $this->translator->trans('Card holder authentication cannot be checked, please choose another payment method or try again.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'We are unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.':
                return $this->translator->trans('We are unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.':
                return $this->translator->trans('We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.':
                return $this->translator->trans('Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The transaction failed. Please try a different card.':
                return $this->translator->trans('The transaction failed. Please try a different card.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The transaction was refused.':
                return $this->translator->trans('The transaction was refused.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'This payment method is unavailable':
                return $this->translator->trans('This payment method is unavailable', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Unable to call API':
                return $this->translator->trans('Unable to call API', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal order identifier is missing':
                return $this->translator->trans('PayPal order identifier is missing', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal payment method is missing':
                return $this->translator->trans('PayPal payment method is missing', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Cart is invalid':
                return $this->translator->trans('Cart is invalid', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Order cannot be saved':
                return $this->translator->trans('Order cannot be saved', $parameters, 'Modules.Checkout.Pscheckout');
            case 'OrderState cannot be saved':
                return $this->translator->trans('OrderState cannot be saved', $parameters, 'Modules.Checkout.Pscheckout');
            case 'OrderPayment cannot be saved':
                return $this->translator->trans('OrderPayment cannot be saved', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The transaction amount doesn\'t match with the cart amount.':
                return $this->translator->trans('The transaction amount doesn\'t match with the cart amount.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Cart doesn\'t contains product.':
                return $this->translator->trans('Cart doesn\'t contains product.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Cart contains product unavailable.':
                return $this->translator->trans('Cart contains product unavailable.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Cart invoice address is invalid.':
                return $this->translator->trans('Cart invoice address is invalid.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Cart delivery address is invalid.':
                return $this->translator->trans('Cart delivery address is invalid.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Cart delivery option is unavailable.':
                return $this->translator->trans('Cart delivery option is unavailable.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Processing of this card type is not supported. Use another card type.':
                return $this->translator->trans('Processing of this card type is not supported. Use another card type.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The CVC code length is invalid for the specified card type.':
                return $this->translator->trans('The CVC code length is invalid for the specified card type.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your card cannot be used to pay in this currency, please try another payment method.':
                return $this->translator->trans('Your card cannot be used to pay in this currency, please try another payment method.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your country is not supported by this payment method, please try to select another.':
                return $this->translator->trans('Your country is not supported by this payment method, please try to select another.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The transaction failed. Please try a different payment method.':
                return $this->translator->trans('The transaction failed. Please try a different payment method.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Transaction expired, please try again.':
                return $this->translator->trans('Transaction expired, please try again.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Order is already captured.':
                return $this->translator->trans('Order is already captured.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The payment is not valid: the amount is not eligible.':
                return $this->translator->trans('The payment is not valid: the amount is not eligible.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'This payment method has been refused by the payment platform, please use another payment method.':
                return $this->translator->trans('This payment method has been refused by the payment platform, please use another payment method.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'This message is sent automatically by module PrestaShop Checkout':
                return $this->translator->trans('This message is sent automatically by module PrestaShop Checkout', $parameters, 'Modules.Checkout.Pscheckout');
            case 'A customer encountered a processing payment error :':
                return $this->translator->trans('A customer encountered a processing payment error :', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Customer identifier:':
                return $this->translator->trans('Customer identifier:', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Cart identifier:':
                return $this->translator->trans('Cart identifier:', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal order identifier:':
                return $this->translator->trans('PayPal order identifier:', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Exception identifier:':
                return $this->translator->trans('Exception identifier:', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Exception detail:':
                return $this->translator->trans('Exception detail:', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Payment gateway information':
                return $this->translator->trans('Payment gateway information', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Order identifier':
                return $this->translator->trans('Order identifier', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Order status':
                return $this->translator->trans('Order status', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Transaction identifier':
                return $this->translator->trans('Transaction identifier', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Transaction status':
                return $this->translator->trans('Transaction status', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Funding source':
                return $this->translator->trans('Funding source', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Amount paid':
                return $this->translator->trans('Amount paid', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Approve payment':
                return $this->translator->trans('Approve payment', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Authenticate payment':
                return $this->translator->trans('Authenticate payment', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your payment has been declined by our payment gateway, please contact us via the link below.':
                return $this->translator->trans('Your payment has been declined by our payment gateway, please contact us via the link below.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your payment needs to be approved, please click the button below.':
                return $this->translator->trans('Your payment needs to be approved, please click the button below.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your payment needs to be authenticated, please click the button below.':
                return $this->translator->trans('Your payment needs to be authenticated, please click the button below.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'You will be redirected to an external secured page of our payment gateway.':
                return $this->translator->trans('You will be redirected to an external secured page of our payment gateway.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'If you have any question, please contact us.':
                return $this->translator->trans('If you have any question, please contact us.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Payment method status':
                return $this->translator->trans('Payment method status', $parameters, 'Modules.Checkout.Pscheckout');
            case 'was saved for future purchases':
                return $this->translator->trans('was saved for future purchases', $parameters, 'Modules.Checkout.Pscheckout');
            case 'was not saved for future purchases':
                return $this->translator->trans('was not saved for future purchases', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Payment':
                return $this->translator->trans('Payment', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Refund':
                return $this->translator->trans('Refund', $parameters, 'Modules.Checkout.Pscheckout');
            case 'You are not authorized to refund this order.':
                return $this->translator->trans('You are not authorized to refund this order.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal Order is invalid.':
                return $this->translator->trans('PayPal Order is invalid.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal Transaction is invalid.':
                return $this->translator->trans('PayPal Transaction is invalid.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal refund currency is invalid.':
                return $this->translator->trans('PayPal refund currency is invalid.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal refund amount is invalid.':
                return $this->translator->trans('PayPal refund amount is invalid.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal refund failed.':
                return $this->translator->trans('PayPal refund failed.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Refund has been processed by PayPal, but order status change or email sending failed.':
                return $this->translator->trans('Refund has been processed by PayPal, but order status change or email sending failed.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Refund cannot be processed by PayPal.':
                return $this->translator->trans('Refund cannot be processed by PayPal.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Refund has been processed by PayPal.':
                return $this->translator->trans('Refund has been processed by PayPal.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'No PrestaShop Order identifier received':
                return $this->translator->trans('No PrestaShop Order identifier received', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Unable to find PayPal Order associated to this PrestaShop Order %s':
                return $this->translator->trans('Unable to find PayPal Order associated to this PrestaShop Order %s', $parameters, 'Modules.Checkout.Pscheckout');
            case 'PayPal Order %s is not in the same environment as PrestaShop Checkout':
                return $this->translator->trans('PayPal Order %s is not in the same environment as PrestaShop Checkout', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Authorization captured successfully.':
                return $this->translator->trans('Authorization captured successfully.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Authorization voided successfully.':
                return $this->translator->trans('Authorization voided successfully.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Authorization reauthorized successfully.':
                return $this->translator->trans('Authorization reauthorized successfully.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Eligible':
                return $this->translator->trans('Eligible', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Partially eligible':
                return $this->translator->trans('Partially eligible', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Not eligible':
                return $this->translator->trans('Not eligible', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.':
                return $this->translator->trans('Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your PayPal balance remains intact if the customer claims that they did not receive an item.':
                return $this->translator->trans('Your PayPal balance remains intact if the customer claims that they did not receive an item.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.':
                return $this->translator->trans('Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'Dispute categories covered:':
                return $this->translator->trans('Dispute categories covered:', $parameters, 'Modules.Checkout.Pscheckout');
            case 'For more information, please go to the official PayPal website.':
                return $this->translator->trans('For more information, please go to the official PayPal website.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The payer paid for an item that they did not receive.':
                return $this->translator->trans('The payer paid for an item that they did not receive.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The payer did not authorize the payment.':
                return $this->translator->trans('The payer did not authorize the payment.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The authorization has been successfully captured.':
                return $this->translator->trans('The authorization has been successfully captured.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'An error occurred during the capture of the authorization.':
                return $this->translator->trans('An error occurred during the capture of the authorization.', $parameters, 'Modules.Checkout.Pscheckout');
            case 'The currency you selected is not supported. Please try another payment method or contact support for assistance.':
                return $this->translator->trans('The currency you selected is not supported. Please try another payment method or contact support for assistance.', $parameters, 'Modules.Checkout.Pscheckout');
            default:
                return $key;
        }
    }
}
