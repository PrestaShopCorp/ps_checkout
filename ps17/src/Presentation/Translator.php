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
        $map = [
            'Checkout' => $this->module->l('Checkout', 'Translator'),
            'Go back to the Checkout' => $this->module->l('Go back to the Checkout', 'Translator'),
            'Card payment' => $this->module->l('Card payment', 'Translator'),
            'Order summary' => $this->module->l('Order summary', 'Translator'),
            'Your shopping cart is empty.' => $this->module->l('Your shopping cart is empty.', 'Translator'),
            'PayPal' => $this->module->l('PayPal', 'Translator'),
            'You have chosen to pay by Card.' => $this->module->l('You have chosen to pay by Card.', 'Translator'),
            'You have chosen to pay by PayPal.' => $this->module->l('You have chosen to pay by PayPal.', 'Translator'),
            'Here is a short summary of your order:' => $this->module->l('Here is a short summary of your order:', 'Translator'),
            'The total amount of your order comes to' => $this->module->l('The total amount of your order comes to', 'Translator'),
            '(tax incl.)' => $this->module->l('(tax incl.)', 'Translator'),
            'Please confirm your order by clicking "I confirm my order".' => $this->module->l('Please confirm your order by clicking "I confirm my order".', 'Translator'),
            'Delete this payment method?' => $this->module->l('Delete this payment method?', 'Translator'),
            'The following payment method will be deleted from your account:' => $this->module->l('The following payment method will be deleted from your account:', 'Translator'),
            'Delete payment method' => $this->module->l('Delete payment method', 'Translator'),
            'Please wait, we are processing your request' => $this->module->l('Please wait, we are processing your request', 'Translator'),
            'Other payment methods' => $this->module->l('Other payment methods', 'Translator'),
            'I confirm my order' => $this->module->l('I confirm my order', 'Translator'),
            'Thanks for your purchase!' => $this->module->l('Thanks for your purchase!', 'Translator'),
            'Please wait, we are processing your payment' => $this->module->l('Please wait, we are processing your payment', 'Translator'),
            'This is taking longer than expected. Please wait...' => $this->module->l('This is taking longer than expected. Please wait...', 'Translator'),
            'Ok' => $this->module->l('Ok', 'Translator'),
            'Cancel' => $this->module->l('Cancel', 'Translator'),
            '100% secure payments' => $this->module->l('100% secure payments', 'Translator'),
            'or' => $this->module->l('or', 'Translator'),
            'Express Checkout' => $this->module->l('Express Checkout', 'Translator'),

            'Card' => $this->module->l('Card', 'Translator'),
            'Pay by Card - 100% secure payments' => $this->module->l('Pay by Card - 100% secure payments', 'Translator'),
            'Pay with a PayPal account' => $this->module->l('Pay with a PayPal account', 'Translator'),
            'Pay in installments with PayPal Pay Later' => $this->module->l('Pay in installments with PayPal Pay Later', 'Translator'),
            'Pay by %s' => $this->module->l('Pay by %s', 'Translator'),
            'Pay with %s' => $this->module->l('Pay with %s', 'Translator'),

            'Card holder name' => $this->module->l('Card holder name', 'Translator'),
            'Card number' => $this->module->l('Card number', 'Translator'),
            'Expiry date' => $this->module->l('Expiry date', 'Translator'),
            'MM/YY' => $this->module->l('MM/YY', 'Translator'),
            'CVC' => $this->module->l('CVC', 'Translator'),
            'XXX' => $this->module->l('XXX', 'Translator'),

            'Created' => $this->module->l('Created', 'Translator'),
            'Saved' => $this->module->l('Saved', 'Translator'),
            'Approved' => $this->module->l('Approved', 'Translator'),
            'Voided' => $this->module->l('Voided', 'Translator'),
            'Completed' => $this->module->l('Completed', 'Translator'),
            'Declined' => $this->module->l('Declined', 'Translator'),
            'Pending' => $this->module->l('Pending', 'Translator'),
            'Partially refunded' => $this->module->l('Partially refunded', 'Translator'),
            'Refunded' => $this->module->l('Refunded', 'Translator'),
            'Failed' => $this->module->l('Failed', 'Translator'),

            'There was an error during the payment. Please try again or contact the support.' => $this->module->l('There was an error during the payment. Please try again or contact the support.', 'Translator'),
            'No PayPal Javascript SDK Instance' => $this->module->l('No PayPal Javascript SDK Instance', 'Translator'),
            'No Google Pay Javascript SDK Instance' => $this->module->l('No Google Pay Javascript SDK Instance', 'Translator'),
            'No Apple Pay Javascript SDK Instance' => $this->module->l('No Apple Pay Javascript SDK Instance', 'Translator'),
            'An error occurred fetching Google Pay transaction info' => $this->module->l('An error occurred fetching Google Pay transaction info', 'Translator'),
            'An error occurred fetching Apple Pay payment request' => $this->module->l('An error occurred fetching Apple Pay payment request', 'Translator'),
            'Card holder authentication canceled, please choose another payment method or try again.' => $this->module->l('Card holder authentication canceled, please choose another payment method or try again.', 'Translator'),
            'An error occurred on card holder authentication, please choose another payment method or try again.' => $this->module->l('An error occurred on card holder authentication, please choose another payment method or try again.', 'Translator'),
            'Card holder authentication failed, please choose another payment method or try again.' => $this->module->l('Card holder authentication failed, please choose another payment method or try again.', 'Translator'),
            'Card holder authentication cannot be checked, please choose another payment method or try again.' => $this->module->l('Card holder authentication cannot be checked, please choose another payment method or try again.', 'Translator'),
            'We’re unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.' => $this->module->l('We’re unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.', 'Translator'),
            'We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.' => $this->module->l('We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.', 'Translator'),

            // Exception messages
            'Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.' => $this->module->l('Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.', 'Translator'),
            'The transaction failed. Please try a different card.' => $this->module->l('The transaction failed. Please try a different card.', 'Translator'),
            'The transaction was refused.' => $this->module->l('The transaction was refused.', 'Translator'),
            'This payment method is unavailable' => $this->module->l('This payment method is unavailable', 'Translator'),
            'Unable to call API' => $this->module->l('Unable to call API', 'Translator'),
            'PayPal order identifier is missing' => $this->module->l('PayPal order identifier is missing', 'Translator'),
            'PayPal payment method is missing' => $this->module->l('PayPal payment method is missing', 'Translator'),
            'Cart is invalid' => $this->module->l('Cart is invalid', 'Translator'),
            'Order cannot be saved' => $this->module->l('Order cannot be saved', 'Translator'),
            'OrderState cannot be saved' => $this->module->l('OrderState cannot be saved', 'Translator'),
            'OrderPayment cannot be saved' => $this->module->l('OrderPayment cannot be saved', 'Translator'),
            'The transaction amount doesn\'t match with the cart amount.' => $this->module->l('The transaction amount doesn\'t match with the cart amount.', 'Translator'),
            'Cart doesn\'t contains product.' => $this->module->l('Cart doesn\'t contains product.', 'Translator'),
            'Cart contains product unavailable.' => $this->module->l('Cart contains product unavailable.', 'Translator'),
            'Cart invoice address is invalid.' => $this->module->l('Cart invoice address is invalid.', 'Translator'),
            'Cart delivery address is invalid.' => $this->module->l('Cart delivery address is invalid.', 'Translator'),
            'Cart delivery option is unavailable.' => $this->module->l('Cart delivery option is unavailable.', 'Translator'),
            'Processing of this card type is not supported. Use another card type.' => $this->module->l('Processing of this card type is not supported. Use another card type.', 'Translator'),
            'The CVC code length is invalid for the specified card type.' => $this->module->l('The CVC code length is invalid for the specified card type.', 'Translator'),
            'Your card cannot be used to pay in this currency, please try another payment method.' => $this->module->l('Your card cannot be used to pay in this currency, please try another payment method.', 'Translator'),
            'Your country is not supported by this payment method, please try to select another.' => $this->module->l('Your country is not supported by this payment method, please try to select another.', 'Translator'),
            'The transaction failed. Please try a different payment method.' => $this->module->l('The transaction failed. Please try a different payment method.', 'Translator'),
            'Transaction expired, please try again.' => $this->module->l('Transaction expired, please try again.', 'Translator'),
            'Order is already captured.' => $this->module->l('Order is already captured.', 'Translator'),
            'This payment method has been refused by the payment platform, please use another payment method.' => $this->module->l('This payment method has been refused by the payment platform, please use another payment method.', 'Translator'),

            // Notify customer error message
            'This message is sent automatically by module PrestaShop Checkout' => $this->module->l('This message is sent automatically by module PrestaShop Checkout', 'Translator'),
            'A customer encountered a processing payment error :' => $this->module->l('A customer encountered a processing payment error :', 'Translator'),
            'Customer identifier:' => $this->module->l('Customer identifier:', 'Translator'),
            'Cart identifier:' => $this->module->l('Cart identifier:', 'Translator'),
            'PayPal order identifier:' => $this->module->l('PayPal order identifier:', 'Translator'),
            'Exception identifier:' => $this->module->l('Exception identifier:', 'Translator'),
            'Exception detail:' => $this->module->l('Exception detail:', 'Translator'),
            'If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.' => $this->module->l('If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.', 'Translator'),

            'Payment gateway information' => $this->module->l('Payment gateway information', 'Translator'),
            'Order identifier' => $this->module->l('Order identifier', 'Translator'),
            'Order status' => $this->module->l('Order status', 'Translator'),
            'Transaction identifier' => $this->module->l('Transaction identifier', 'Translator'),
            'Transaction status' => $this->module->l('Transaction status', 'Translator'),
            'Funding source' => $this->module->l('Funding source', 'Translator'),
            'Amount paid' => $this->module->l('Amount paid', 'Translator'),
            'Approve payment' => $this->module->l('Approve payment', 'Translator'),
            'Authenticate payment' => $this->module->l('Authenticate payment', 'Translator'),
            'Your payment has been declined by our payment gateway, please contact us via the link below.' => $this->module->l('Your payment has been declined by our payment gateway, please contact us via the link below.', 'Translator'),
            'Your payment needs to be approved, please click the button below.' => $this->module->l('Your payment needs to be approved, please click the button below.', 'Translator'),
            'Your payment needs to be authenticated, please click the button below.' => $this->module->l('Your payment needs to be authenticated, please click the button below.', 'Translator'),
            'You will be redirected to an external secured page of our payment gateway.' => $this->module->l('You will be redirected to an external secured page of our payment gateway.', 'Translator'),
            'If you have any question, please contact us.' => $this->module->l('If you have any question, please contact us.', 'Translator'),
            'Payment method status' => $this->module->l('Payment method status', 'Translator'),
            'was saved for future purchases' => $this->module->l('was saved for future purchases', 'Translator'),
            'was not saved for future purchases' => $this->module->l('was not saved for future purchases', 'Translator'),
            'Total ApplePay' => $this->module->l('Total', 'Translator'),
            'Total GooglePay' => $this->module->l('Total', 'Translator'),
            'Payment' => $this->module->l('Payment', 'Translator'),
            'Refund' => $this->module->l('Refund', 'Translator'),
            'You are not authorized to refund this order.' => $this->module->l('You are not authorized to refund this order.', 'Translator'),
            'PayPal Order is invalid.' => $this->module->l('PayPal Order is invalid.', 'Translator'),
            'PayPal Transaction is invalid.' => $this->module->l('PayPal Transaction is invalid.', 'Translator'),
            'PayPal refund currency is invalid.' => $this->module->l('PayPal refund currency is invalid.', 'Translator'),
            'PayPal refund amount is invalid.' => $this->module->l('PayPal refund amount is invalid.', 'Translator'),
            'PayPal refund failed.' => $this->module->l('PayPal refund failed.', 'Translator'),
            'Refund has been processed by PayPal, but order status change or email sending failed.' => $this->module->l('Refund has been processed by PayPal, but order status change or email sending failed.', 'Translator'),
            'Refund cannot be processed by PayPal.' => $this->module->l('Refund cannot be processed by PayPal.', 'Translator'),
            'Refund has been processed by PayPal.' => $this->module->l('Refund has been processed by PayPal.', 'Translator'),
            'No PrestaShop Order identifier received' => $this->module->l('No PrestaShop Order identifier received', 'Translator'),
            'Unable to find PayPal Order associated to this PrestaShop Order %s' => $this->module->l('Unable to find PayPal Order associated to this PrestaShop Order %s', 'Translator'),
            'PayPal Order %s is not in the same environment as PrestaShop Checkout' => $this->module->l('PayPal Order %s is not in the same environment as PrestaShop Checkout', 'Translator'),

            'Eligible' => $this->module->l('Eligible', 'Translator'),
            'Partially eligible' => $this->module->l('Partially eligible', 'Translator'),
            'Not eligible' => $this->module->l('Not eligible', 'Translator'),
            'Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.' => $this->module->l('Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.', 'Translator'),
            'Your PayPal balance remains intact if the customer claims that they did not receive an item.' => $this->module->l('Your PayPal balance remains intact if the customer claims that they did not receive an item.', 'Translator'),
            'Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.' => $this->module->l('Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.', 'Translator'),
            'Dispute categories covered:' => $this->module->l('Dispute categories covered:', 'Translator'),
            'For more information, please go to the official PayPal website.' => $this->module->l('For more information, please go to the official PayPal website.', 'Translator'),
            'The payer paid for an item that they did not receive.' => $this->module->l('The payer paid for an item that they did not receive.', 'Translator'),
            'The payer did not authorize the payment.' => $this->module->l('The payer did not authorize the payment.', 'Translator'),
        ];

        return $map[$key] ?: $key;
    }
}
