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
        $locale = \Context::getContext()->language->iso_code;
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
                'wrongConfiguration' => $this->module->l('An error during configuration was detected. Please reset the PrestaShop Checkout module and configure it again.', 'translations'),
                'multiShop' => [
                    'title' => $this->module->l('Multistore detected', 'translations'),
                    'subtitle' => $this->module->l('Each shop must be configured separately, even if you configure the same account on all of them.', 'translations'),
                    'chooseOne' => $this->module->l('Please select the first shop to configure from the list below :', 'translations'),
                    'group' => 'Group:',
                    'configure' => 'Configure',
                    'tips' => $this->module->l('Once you are done with the first shop, you can configure the others: select them one by one with the shop selector, in the horizontal menu.', 'translations'),
                ],
                'cantReceivePayments' => $this->module->l('You can not receive Payments in PayPal at this moment, please contact PayPal to solve this problem', 'translations'),
                'paymentPreferences' => $this->module->l('Payment preferences', 'translations'),
            ],
            'pages' => [
                'accounts' => [
                    'approved' => $this->module->l('Approved', 'translations'),
                    'approvalPending' => $this->module->l('Approval pending', 'translations'),
                    'accountLinkingInProgress' => $this->module->l('Account Linking in progress', 'translations'),
                    'waitingPaypalLinkingTitle' => $this->module->l('Onboarding almost done!', 'translations'),
                    'waitingPaypalLinking' => $this->module->l('Synchronization between your store and your PayPal account is in progress. Please wait.', 'translations'),
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

                    'suspendedAlertTitle' => $this->module->l('Credit Card availability suspended', 'translations'),
                    'suspendedAlertLabel' => $this->module->l('Unfortunately, credit card payments cannot be processed for you at the moment. In the meantime, you can still receive payments via PayPal. Please proceed to some actions from your PayPal account, where you should have received a notification. If not, please contact PayPal help center.', 'translations'),
                    'suspendedButton' => $this->module->l('Manage your account', 'translations'),
                    'revokedAlertTitle' => $this->module->l('Credit Card availability revoked', 'translations'),
                    'revokedAlertLabel' => $this->module->l('Unfortunately, you have revoked PrestaShop Checkout permissions. Credit card payments cannot be processed for you. You can still receive payments via PayPal. Please, log out your PayPal account just below and link your account giving permission again. You can contact PayPal help center to have more information about your account.', 'translations'),
                    'revokedButton' => $this->module->l('Manage your account', 'translations'),
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
                'customize' => [
                    'customThemeWarningMessage1' => $this->module->l('All custom themes might not be fully compatible. To avoid potential integration issues on payment page,', 'translations'),
                    'customThemeWarningMessage2' => $this->module->l('please check our list of customizable settings', 'translations'),
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
            'banner' => [
                'paypalStatus' => [
                    'buttonSuccess' => $this->module->l('Thank you, close this message', 'translations'),
                    'buttonLegal' => $this->module->l('Send my legal documents now', 'translations'),
                    'allSet' => $this->module->l('You\'re all set !', 'translations'),
                    'congrats' => $this->module->l('Congrats ! You can start selling online now.', 'translations'),
                    'waitingFinalApprove' => $this->module->l('As soon as PayPal gets all your documents, you\'ll have to wait 48h for final approval.', 'translations'),
                    'oneMoreThing' => $this->module->l('One more thing : send documents to be fully approved by PayPal', 'translations'),
                    'psAccountConnected' => $this->module->l('Connect your PrestaShop account', 'translations'),
                    'paypalAccountConnected' => $this->module->l('Link your PayPal account', 'translations'),
                    'legalDocumentsSent' => $this->module->l('Send your legal documents to PayPal : ', 'translations'),
                    'upTo' => $this->module->l('in the meantime, you can sell now up to 500$ in card transactions.', 'translations'),
                    'onlyCC' => $this->module->l('in the meantime, you will only accept credit cards payments thought the PayPal branded credit card fields', 'translations'),
                    'confirmation' => $this->module->l('We have received the confirmation from PayPal. You can now process all card transactions with no limits.', 'translations'),
                ],
                'paypalIncompatibleCountry' => [
                    'title' => $this->module->l('PrestaShop Checkout transactions won\'t work in some of your configured countries, but there is a solution !', 'translations'),
                    'content' => $this->module->l('Please upgrade your settings for :', 'translations'),
                    'changeCodes' => $this->module->l('Change countries ISO Codes', 'translations'),
                    'changeActivation' => $this->module->l('Change countries activation for this payment module', 'translations'),
                    'more' => $this->module->l('Know more about compliant ISO Codes', 'translations'),
                ],
                'paypalIncompatibleCurrency' => [
                    'title' => $this->module->l('PrestaShop Checkout transactions won\'t work in some of your configured currencies, but there is a solution !', 'translations'),
                    'content' => $this->module->l('Please upgrade your settings for :', 'translations'),
                    'changeCodes' => $this->module->l('Change currencies ISO Codes', 'translations'),
                    'changeActivation' => $this->module->l('Change currencies activation for this payment module', 'translations'),
                    'more' => $this->module->l('Know more about compliant ISO Codes', 'translations'),
                ],
                'paypalValueProposition' => [
                    'titleFees' => $this->module->l('Competitive and transparent fees', 'translations'),
                    'titlePaymentMethods' => $this->module->l('10 payment methods included all-in-one', 'translations'),
                    'titleConversions' => $this->module->l('No redirection when paying by credit card', 'translations'),
                    'fees' => [
                        'title' => $this->module->l('Starting from', 'translations'),
                        'subtitle' => $this->module->l('per transaction depending on your country. ', 'translations'),
                        'linkLabel' => $this->module->l('Know more', 'translations'),
                        'linkHref' => $this->module->l('https://www.prestashop.com/en/prestashop-checkout', 'translations'),
                        'popup' => $this->module->l('For more informations about your fees please visit this page:', 'translations'),
                        'popupLinkLabel' => $this->module->l('PrestaShop Checkout 2.0', 'translations'),
                    ],
                    'conversions' => [
                        'title' => $this->module->l('Higher conversions', 'translations'),
                    ],
                ],
            ],
            'panel' => [
                'accounts' => [
                    'activateAllPayment' => $this->module->l('You need to connect to both PrestaShop and PayPal accounts to activate all payment methods', 'translations'),
                    'paypal' => [
                        'title' => $this->module->l('PayPal account', 'translations'),
                        'activate' => $this->module->l('Log in or sign up to PayPal', 'translations'),
                        'isLinked' => $this->module->l('Your PrestaShop account is linked to your PayPal account', 'translations'),
                        'useAnotherAccount' => $this->module->l('Use another account', 'translations'),
                        'linkToPaypal' => $this->module->l('Link to PayPal account', 'translations'),
                        'linkToPsCheckoutFirst' => $this->module->l('Link to PrestaShop Checkout first', 'translations'),
                        'loading' => $this->module->l('Loading', 'translations'),
                        'onboardingLinkError' => $this->module->l('An error occured, the PayPal sign up or login window can\'t be opened. Please wait a bit and click again.', 'translations'),
                    ],
                    'checkout' => [
                        'title' => $this->module->l('PrestaShop Checkout account', 'translations'),
                        'connectedWith' => $this->module->l('You are now logged in with your', 'translations'),
                        'account' => $this->module->l('account', 'translations'),
                        'createNewAccount' => $this->module->l('Sign in or login to provide every payment method to your customer.', 'translations'),
                        'logIn' => $this->module->l('Log in', 'translations'),
                        'createAccount' => $this->module->l('Sign up', 'translations'),
                        'logOut' => $this->module->l('Log out', 'translations'),
                        'titleLogout' => $this->module->l('Are you sure you want to logout ?', 'translations'),
                        'descriptionLogout' => $this->module->l('Logging out will deactivate all payment methods. You will no longer be able to receive payments with PrestaShop Checkout.', 'translations'),
                        'cancel' => $this->module->l('Cancel', 'translations'),
                    ],
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
                    'availableIn' => $this->module->l('available in :', 'translations'),
                    'allCountries' => $this->module->l('All countries', 'translations'),
                    'localPaymentMethods' => $this->module->l('Local payment methods', 'translations'),
                    'tipsTitle' => $this->module->l('TIPS', 'translations'),
                    'tipsContent' => $this->module->l('Boost your conversion rate by displaying PayPal as the first choice in the list of payment methods', 'translations'),
                ],
                'payment-acceptance' => [
                    'paymentMethod' => $this->module->l('Payment method', 'translations'),
                    'availability' => $this->module->l('Availability', 'translations'),
                    'activationStatus' => $this->module->l('Activation status', 'translations'),
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
                'payment-method-activation' => [
                    'title' => $this->module->l('Alternative Credit Card Fields activation', 'translations'),
                    'label' => $this->module->l('PayPal Branded Credit Card Fields', 'translations'),
                    'disable' => $this->module->l('You can choose the type of credit card fields only if Credit card is activated in « Customize checkout experience » tab.', 'translations'),
                    'popover-difference-question' => $this->module->l('What is the difference between Integrated Credit Card fields and PayPal branded Credit Card Fields ?', 'translations'),
                    'popover-when-question' => $this->module->l('When to use PayPal branded Credit Card fields ?', 'translations'),
                    'popover-difference-answer-begin' => $this->module->l('Integrated Credit Card fields provide the best payment experience you can find in PrestaShop. Well integrated in your checkout process, not branded, with the fewest number of fields, and lowest fee rates (see them on ', 'translations'),
                    'popover-difference-answer-end' => $this->module->l(' ) : we highly recommend to use these ones, by default. But you need PayPal full approval for accepting Credit Cards payment with the fields. You can see the status of this approval in the ', 'translations'),
                    'popover-when-answer' => $this->module->l('If approval is pending or issues are encountered with the Integrated fields, you can activate these fields as a backup, only if Integrated fields are not available or deactivated. The fees are the same as PayPal payment method.', 'translations'),
                    'integrated-credit-card-fields' => $this->module->l('Integrated Credit Card Fields', 'translations'),
                    'paypal-branded-credit-card-fields' => $this->module->l('PayPal branded Credit Card Fields', 'translations'),
                ],
                'express-checkout' => [
                    'title' => $this->module->l('Define PayPal express checkout flow', 'translations'),
                    'pageLocation' => $this->module->l('Choose page location', 'translations'),
                    'orderPage' => $this->module->l('Order summary page', 'translations'),
                    'checkoutPage' => $this->module->l('Sign up on order page', 'translations'),
                    'productPage' => $this->module->l('Product page', 'translations'),
                    'recommended' => $this->module->l('Recommended', 'translations'),
                    'shippingCost' => $this->module->l('Shipping costs, if any, will be estimated in basket total. Delivery method selected by default will be the one set in first position on Carriers page.', 'translations'),
                    'alertTitle' => $this->module->l('TIPS', 'translations'),
                    'alertContent' => $this->module->l('Express Checkout Shortcut allows merging account creation and payment, to make your customers purchases effortless.', 'translations'),
                ],
                'button-customization' => [
                    'title' => $this->module->l('Design smart payment buttons', 'translations'),
                    'shape' => [
                        'title' => $this->module->l('Adjust shape for all buttons', 'translations'),
                        'select' => $this->module->l('Select the shape', 'translations'),
                        'pill' => $this->module->l('Pill', 'translations'),
                        'rect' => $this->module->l('Rectangle', 'translations'),
                    ],
                    'customize' => [
                        'title' => $this->module->l('Customize PayPal button', 'translations'),
                        'label' => [
                            'select' => $this->module->l('Choose label', 'translations'),
                            'pay' => $this->module->l('Pay with', 'translations'),
                            'checkout' => $this->module->l('Checkout', 'translations'),
                            'buynow' => $this->module->l('Buy Now', 'translations'),
                        ],
                        'color' => [
                            'select' => $this->module->l('Select background color', 'translations'),
                            'gold' => $this->module->l('Gold', 'translations'),
                            'blue' => $this->module->l('Blue', 'translations'),
                            'silver' => $this->module->l('Silver', 'translations'),
                            'white' => $this->module->l('White', 'translations'),
                            'black' => $this->module->l('Black', 'translations'),
                        ],
                        'tips' => [
                            'title' => $this->module->l('TIPS', 'translations'),
                            'content' => $this->module->l('Gold version shows better results on conversion rate', 'translations'),
                        ],
                        'save' => $this->module->l('Save', 'translations'),
                        'savedConfiguration' => $this->module->l('Button configuration saved with success !', 'translations'),
                    ],
                    'preview' => [
                        'title' => $this->module->l('Preview', 'translations'),
                        'paypal-button' => $this->module->l('PayPal button', 'translations'),
                        'local-payment-buttons' => $this->module->l('Local payment buttons', 'translations'),
                        'notice' => $this->module->l('As for local payment methods, buttons will be displayed according to the purchase context: country, amount, device, etc.', 'translations'),
                    ],
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
                'pay-in-4x' => [
                    'title' => $this->module->l('Pay in 4x messaging banner', 'translations'),
                    'alert-content' => $this->module->l('Displaying this message has shown a better conversion rate and a raise of Average Order Value.', 'translations'),
                ],
            ],
            'account-settings-deeplink' => [
                'fraud-tool' => [
                    'title' => $this->module->l('Limit your fraud rate', 'translations'),
                    'description' => $this->module->l('PayPal algorithms automatically limit your fraud rate. There is a complete tool on PayPal to set specific rules and drive your performance concerning fraud and chargeback costs.', 'translations'),
                    'link-title' => $this->module->l('Setup fraud tool', 'translations'),
                    'icon-title' => $this->module->l('Fraud tool', 'translations'),
                ],
                'bank-account' => [
                    'title' => $this->module->l('Adjust your bank account', 'translations'),
                    'description' => $this->module->l('Within your PayPal account, you can add a bank account to be beneficiary of your money transfer.', 'translations'),
                    'link-title' => $this->module->l('Manage bank account', 'translations'),
                    'icon-title' => $this->module->l('Bank account', 'translations'),
                ],
                'currencies' => [
                    'title' => $this->module->l('Match currencies', 'translations'),
                    'description' => $this->module->l('You can manage the currencies of your PayPal account. Ideally, make them match with the available currencies of your store.', 'translations'),
                    'link-title' => $this->module->l('Manage currencies', 'translations'),
                    'icon-title' => $this->module->l('Currencies', 'translations'),
                ],
                'conversion-rules' => [
                    'title' => $this->module->l('Define the currency conversion rules', 'translations'),
                    'description' => $this->module->l('Let\'s choose the conversion rules for any transaction in a currency other than those activated on your account, should they be automatically converted or request your validation.', 'translations'),
                    'link-title' => $this->module->l('Manage conversion rules', 'translations'),
                    'icon-title' => $this->module->l('Conversion rules', 'translations'),
                ],
                'soft-descriptor' => [
                    'title' => $this->module->l('Configure your bank statements description', 'translations'),
                    'description' => $this->module->l('You can choose the short or long description that appears on your customer\'s bank statements. Let\'s make them sure to understand their transaction!', 'translations'),
                    'link-title' => $this->module->l('Set up description', 'translations'),
                    'icon-title' => $this->module->l('Details for Bank statements', 'translations'),
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
                'feature-incoming' => [
                    'text' => $this->module->l('Pay in 4x, save credit cards, authorize mode, send payments direct link... and more to come! Let us know what you would love to be added to this module: any new feature or behavior improvement!', 'translations'),
                    'submitIdea' => $this->module->l('Submit idea', 'translations'),
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
                    'revoked' => $this->module->l('Revoked', 'translations'),
                    'suspended' => $this->module->l('Suspended', 'translations'),
                    'paypalLabel' => $this->module->l('Accept payments through PayPal buttons on your checkout page.', 'translations'),
                    'paypalLabelEmailNotValid' => $this->module->l('Your account needs to be validated to accept PayPal payments. Please check your inbox for any email confirmation.', 'translations'),
                    'creditCardLabelLimited' => $this->module->l('You can process a limited amount in card transactions.', 'translations'),
                    'creditCardLabelSuspended' => $this->module->l('The capability can no longer be used, but there are remediation steps to regain access to the corresponding functionality.', 'translations'),
                    'creditCardLabelRevoked' => $this->module->l('The capability can no longer be used and there are no remediation steps available to regain the functionality.', 'translations'),
                    'creditCardLabelDenied' => $this->module->l('We cannot process credit card payments for you at the moment.', 'translations'),
                    'creditCardLabelLive' => $this->module->l('Process unlimited card payments. You can accept either credit or debit card.', 'translations'),
                    'creditCardLabelPending' => $this->module->l('Your account needs further checks to accept Credit and Debit Cards payment.', 'translations'),
                ],
                'rounding-banner' => [
                    'title' => $this->module->l('Roundings settings to change', 'translations'),
                    'content' => $this->module->l('Be careful, your roundings settings are not fully compatible with PrestaShop Checkout transaction processing. Some of the transactions could fail. But it is easy, your setting Round mode and Round type should be set on « Round up away from zero, when it is half way there » and « Round on each item » or click on the button bellow to make it automatically !', 'translations'),
                    'button' => $this->module->l('Change rounding settings', 'translations'),
                    'confirmationTitle' => $this->module->l('Settings updated !', 'translations'),
                    'confirmationLabel' => $this->module->l('Your rounding settings are now fully compatible', 'translations'),
                ],
                'reporting' => [
                    'pending' => $this->module->l('pending', 'translations'),
                    'title' => $this->module->l('All transactions', 'translations'),
                    'subTitle1' => $this->module->l('pending transaction(s)', 'translations'),
                    'subTitle2' => $this->module->l('transaction(s)', 'translations'),
                    'label' => $this->module->l('See below the transactions processed with PrestaShop Checkout, limited to the last 1000 to load them faster.', 'translations'),
                    'subtitleLinkLabel' => $this->module->l('See the full list of transactions on your PayPal account', 'translations'),
                    'goToPaypal' => $this->module->l('Go to PayPal', 'translations'),
                    'goToTransaction' => $this->module->l('Go to PayPal', 'translations'),
                    'orderPendingTableTitle' => $this->module->l('Pending transaction', 'translations'),
                    'transactionTableTitle' => $this->module->l('All transactions', 'translations'),
                    'type' => [
                        'payment' => $this->module->l('Payment', 'translations'),
                        'refund' => $this->module->l('Refund', 'translations'),
                    ],
                    'column' => [
                        'date' => $this->module->l('Date', 'translations'),
                        'orderId' => $this->module->l('Order Id', 'translations'),
                        'customer' => $this->module->l('Customer', 'translations'),
                        'type' => $this->module->l('Type', 'translations'),
                        'beforeCommission' => $this->module->l('Before commission', 'translations'),
                        'commission' => $this->module->l('Commission', 'translations'),
                        'total' => $this->module->l('Total', 'translations'),
                        'actions' => $this->module->l('Actions', 'translations'),
                    ],
                ],
            ],
        ];

        return $translations;
    }
}
