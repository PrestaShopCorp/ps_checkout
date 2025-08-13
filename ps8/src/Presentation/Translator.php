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
        $map = [
            'Checkout' => $this->translator->trans('Checkout', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Go back to the Checkout' => $this->translator->trans('Go back to the Checkout', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Card payment' => $this->translator->trans('Card payment', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Order summary' => $this->translator->trans('Order summary', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Your shopping cart is empty.' => $this->translator->trans('Your shopping cart is empty.', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'PayPal' => $this->translator->trans('PayPal', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'You have chosen to pay by Card.' => $this->translator->trans('You have chosen to pay by Card.', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'You have chosen to pay by PayPal.' => $this->translator->trans('You have chosen to pay by PayPal.', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Here is a short summary of your order:' => $this->translator->trans('Here is a short summary of your order:', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'The total amount of your order comes to' => $this->translator->trans('The total amount of your order comes to', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            '(tax incl.)' => $this->translator->trans('(tax incl.)', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Please confirm your order by clicking "I confirm my order".' => $this->translator->trans('Please confirm your order by clicking "I confirm my order".', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Delete this payment method?' => $this->translator->trans('Delete this payment method?', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'The following payment method will be deleted from your account:' => $this->translator->trans('The following payment method will be deleted from your account:', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Delete payment method' => $this->translator->trans('Delete payment method', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Please wait, we are processing your request' => $this->translator->trans('Please wait, we are processing your request', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Other payment methods' => $this->translator->trans('Other payment methods', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'I confirm my order' => $this->translator->trans('I confirm my order', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Thanks for your purchase!' => $this->translator->trans('Thanks for your purchase!', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Please wait, we are processing your payment' => $this->translator->trans('Please wait, we are processing your payment', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'This is taking longer than expected. Please wait...' => $this->translator->trans('This is taking longer than expected. Please wait...', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Ok' => $this->translator->trans('Ok', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Cancel' => $this->translator->trans('Cancel', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            '100% secure payments' => $this->translator->trans('100% secure payments', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'or' => $this->translator->trans('or', $parameters, 'Modules.Checkout.Pscheckout.checkout'),
            'Express Checkout' => $this->translator->trans('Express Checkout', $parameters, 'Modules.Checkout.Pscheckout.checkout'),

            'Card' => $this->translator->trans('Card', $parameters, 'Modules.Checkout.Pscheckout.methods'),
            'Pay by Card - 100% secure payments' => $this->translator->trans('Pay by Card - 100% secure payments', $parameters, 'Modules.Checkout.Pscheckout.methods'),
            'Pay with a PayPal account' => $this->translator->trans('Pay with a PayPal account', $parameters, 'Modules.Checkout.Pscheckout.methods'),
            'Pay in installments with PayPal Pay Later' => $this->translator->trans('Pay in installments with PayPal Pay Later', $parameters, 'Modules.Checkout.Pscheckout.methods'),
            'Pay by %s' => $this->translator->trans('Pay by %s', $parameters, 'Modules.Checkout.Pscheckout.methods'),
            'Pay with %s' => $this->translator->trans('Pay with %s', $parameters, 'Modules.Checkout.Pscheckout.methods'),

            'Card holder name' => $this->translator->trans('Card holder name', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Card number' => $this->translator->trans('Card number', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Expiry date' => $this->translator->trans('Expiry date', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'MM/YY' => $this->translator->trans('MM/YY', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'CVC' => $this->translator->trans('CVC', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'XXX' => $this->translator->trans('XXX', $parameters, 'Modules.Checkout.Pscheckout.paypal'),

            'Created' => $this->translator->trans('Created', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Saved' => $this->translator->trans('Saved', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Approved' => $this->translator->trans('Approved', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Voided' => $this->translator->trans('Voided', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Completed' => $this->translator->trans('Completed', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Declined' => $this->translator->trans('Declined', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Pending' => $this->translator->trans('Pending', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Partially refunded' => $this->translator->trans('Partially refunded', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Refunded' => $this->translator->trans('Refunded', $parameters, 'Modules.Checkout.Pscheckout.paypal'),
            'Failed' => $this->translator->trans('Failed', $parameters, 'Modules.Checkout.Pscheckout.paypal'),

            'There was an error during the payment. Please try again or contact the support.' => $this->translator->trans('There was an error during the payment. Please try again or contact the support.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'No PayPal Javascript SDK Instance' => $this->translator->trans('No PayPal Javascript SDK Instance', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'No Google Pay Javascript SDK Instance' => $this->translator->trans('No Google Pay Javascript SDK Instance', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'No Apple Pay Javascript SDK Instance' => $this->translator->trans('No Apple Pay Javascript SDK Instance', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'An error occurred fetching Google Pay transaction info' => $this->translator->trans('An error occurred fetching Google Pay transaction info', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'An error occurred fetching Apple Pay payment request' => $this->translator->trans('An error occurred fetching Apple Pay payment request', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Card holder authentication canceled, please choose another payment method or try again.' => $this->translator->trans('Card holder authentication canceled, please choose another payment method or try again.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'An error occurred on card holder authentication, please choose another payment method or try again.' => $this->translator->trans('An error occurred on card holder authentication, please choose another payment method or try again.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Card holder authentication failed, please choose another payment method or try again.' => $this->translator->trans('Card holder authentication failed, please choose another payment method or try again.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Card holder authentication cannot be checked, please choose another payment method or try again.' => $this->translator->trans('Card holder authentication cannot be checked, please choose another payment method or try again.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'We’re unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.' => $this->translator->trans('We’re unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.' => $this->translator->trans('We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.', $parameters, 'Modules.Checkout.Pscheckout.error'),

            // Exception messages
            'Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.' => $this->translator->trans('Error processing payment, you could have been charged. Please check your order history in your account to check the status of the order or please contact our customer service to know more.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'The transaction failed. Please try a different card.' => $this->translator->trans('The transaction failed. Please try a different card.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'The transaction was refused.' => $this->translator->trans('The transaction was refused.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'This payment method is unavailable' => $this->translator->trans('This payment method is unavailable', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Unable to call API' => $this->translator->trans('Unable to call API', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'PayPal order identifier is missing' => $this->translator->trans('PayPal order identifier is missing', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'PayPal payment method is missing' => $this->translator->trans('PayPal payment method is missing', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Cart is invalid' => $this->translator->trans('Cart is invalid', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Order cannot be saved' => $this->translator->trans('Order cannot be saved', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'OrderState cannot be saved' => $this->translator->trans('OrderState cannot be saved', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'OrderPayment cannot be saved' => $this->translator->trans('OrderPayment cannot be saved', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'The transaction amount doesn\'t match with the cart amount.' => $this->translator->trans('The transaction amount doesn\'t match with the cart amount.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Cart doesn\'t contains product.' => $this->translator->trans('Cart doesn\'t contains product.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Cart contains product unavailable.' => $this->translator->trans('Cart contains product unavailable.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Cart invoice address is invalid.' => $this->translator->trans('Cart invoice address is invalid.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Cart delivery address is invalid.' => $this->translator->trans('Cart delivery address is invalid.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Cart delivery option is unavailable.' => $this->translator->trans('Cart delivery option is unavailable.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Processing of this card type is not supported. Use another card type.' => $this->translator->trans('Processing of this card type is not supported. Use another card type.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'The CVC code length is invalid for the specified card type.' => $this->translator->trans('The CVC code length is invalid for the specified card type.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Your card cannot be used to pay in this currency, please try another payment method.' => $this->translator->trans('Your card cannot be used to pay in this currency, please try another payment method.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Your country is not supported by this payment method, please try to select another.' => $this->translator->trans('Your country is not supported by this payment method, please try to select another.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'The transaction failed. Please try a different payment method.' => $this->translator->trans('The transaction failed. Please try a different payment method.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Transaction expired, please try again.' => $this->translator->trans('Transaction expired, please try again.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Order is already captured.' => $this->translator->trans('Order is already captured.', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'This payment method has been refused by the payment platform, please use another payment method.' => $this->translator->trans('This payment method has been refused by the payment platform, please use another payment method.', $parameters, 'Modules.Checkout.Pscheckout.error'),

            // Notify customer error message
            'This message is sent automatically by module PrestaShop Checkout' => $this->translator->trans('This message is sent automatically by module PrestaShop Checkout', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'A customer encountered a processing payment error :' => $this->translator->trans('A customer encountered a processing payment error :', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Customer identifier:' => $this->translator->trans('Customer identifier:', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Cart identifier:' => $this->translator->trans('Cart identifier:', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'PayPal order identifier:' => $this->translator->trans('PayPal order identifier:', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Exception identifier:' => $this->translator->trans('Exception identifier:', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'Exception detail:' => $this->translator->trans('Exception detail:', $parameters, 'Modules.Checkout.Pscheckout.error'),
            'If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.' => $this->translator->trans('If you need assistance, please contact our Support Team on PrestaShop Checkout configuration page on Help subtab.', $parameters, 'Modules.Checkout.Pscheckout.support'),

            'Payment gateway information' => $this->translator->trans('Payment gateway information', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Order identifier' => $this->translator->trans('Order identifier', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Order status' => $this->translator->trans('Order status', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Transaction identifier' => $this->translator->trans('Transaction identifier', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Transaction status' => $this->translator->trans('Transaction status', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Funding source' => $this->translator->trans('Funding source', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Amount paid' => $this->translator->trans('Amount paid', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Approve payment' => $this->translator->trans('Approve payment', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Authenticate payment' => $this->translator->trans('Authenticate payment', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Your payment has been declined by our payment gateway, please contact us via the link below.' => $this->translator->trans('Your payment has been declined by our payment gateway, please contact us via the link below.', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Your payment needs to be approved, please click the button below.' => $this->translator->trans('Your payment needs to be approved, please click the button below.', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Your payment needs to be authenticated, please click the button below.' => $this->translator->trans('Your payment needs to be authenticated, please click the button below.', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'You will be redirected to an external secured page of our payment gateway.' => $this->translator->trans('You will be redirected to an external secured page of our payment gateway.', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'If you have any question, please contact us.' => $this->translator->trans('If you have any question, please contact us.', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Payment method status' => $this->translator->trans('Payment method status', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'was saved for future purchases' => $this->translator->trans('was saved for future purchases', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'was not saved for future purchases' => $this->translator->trans('was not saved for future purchases', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Total ApplePay' => $this->translator->trans('Total', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Total GooglePay' => $this->translator->trans('Total', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Payment' => $this->translator->trans('Payment', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'Refund' => $this->translator->trans('Refund', $parameters, 'Modules.Checkout.Pscheckout.order'),
            'You are not authorized to refund this order.' => $this->translator->trans('You are not authorized to refund this order.', $parameters, 'Modules.Checkout.Pscheckout.order'),

            'Eligible' => $this->translator->trans('Eligible', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'Partially eligible' => $this->translator->trans('Partially eligible', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'Not eligible' => $this->translator->trans('Not eligible', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.' => $this->translator->trans('Your PayPal balance remains intact if the customer claims that they did not receive an item or the account holder claims that they did not authorize the payment.', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'Your PayPal balance remains intact if the customer claims that they did not receive an item.' => $this->translator->trans('Your PayPal balance remains intact if the customer claims that they did not receive an item.', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.' => $this->translator->trans('Your PayPal balance is not protected, the transaction is not eligible to the seller protection program.', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'Dispute categories covered:' => $this->translator->trans('Dispute categories covered:', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'For more information, please go to the official PayPal website.' => $this->translator->trans('For more information, please go to the official PayPal website.', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'The payer paid for an item that they did not receive.' => $this->translator->trans('The payer paid for an item that they did not receive.', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
            'The payer did not authorize the payment.' => $this->translator->trans('The payer did not authorize the payment.', $parameters, 'Modules.Checkout.Pscheckout.sellerProtection'),
        ];

        return $map[$key] ?? $key;
    }
}
