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

namespace PrestaShop\Module\PrestashopCheckout\Translations;

class Translations
{
    /**
     * @var \Module
     */
    private $module = null;

    /**
     * @param \Module $module
     */
    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Create all translations (backoffice)
     *
     * @return array translation list
     */
    public function getTranslations()
    {
        $locale = \Context::getContext()->language->locale;
        $linkTranslations = new LinksTranslations($locale);

        $translations[$locale] = [
            'menu' => [
                'authentication' => $this->module->l('Authentication', 'translations'),
                'customizeCheckout' => $this->module->l('Customize checkout experience', 'translations'),
                'manageActivity' => $this->module->l('Manage Activity', 'translations'),
                'advancedSettings' => $this->module->l('Advanced settings', 'translations'),
                'help' => $this->module->l('Help', 'translations'),
            ],
            'general' => [
                'save' => $this->module->l('Save', 'translations'),
                'testModeOn' => $this->module->l('Test mode is turned on', 'translations'),
            ],
            'pages' => [
                'accounts' => [
                    'approved' => $this->module->l('Approved', 'translations'),
                    'approvalPending' => $this->module->l('Approval pending', 'translations'),
                    'emailValidationNeeded' => $this->module->l('Email validation needed', 'translations'),
                    'waitingEmail' => $this->module->l('A confirmation email has been sent. Check your inbox and click on the link to activate your account.', 'translations'),
                    'didntReceiveEmail' => $this->module->l('No confirmation email?', 'translations'),
                    'sendEmailAgain' => $this->module->l('Send it again', 'translations'),
                    'documentNeeded' => $this->module->l('Information needed', 'translations'),
                    'additionalDocumentsNeeded' => $this->module->l('Additional information is required to complete background check. Please go on your www.paypal.com account and check the notification bell on the top right to know which documents are needed. It could be:', 'translations'),
                    'photoIds' => $this->module->l('Credit Card information, bank account information and ID card', 'translations'),
                    'knowMoreAboutAccount' => $this->module->l('Know more about my account approval', 'translations'),
                    'undergoingCheck' => $this->module->l('Background check is currently undergoing', 'translations'),
                    'severalDays' => $this->module->l('It can take several days. If further information is needed, you will be notified. Please check your emails or your notification bell on the top right of your www.paypal.com account and follow the instructions.', 'translations'),
                    'youCanProcess' => $this->module->l('You can process', 'translations'),
                    'upTo' => $this->module->l('up to $500', 'translations'),
                    'transactionsUntil' => $this->module->l('in card transactions until your account is fully approved', 'translations'),
                    'accountDeclined' => $this->module->l('Account declined', 'translations'),
                    'cannotProcessCreditCard' => $this->module->l('Unfortunately, credit card payments cannot be processed for you at the moment. You will be able to reapply after 90 days. In the meantime, you can still receive payments via PayPal', 'translations'),
                    'accountDeclinedLink' => $this->module->l('Account declined FAQs', 'translations'),
                ],
                'signin' => [
                    'logInWithYourPsAccount' => $this->module->l('Log in with your PrestaShop Checkout account', 'translations'),
                    'email' => $this->module->l('Email', 'translations'),
                    'password' => $this->module->l('Password', 'translations'),
                    'forgotPassword' => $this->module->l('Forgot password?', 'translations'),
                    'back' => $this->module->l('Back', 'translations'),
                    'signup' => $this->module->l('Sign up', 'translations'),
                    'login' => $this->module->l('Log in', 'translations'),
                ],
                'signup' => [
                    'createYourPsAccount' => $this->module->l('Create your PrestaShop Checkout account', 'translations'),
                    'email' => $this->module->l('Email', 'translations'),
                    'password' => $this->module->l('Password', 'translations'),
                    'termsOfUse' => $this->module->l('I agree to the ', 'translations'),
                    'termsOfUseLinkText' => $this->module->l('Terms and Conditions of Use of PrestaShop Checkout', 'translations'),
                    'termsOfUseLink' => $linkTranslations->getCheckoutDataPolicyLink(),
                    'termsOfUseError' => $this->module->l('I accept the terms of use', 'translations'),
                    'mentionsTermsText' => $this->module->l('By submitting this form, I agree that the data provided may be collected by PrestaShop S.A to create your PrestaShop Checkout account. By creating your account, you will receive commercial prospecting from PrestaShop', 'Translations'),
                    'mentionsTermsLinkTextPart1' => $this->module->l('except opposition here', 'translations'),
                    'mentionsTermsLinkTextPart2' => $this->module->l('Learn more about managing your data and rights.', 'translations'),
                    'mentionsTermsLinkPart2' => $linkTranslations->getCheckoutDataPolicyLink(),
                    'back' => $this->module->l('Back', 'translations'),
                    'signIn' => $this->module->l('Sign in', 'translations'),
                    'createAccount' => $this->module->l('Create account', 'translations'),
                ],
                'resetPassword' => [
                    'resetPassword' => $this->module->l('Reset password', 'translations'),
                    'youGotEmail' => $this->module->l('You’ve got an email.', 'translations'),
                    'sendEmail' => $this->module->l('We sent you an email with instructions to reset your password. Please check your inbox.', 'translations'),
                    'sendLink' => $this->module->l('We will send you a link to reset your password.', 'translations'),
                    'email' => $this->module->l('Email', 'translations'),
                    'goBackToLogin' => $this->module->l('Go back to login', 'translations'),
                    'reset' => $this->module->l('Reset', 'translations'),
                ],
            ],
            'firebase' => [
                'error' => [
                    'emailExists' => $this->module->l('Email already exist.', 'translations'),
                    'missingEmail' => $this->module->l('The email is missing.', 'translations'),
                    'missingPassword' => $this->module->l('The password is missing.', 'translations'),
                    'invalidEmail' => $this->module->l('The email address is badly formatted.', 'translations'),
                    'invalidPassword' => $this->module->l('The password is invalid.', 'translations'),
                    'emailNotFound' => $this->module->l('The email is not found.', 'translations'),
                    'defaultError' => $this->module->l('Error, try later.', 'translations'),
                ],
            ],
            'panel' => [
                'account-list' => [
                    'accountSettings' => $this->module->l('Account settings', 'translations'),
                    'essentialsAccount' => $this->module->l('PrestaShop Checkout account', 'translations'),
                    'activateAllPayment' => $this->module->l('You need to connect to both PrestaShop Checkout and PayPal accounts to activate all payment methods', 'translations'),
                    'connectedWitdh' => $this->module->l('You are now logged in with your', 'translations'),
                    'account' => $this->module->l('account', 'translations'),
                    'createNewAccount' => $this->module->l('Sign in or login to provide every payment method to your customer.', 'translations'),
                    'createAccount' => $this->module->l('Sign up', 'translations'),
                    'logIn' => $this->module->l('Log in', 'translations'),
                    'logOut' => $this->module->l('Log out', 'translations'),
                    'paypalAccount' => $this->module->l('PayPal account', 'translations'),
                    'activatePayment' => $this->module->l('Log in or sign up to PayPal', 'translations'),
                    'accountIsLinked' => $this->module->l('Your PrestaShop Checkout account is linked to your PayPal account', 'translations'),
                    'linkToPaypal' => $this->module->l('Link to PayPal account', 'translations'),
                    'linkToPsCheckoutFirst' => $this->module->l('Link to PrestaShop Checkout first', 'translations'),
                    'loading' => $this->module->l('Loading', 'translations'),
                    'useAnotherAccount' => $this->module->l('Use another account', 'translations'),
                    'cancel' => $this->module->l('Cancel', 'translations'),
                    'titleLogout' => $this->module->l('Are you sure you want to logout ?', 'translations'),
                    'descriptionLogout' => $this->module->l('Logging out will deactivate all payment methods. You will no longer be able to receive payments with PrestaShop Checkout.', 'translations'),
                    'onboardingLinkError' => $this->module->l('An error occured, the PayPal sign up or login window can\'t be opened. Please wait a bit and click again.', 'translations'),
                ],
                'psx-form' => [
                    'additionalDetails' => $this->module->l('Additional Details', 'translations'),
                    'fillUp' => $this->module->l('Fill out the form to complete registration:', 'translations'),
                    'personalInformation' => $this->module->l('Personal information', 'translations'),
                    'genderMr' => $this->module->l('Mr', 'translations'),
                    'genderMrs' => $this->module->l('Mrs', 'translations'),
                    'firstName' => $this->module->l('First name', 'translations'),
                    'language' => $this->module->l('Language', 'translations'),
                    'lastName' => $this->module->l('Last name', 'translations'),
                    'qualification' => $this->module->l('Are you', 'translations'),
                    'merchant' => $this->module->l('A merchant', 'translations'),
                    'agency' => $this->module->l('An agency', 'translations'),
                    'freelancer' => $this->module->l('A freelancer', 'translations'),
                    'billingAddress' => $this->module->l('Billing address', 'translations'),
                    'storeName' => $this->module->l('Store name', 'translations'),
                    'address' => $this->module->l('Address', 'translations'),
                    'postCode' => $this->module->l('Postcode', 'translations'),
                    'town' => $this->module->l('Town', 'translations'),
                    'country' => $this->module->l('Country', 'translations'),
                    'state' => $this->module->l('State', 'translations'),
                    'businessPhone' => $this->module->l('Business phone', 'translations'),
                    'businessType' => $this->module->l('Business type', 'translations'),
                    'businessInformation' => $this->module->l('Business information', 'translations'),
                    'website' => $this->module->l('Website', 'translations'),
                    'companyTurnover' => $this->module->l('Company estimated monthly turnover', 'translations'),
                    'businessCategory' => $this->module->l('Business category', 'translations'),
                    'businessSubCategory' => $this->module->l('Business subcategory', 'translations'),
                    'optional' => $this->module->l('Optional', 'translations'),
                    'continue' => $this->module->l('Continue', 'translations'),
                    'back' => $this->module->l('Back', 'translations'),
                    'errors' => $this->module->l('Errors', 'translations'),
                    'privacyTextPart1' => $this->module->l('By submitting this form, I agree that the data provided may be collected by PrestaShop S.A to permit (i) the use of our services (ii) to improve your customer experience. Your data can be transmitted to our partner Paypal if you do not already have an account.', 'translations'),
                    'privacyTextPart2' => $this->module->l('Learn more about managing your data and rights.', 'translations'),
                    'privacyLink' => $linkTranslations->getCheckoutDataPolicyLink(),
                ],
                'active-payment' => [
                    'activePaymentMethods' => $this->module->l('Activate payment methods', 'translations'),
                    'changeOrder' => $this->module->l('Change payment methods order', 'translations'),
                    'enabled' => $this->module->l('Enabled', 'translations'),
                    'disabled' => $this->module->l('Disabled', 'translations'),
                    'available' => $this->module->l('Available', 'translations'),
                    'notAvailable' => $this->module->l('Not available', 'translations'),
                    'restricted' => $this->module->l('Restricted', 'translations'),
                    'creditCard' => $this->module->l('Credit card', 'translations'),
                    'paypal' => $this->module->l('PayPal', 'translations'),
                    'localPaymentMethods' => $this->module->l('Local payment methods', 'translations'),
                    'tipsTitle' => $this->module->l('TIPS', 'translations'),
                    'tipsContent' => $this->module->l('Boost your conversion rate by displaying PayPal as the first choice in the list of payment methods', 'translations'),
                ],
                'payment-acceptance' => [
                    'paymentMethod' => $this->module->l('Payment method'),
                    'availability' => $this->module->l('Availability'),
                    'activationStatus' => $this->module->l('Activation status'),
                    'paymentAcceptanceTitle' => $this->module->l('Payment methods acceptance', 'translations'),
                    'creditCardsLabel' => $this->module->l('Credit and Debit Cards', 'translations'),
                    'tips' => $this->module->l('Tips', 'translations'),
                    'alertInfo' => $this->module->l('To test your payment method you can make a real transaction (prefer small amount), and once you have observed the money on your account, make a refund on the corresponding order page. Warning, you will not recover the fees.', 'translations'),
                ],
                'payment-mode' => [
                    'title' => $this->module->l('Payment methods activation', 'translations'),
                    'paymentAction' => $this->module->l('Transaction type', 'translations'),
                    'capture' => $this->module->l('Direct Sale', 'translations'),
                    'authorize' => $this->module->l('Capture at shipping', 'translations'),
                    'helpBoxPaymentMode' => $this->module->l('Authorize process holds all payments on customers’ account. Mark the order as « Shipped » or « Payment accepted » to capture payments. Local Payment Methods are not compatible with Authorize process.', 'translations'),
                    'infoAlertText' => $this->module->l('We recommend « Capture at shipping » if you are a lean manufacturer or a craft products seller', 'translations'),
                    'environment' => $this->module->l('Environment', 'translations'),
                    'sandboxMode' => $this->module->l('Test mode', 'translations'),
                    'useSandboxMode' => $this->module->l('Switch to test mode?', 'translations'),
                    'tipSandboxMode' => $this->module->l('Note that you cannot collect payments with test mode', 'translations'),
                    'productionMode' => $this->module->l('Production mode', 'translations'),
                    'useProductionMode' => $this->module->l('Use production mode', 'translations'),
                    'tipProductionMode' => $this->module->l('Production mode enables you to collect your payments.', 'translations'),
                ],
                'express-checkout' => [
                    'title' => $this->module->l('Define PayPal express checkout flow', 'translations'),
                    'pageLocation' => $this->module->l('Choose page location', 'translations'),
                    'orderPage' => $this->module->l('Order summary page', 'translations'),
                    'checkoutPage' => $this->module->l('Checkout method page', 'translations'),
                    'productPage' => $this->module->l('Product page', 'translations'),
                    'recommended' => $this->module->l('Recommended', 'translations'),
                    'shippingCost' => $this->module->l('Shipping costs, if any, will be estimated in basket total. Delivery method selected by default will be the one set in first position on Carriers page.', 'translations'),
                    'alertTitle' => $this->module->l('TIPS', 'translations'),
                    'alertContent' => $this->module->l('Express checkout shortcut enables to merge account creation and payment into a single step, which reduces UX frictions.', 'translations'),
                ],
                'help' => [
                    'faq' => $this->module->l('FAQ', 'translations'),
                    'title' => $this->module->l('Help for PrestaShop Checkout', 'translations'),
                    'allowsYou' => $this->module->l('This module allows you to:', 'translations'),
                    'tip1' => $this->module->l('Connect your PrestaShop Checkout account and link your PayPal Account or create one if needed', 'translations'),
                    'tip2' => $this->module->l('Offer the widest range of payment methods: cards, PayPal, etc...', 'translations'),
                    'tip3' => $this->module->l('Benefit from all PayPal expertise and advantages', 'translations'),
                    'tip4' => $this->module->l('Give access to relevant local payment methods for customers around the globe', 'translations'),
                    'couldntFindAnswer' => $this->module->l('Couldn\'t find any answer to your question?', 'translations'),
                    'contactUs' => $this->module->l('Contact us', 'translations'),
                    'needHelp' => $this->module->l('Need help? Find here the documentation of this module', 'translations'),
                    'downloadDoc' => $this->module->l('Download PDF', 'translations'),
                    'noFaqAvailable' => $this->module->l('No faq available. Try later.', 'translations'),
                ],
            ],
            'block' => [
                'reassurance' => [
                    'title' => $this->module->l('One module, all payments methods.', 'translations'),
                    'label1' => $this->module->l('Offer the widest range of payment methods: cards, PayPal, etc.', 'translations'),
                    'label2' => $this->module->l('Benefit from all PayPal expertise and advantages', 'translations'),
                    'label3' => $this->module->l('Give access to relevant local payment methods for customers around the globe', 'translations'),
                    'learnMore' => $this->module->l('Learn more', 'translations'),
                ],
                'fraud-tool' => [
                    'title' => $this->module->l('Fraud tool', 'translations'),
                    'text' => $this->module->l('PayPal algorithms automatically limit your fraud rate.
                    If you want to go further in Fraud Management, there is a complete tool on the PayPal platform
                    to set specific rules and drive your performance concerning fraud and chargeback costs.', 'translations'),
                    'discoverFraudTool' => $this->module->l('Go further', 'translations'),
                ],
                'feature-incoming' => [
                    'text' => $this->module->l('Checkout customization, transactions list, dispute management ... and more to come!', 'translations'),
                ],
                'dispute' => [
                    'pendingDispute' => '{disputeCount}' . ' ' . $this->module->l('pending dispute(s)', 'translations'),
                    'goToDispute' => $this->module->l('Go to the dispute management platform', 'translations'),
                ],
                'payment-status' => [
                    'live' => $this->module->l('Live', 'translations'),
                    'approvalPending' => $this->module->l('Approval pending', 'translations'),
                    'limited' => $this->module->l('Limited to $500', 'translations'),
                    'denied' => $this->module->l('Account declined', 'translations'),
                    'disabled' => $this->module->l('Disabled', 'translations'),
                    'paypalLabel' => $this->module->l('Accept payments through PayPal buttons on your checkout page.', 'translations'),
                    'paypalLabelEmailNotValid' => $this->module->l('Your account needs to be validated to accept PayPal payments. Please check your inbox for any email confirmation.', 'translations'),
                    'creditCardLabelLimited' => $this->module->l('You can process a limited amount in card transactions.', 'translations'),
                    'creditCardLabelDenied' => $this->module->l('We cannot process credit card payments for you at the moment.', 'translations'),
                    'creditCardLabelLive' => $this->module->l('Process unlimited card payments. You can accept either credit or debit card.', 'translations'),
                    'creditCardLabelPending' => $this->module->l('Your account needs further checks to accept Credit and Debit Cards payment.', 'translations'),
                ],
                'rounding-banner' => [
                    'title' => $this->module->l('Roundings settings to change', 'translations'),
                    'content' => $this->module->l('Be careful, your roundings settings are not fully compatible with PrestaShop Checkout transaction processing. Some of the transactions could fail. But it is easy, your setting Round mode and Round type should be set on « Round up away from zero, when it is half way there » and « Round on each item » or click on the button bellow to make it automatically !', 'translations'),
                    'button' => $this->module->l('Make my roudings settings fully compatible!', 'translations'),
                ],
            ],
        ];

        return $translations;
    }
}
