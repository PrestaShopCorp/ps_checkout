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

namespace PsCheckout\Presentation\Presenter\Settings\Front\Modules;

use PsCheckout\Presentation\Presenter\PresenterInterface;
use PsCheckout\Presentation\TranslatorInterface;

class TranslationModule implements PresenterInterface
{
    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param string $moduleName
     * @param TranslatorInterface $translator
     */
    public function __construct(
        string $moduleName,
        TranslatorInterface $translator
    ) {
        $this->moduleName = $moduleName;
        $this->translator = $translator;
    }

    public function present(): array
    {
        return [
            $this->moduleName . 'CheckoutTranslations' => [
                'checkout.go.back.label' => $this->translator->trans('Checkout'),
                'checkout.go.back.link.title' => $this->translator->trans('Go back to the Checkout'),
                'checkout.card.payment' => $this->translator->trans('Card payment'),
                'checkout.page.heading' => $this->translator->trans('Order summary'),
                'checkout.cart.empty' => $this->translator->trans('Your shopping cart is empty.'),
                'checkout.page.subheading.card' => $this->translator->trans('Card'),
                'checkout.page.subheading.paypal' => $this->translator->trans('PayPal'),
                'checkout.payment.by.card' => $this->translator->trans('You have chosen to pay by Card.'),
                'checkout.payment.by.paypal' => $this->translator->trans('You have chosen to pay by PayPal.'),
                'checkout.order.summary' => $this->translator->trans('Here is a short summary of your order:'),
                'checkout.order.amount.total' => $this->translator->trans('The total amount of your order comes to'),
                'checkout.order.included.tax' => $this->translator->trans('(tax incl.)'),
                'checkout.order.confirm.label' => $this->translator->trans('Please confirm your order by clicking "I confirm my order".'),
                'checkout.payment.token.delete.modal.header' => $this->translator->trans('Delete this payment method?'),
                'checkout.payment.token.delete.modal.content' => $this->translator->trans('The following payment method will be deleted from your account:'),
                'checkout.payment.token.delete.modal.confirm-button' => $this->translator->trans('Delete payment method'),
                'checkout.payment.loader.processing-request' => $this->translator->trans('Please wait, we are processing your request'),
                'checkout.payment.others.link.label' => $this->translator->trans('Other payment methods'),
                'checkout.payment.others.confirm.button.label' => $this->translator->trans('I confirm my order'),
                'checkout.form.error.label' => $this->translator->trans('There was an error during the payment. Please try again or contact the support.'),
                'loader-component.label.header' => $this->translator->trans('Thanks for your purchase!'),
                'loader-component.label.body' => $this->translator->trans('Please wait, we are processing your payment'),
                'loader-component.label.body.longer' => $this->translator->trans('This is taking longer than expected. Please wait...'),
                'payment-method-logos.title' => $this->translator->trans('100% secure payments'),
                'express-button.cart.separator' => $this->translator->trans('or'),
                'express-button.checkout.express-checkout' => $this->translator->trans('Express Checkout'),
                'ok' => $this->translator->trans('Ok'),
                'cancel' => $this->translator->trans('Cancel'),
                'paypal.hosted-fields.label.card-name' => $this->translator->trans('Card holder name'),
                'paypal.hosted-fields.placeholder.card-name' => $this->translator->trans('Card holder name'),
                'paypal.hosted-fields.label.card-number' => $this->translator->trans('Card number'),
                'paypal.hosted-fields.placeholder.card-number' => $this->translator->trans('Card number'),
                'paypal.hosted-fields.label.expiration-date' => $this->translator->trans('Expiry date'),
                'paypal.hosted-fields.placeholder.expiration-date' => $this->translator->trans('MM/YY'),
                'paypal.hosted-fields.label.cvv' => $this->translator->trans('CVC'),
                'paypal.hosted-fields.placeholder.cvv' => $this->translator->trans('XXX'),
                'error.paypal-sdk' => $this->translator->trans('No PayPal Javascript SDK Instance'),
                'error.google-pay-sdk' => $this->translator->trans('No Google Pay Javascript SDK Instance'),
                'error.apple-pay-sdk' => $this->translator->trans('No Apple Pay Javascript SDK Instance'),
                'error.google-pay.transaction-info' => $this->translator->trans('An error occurred fetching Google Pay transaction info'),
                'error.apple-pay.payment-request' => $this->translator->trans('An error occurred fetching Apple Pay payment request'),
                'error.paypal-sdk.contingency.cancel' => $this->translator->trans('Card holder authentication canceled, please choose another payment method or try again.'),
                'error.paypal-sdk.contingency.error' => $this->translator->trans('An error occurred on card holder authentication, please choose another payment method or try again.'),
                'error.paypal-sdk.contingency.failure' => $this->translator->trans('Card holder authentication failed, please choose another payment method or try again.'),
                'error.paypal-sdk.contingency.unknown' => $this->translator->trans('Card holder authentication cannot be checked, please choose another payment method or try again.'),
                'APPLE_PAY_MERCHANT_SESSION_VALIDATION_ERROR' => $this->translator->trans('We’re unable to process your Apple Pay payment at the moment. This could be due to an issue verifying the payment setup for this website. Please try again later or choose a different payment method.'),
                'APPROVE_APPLE_PAY_VALIDATION_ERROR' => $this->translator->trans('We encountered an issue while processing your Apple Pay payment. Please verify your order details and try again, or use a different payment method.')
            ],
        ];
    }
}
