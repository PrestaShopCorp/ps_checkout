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

use Ps_Checkout;
use PsCheckout\Presentation\TranslatorInterface;

class Translator implements TranslatorInterface
{
    /**
     * @var Ps_Checkout
     */
    private $module;

    public function __construct(Ps_Checkout $module)
    {
        $this->module = $module;
    }

    /**
     * {@inheritdoc}
     */
    public function trans(string $key, array $parameters = []): string
    {
        switch ($key) {
            case 'Total':
                return $this->module->l('Total', 'Translator');
            case 'Checkout':
                return $this->module->l('Checkout', 'Translator');
            case 'Go back to the Checkout':
                return $this->module->l('Go back to the Checkout', 'Translator');
            case 'Card payment':
                return $this->module->l('Card payment', 'Translator');
            case 'Order summary':
                return $this->module->l('Order summary', 'Translator');
            case 'Your shopping cart is empty.':
                return $this->module->l('Your shopping cart is empty.', 'Translator');
            case 'PayPal':
                return $this->module->l('PayPal', 'Translator');
            case 'You have chosen to pay by Card.':
                return $this->module->l('You have chosen to pay by Card.', 'Translator');
            case 'You have chosen to pay by PayPal.':
                return $this->module->l('You have chosen to pay by PayPal.', 'Translator');
            case 'Here is a short summary of your order:':
                return $this->module->l('Here is a short summary of your order:', 'Translator');
            case 'The total amount of your order comes to':
                return $this->module->l('The total amount of your order comes to', 'Translator');
            case '(tax incl.)':
                return $this->module->l('(tax incl.)', 'Translator');
            case 'Please confirm your order by clicking "I confirm my order".':
                return $this->module->l('Please confirm your order by clicking "I confirm my order".', 'Translator');
            case 'Delete this payment method?':
                return $this->module->l('Delete this payment method?', 'Translator');
            case 'The following payment method will be deleted from your account:':
                return $this->module->l('The following payment method will be deleted from your account:', 'Translator');
            case 'Delete payment method':
                return $this->module->l('Delete payment method', 'Translator');
            case 'Please wait, we are processing your request':
                return $this->module->l('Please wait, we are processing your request', 'Translator');
            case 'Other payment methods':
                return $this->module->l('Other payment methods', 'Translator');
            case 'I confirm my order':
                return $this->module->l('I confirm my order', 'Translator');
            case 'Thanks for your purchase!':
                return $this->module->l('Thanks for your purchase!', 'Translator');
            case 'Please wait, we are processing your payment':
                return $this->module->l('Please wait, we are processing your payment', 'Translator');
            case 'This is taking longer than expected. Please wait...':
                return $this->module->l('This is taking longer than expected. Please wait...', 'Translator');
            case 'Ok':
                return $this->module->l('Ok', 'Translator');
            case 'Cancel':
                return $this->module->l('Cancel', 'Translator');
            case 'Secure payments':
                return $this->module->l('Secure payments', 'Translator');
            case 'or':
                return $this->module->l('or', 'Translator');
            case 'Express Checkout':
                return $this->module->l('Express Checkout', 'Translator');
            case 'Please wait, loading additional payment methods.':
                return $this->module->l('Please wait, loading additional payment methods.', 'Translator');
            case 'You have selected your %s PayPal account to proceed to the payment.':
                return $this->module->l('You have selected your %s PayPal account to proceed to the payment.', 'Translator');
            case 'Warning':
                return $this->module->l('Warning', 'Translator');
            case 'Card':
                return $this->module->l('Card', 'Translator');
            case 'Pay by Card - Secure payments':
                return $this->module->l('Pay by Card - Secure payments', 'Translator');
            case 'Pay with a PayPal account':
                return $this->module->l('Pay with a PayPal account', 'Translator');
            case 'Pay in installments with PayPal Pay Later':
                return $this->module->l('Pay in installments with PayPal Pay Later', 'Translator');
            case 'Pay by %s':
                return $this->module->l('Pay by %s', 'Translator');
            case 'Pay with %s':
                return $this->module->l('Pay with %s', 'Translator');
            case 'Pay later with invoice':
                return $this->module->l('Pay later with invoice', 'Translator');
            case 'Pay upon Invoice':
                return $this->module->l('Pay upon Invoice', 'Translator');
            case 'Contact customer service via %s':
                return $this->module->l('Contact customer service via %s', 'Translator');
            case 'Card holder name':
                return $this->module->l('Card holder name', 'Translator');
            case 'Card number':
                return $this->module->l('Card number', 'Translator');
            case 'Expiry date':
                return $this->module->l('Expiry date', 'Translator');
            case 'MM/YY':
                return $this->module->l('MM/YY', 'Translator');
            case 'CVC':
                return $this->module->l('CVC', 'Translator');
            case 'XXX':
                return $this->module->l('XXX', 'Translator');
            case 'Phone Number':
                return $this->module->l('Phone Number', 'Translator');
            case 'Date of Birth':
                return $this->module->l('Date of Birth', 'Translator');
            case 'Not required':
                return $this->module->l('Not required', 'Translator');
            case 'Created':
                return $this->module->l('Created', 'Translator');
            case 'Saved':
                return $this->module->l('Saved', 'Translator');
            case 'Approved':
                return $this->module->l('Approved', 'Translator');
            case 'Voided':
                return $this->module->l('Voided', 'Translator');
            case 'Completed':
                return $this->module->l('Completed', 'Translator');
            case 'Declined':
                return $this->module->l('Declined', 'Translator');
            case 'Pending':
                return $this->module->l('Pending', 'Translator');
            case 'Partially refunded':
                return $this->module->l('Partially refunded', 'Translator');
            case 'Refunded':
                return $this->module->l('Refunded', 'Translator');
            case 'Failed':
                return $this->module->l('Failed', 'Translator');
            case 'There was an error during the payment. Please try again or contact the support.':
                return $this->module->l('There was an error during the payment. Please try again or contact the support.', 'Translator');
            case 'No PayPal Javascript SDK Instance':
                return $this->module->l('No PayPal Javascript SDK Instance', 'Translator');
            case 'No Google Pay Javascript SDK Instance':
                return $this->module->l('No Google Pay Javascript SDK Instance', 'Translator');
            case 'No Apple Pay Javascript SDK Instance':
                return $this->module->l('No Apple Pay Javascript SDK Instance', 'Translator');
            case 'An error occurred fetching Google Pay transaction info':
                return $this->module->l('An error occurred fetching Google Pay transaction info', 'Translator');
            case 'An error occurred fetching Apple Pay payment request':
                return $this->module->l('An error occurred fetching Apple Pay payment request', 'Translator');
            case 'Card holder authentication canceled, please choose another payment method or try again.':
                return $this->module->l('Card holder authentication canceled, please choose another payment method or try again.', 'Translator');
            case 'An error occurred on card holder authentication, please choose another payment method or try again.':
                return $this->module->l('An error occurred on card holder authentication, please choose another payment method or try again.', 'Translator');
            case 'Card holder authentication failed, please choose another payment method or try again.':
                return $this->module->l('Card holder authentication failed, please choose another payment method or try again.', 'Translator');
            case 'Card holder authentication cannot be checked, please choose another payment method or try again.':
                return $this->module->l('Card holder authentication cannot be checked, please choose another payment method or try again.', 'Translator');
            case 'We are unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.':
                return $this->module->l('We are unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.', 'Translator');
            case 'We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.':
                return $this->module->l('We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.', 'Translator');
            case 'Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.':
                return $this->module->l('Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.', 'Translator');
            case 'The transaction failed. Please try a different card.':
                return $this->module->l('The transaction failed. Please try a different card.', 'Translator');
            case 'The transaction was refused.':
                return $this->module->l('The transaction was refused.', 'Translator');
            case 'This payment method is unavailable':
                return $this->module->l('This payment method is unavailable', 'Translator');
            case 'Unable to call API':
                return $this->module->l('Unable to call API', 'Translator');
            case 'PayPal order identifier is missing':
                return $this->module->l('PayPal order identifier is missing', 'Translator');
            case 'PayPal payment method is missing':
                return $this->module->l('PayPal payment method is missing', 'Translator');
            case 'Cart is invalid':
                return $this->module->l('Cart is invalid', 'Translator');
            case 'Order cannot be saved':
                return $this->module->l('Order cannot be saved', 'Translator');
            case 'OrderState cannot be saved':
                return $this->module->l('OrderState cannot be saved', 'Translator');
            case 'OrderPayment cannot be saved':
                return $this->module->l('OrderPayment cannot be saved', 'Translator');
            case 'The transaction amount doesn\'t match with the cart amount.':
                return $this->module->l('The transaction amount doesn\'t match with the cart amount.', 'Translator');
            case 'Cart doesn\'t contains product.':
                return $this->module->l('Cart doesn\'t contains product.', 'Translator');
            case 'Cart contains product unavailable.':
                return $this->module->l('Cart contains product unavailable.', 'Translator');
            case 'Cart invoice address is invalid.':
                return $this->module->l('Cart invoice address is invalid.', 'Translator');
            case 'Cart delivery address is invalid.':
                return $this->module->l('Cart delivery address is invalid.', 'Translator');
            case 'Cart delivery option is unavailable.':
                return $this->module->l('Cart delivery option is unavailable.', 'Translator');
            case 'Processing of this card type is not supported. Use another card type.':
                return $this->module->l('Processing of this card type is not supported. Use another card type.', 'Translator');
            case 'The CVC code length is invalid for the specified card type.':
                return $this->module->l('The CVC code length is invalid for the specified card type.', 'Translator');
            case 'Your card cannot be used to pay in this currency, please try another payment method.':
                return $this->module->l('Your card cannot be used to pay in this currency, please try another payment method.', 'Translator');
            case 'Your country is not supported by this payment method, please try to select another.':
                return $this->module->l('Your country is not supported by this payment method, please try to select another.', 'Translator');
            case 'The transaction failed. Please try a different payment method.':
                return $this->module->l('The transaction failed. Please try a different payment method.', 'Translator');
            case 'Transaction expired, please try again.':
                return $this->module->l('Transaction expired, please try again.', 'Translator');
            case 'Order is already captured.':
                return $this->module->l('Order is already captured.', 'Translator');
            case 'The payment is not valid: the amount is not eligible.':
                return $this->module->l('The payment is not valid: the amount is not eligible.', 'Translator');
            case 'This payment method has been refused by the payment platform, please use another payment method.':
                return $this->module->l('This payment method has been refused by the payment platform, please use another payment method.', 'Translator');
            case 'This message is sent automatically by module PrestaShop Checkout':
                return $this->module->l('This message is sent automatically by module PrestaShop Checkout', 'Translator');
            case 'A customer encountered a processing payment error :':
                return $this->module->l('A customer encountered a processing payment error :', 'Translator');
            case 'Customer identifier:':
                return $this->module->l('Customer identifier:', 'Translator');
            case 'Cart identifier:':
                return $this->module->l('Cart identifier:', 'Translator');
            case 'PayPal order identifier:':
                return $this->module->l('PayPal order identifier:', 'Translator');
            case 'Exception identifier:':
                return $this->module->l('Exception identifier:', 'Translator');
            case 'Exception detail:':
                return $this->module->l('Exception detail:', 'Translator');
            case 'If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.':
                return $this->module->l('If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.', 'Translator');
            case 'Payment gateway information':
                return $this->module->l('Payment gateway information', 'Translator');
            case 'Order identifier':
                return $this->module->l('Order identifier', 'Translator');
            case 'Order status':
                return $this->module->l('Order status', 'Translator');
            case 'Transaction identifier':
                return $this->module->l('Transaction identifier', 'Translator');
            case 'Transaction status':
                return $this->module->l('Transaction status', 'Translator');
            case 'Funding source':
                return $this->module->l('Funding source', 'Translator');
            case 'Amount paid':
                return $this->module->l('Amount paid', 'Translator');
            case 'Approve payment':
                return $this->module->l('Approve payment', 'Translator');
            case 'Authenticate payment':
                return $this->module->l('Authenticate payment', 'Translator');
            case 'Your payment has been declined by our payment gateway, please contact us via the link below.':
                return $this->module->l('Your payment has been declined by our payment gateway, please contact us via the link below.', 'Translator');
            case 'Your payment needs to be approved, please click the button below.':
                return $this->module->l('Your payment needs to be approved, please click the button below.', 'Translator');
            case 'Your payment needs to be authenticated, please click the button below.':
                return $this->module->l('Your payment needs to be authenticated, please click the button below.', 'Translator');
            case 'You will be redirected to an external secured page of our payment gateway.':
                return $this->module->l('You will be redirected to an external secured page of our payment gateway.', 'Translator');
            case 'If you have any question, please contact us.':
                return $this->module->l('If you have any question, please contact us.', 'Translator');
            case 'Payment method status':
                return $this->module->l('Payment method status', 'Translator');
            case 'was saved for future purchases':
                return $this->module->l('was saved for future purchases', 'Translator');
            case 'was not saved for future purchases':
                return $this->module->l('was not saved for future purchases', 'Translator');
            case 'Payment':
                return $this->module->l('Payment', 'Translator');
            case 'Refund':
                return $this->module->l('Refund', 'Translator');
            case 'You are not authorized to refund this order.':
                return $this->module->l('You are not authorized to refund this order.', 'Translator');
            case 'PayPal Order is invalid.':
                return $this->module->l('PayPal Order is invalid.', 'Translator');
            case 'PayPal Transaction is invalid.':
                return $this->module->l('PayPal Transaction is invalid.', 'Translator');
            case 'PayPal refund currency is invalid.':
                return $this->module->l('PayPal refund currency is invalid.', 'Translator');
            case 'PayPal refund amount is invalid.':
                return $this->module->l('PayPal refund amount is invalid.', 'Translator');
            case 'PayPal refund failed.':
                return $this->module->l('PayPal refund failed.', 'Translator');
            case 'Refund has been processed by PayPal, but order status change or email sending failed.':
                return $this->module->l('Refund has been processed by PayPal, but order status change or email sending failed.', 'Translator');
            case 'Refund cannot be processed by PayPal.':
                return $this->module->l('Refund cannot be processed by PayPal.', 'Translator');
            case 'Refund has been processed by PayPal.':
                return $this->module->l('Refund has been processed by PayPal.', 'Translator');
            case 'No PrestaShop Order identifier received':
                return $this->module->l('No PrestaShop Order identifier received', 'Translator');
            case 'Unable to find PayPal Order associated to this PrestaShop Order %s':
                return $this->module->l('Unable to find PayPal Order associated to this PrestaShop Order %s', 'Translator');
            case 'PayPal Order %s is not in the same environment as PrestaShop Checkout':
                return $this->module->l('PayPal Order %s is not in the same environment as PrestaShop Checkout', 'Translator');
            case 'Authorization captured successfully.':
                return $this->module->l('Authorization captured successfully.', 'Translator');
            case 'Authorization voided successfully.':
                return $this->module->l('Authorization voided successfully.', 'Translator');
            case 'Authorization reauthorized successfully.':
                return $this->module->l('Authorization reauthorized successfully.', 'Translator');
            case 'Eligible':
                return $this->module->l('Eligible', 'Translator');
            case 'Partially eligible':
                return $this->module->l('Partially eligible', 'Translator');
            case 'Not eligible':
                return $this->module->l('Not eligible', 'Translator');
            case 'Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.':
                return $this->module->l('Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.', 'Translator');
            case 'Your PayPal balance remains intact if the customer claims that they did not receive an item.':
                return $this->module->l('Your PayPal balance remains intact if the customer claims that they did not receive an item.', 'Translator');
            case 'Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.':
                return $this->module->l('Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.', 'Translator');
            case 'Dispute categories covered:':
                return $this->module->l('Dispute categories covered:', 'Translator');
            case 'For more information, please go to the official PayPal website.':
                return $this->module->l('For more information, please go to the official PayPal website.', 'Translator');
            case 'The payer paid for an item that they did not receive.':
                return $this->module->l('The payer paid for an item that they did not receive.', 'Translator');
            case 'The payer did not authorize the payment.':
                return $this->module->l('The payer did not authorize the payment.', 'Translator');
            case 'The authorization has been successfully captured.':
                return $this->module->l('The authorization has been successfully captured.', 'Translator');
            case 'An error occurred during the capture of the authorization.':
                return $this->module->l('An error occurred during the capture of the authorization.', 'Translator');
            case 'The currency you selected is not supported. Please try another payment method or contact support for assistance.':
                return $this->module->l('The currency you selected is not supported. Please try another payment method or contact support for assistance.', 'Translator');
            default:
                return $key;
        }
    }
}
