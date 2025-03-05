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
    public function __construct(private \Ps_checkout $psCheckout)
    {
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
                'authentication' => $this->psCheckout->l('Authentication', 'translations'),
                'customizeCheckout' => $this->psCheckout->l('Customize checkout experience', 'translations'),
                'manageActivity' => $this->psCheckout->l('Manage Activity', 'translations'),
                'advancedSettings' => $this->psCheckout->l('Advanced settings', 'translations'),
                'help' => $this->psCheckout->l('Help', 'translations'),
            ],
            'general' => [
                'save' => $this->psCheckout->l('Save', 'translations'),
                'testModeOn' => $this->psCheckout->l('Test mode is turned on', 'translations'),
                'wrongConfiguration' => $this->psCheckout->l('An error during configuration was detected. Please reset the PrestaShop Checkout module and configure it again.', 'translations'),
                'multiShop' => [
                    'title' => $this->psCheckout->l('Multistore detected', 'translations'),
                    'subtitle' => $this->psCheckout->l('Each shop must be configured separately, even if you configure the same account on all of them.', 'translations'),
                    'chooseOne' => $this->psCheckout->l('Please select the first shop to configure from the list below :', 'translations'),
                    'group' => 'Group:',
                    'configure' => 'Configure',
                    'tips' => $this->psCheckout->l('Once you are done with the first shop, you can configure the others: select them one by one with the shop selector, in the horizontal menu.', 'translations'),
                ],
                'errors' => [
                    'cantReceivePayments' => $this->psCheckout->l('There are no payment methods available. You can\'t receive payments from PayPal right now and your customers can\'t proceed to payments (anymore) via the payment methods provided by PrestaShop Checkout.', 'translations'),
                    'contactPaypalCustomerService' => $this->psCheckout->l('Please contact PayPal Customer Service', 'translations'),
                    'decimalCurrenciesError' => $this->psCheckout->l('Warning : the currencies you have activated : {0} are not currently supported by PrestaShop Checkout. Please deactivate {0} for PrestaShop Checkout in your', 'translations'),
                    'paymentPreferences' => $this->psCheckout->l('Payment preferences', 'translations'),
                ],
            ],
            'pages' => [
                'accounts' => [
                    'approved' => $this->psCheckout->l('Approved', 'translations'),
                    'approvalPending' => $this->psCheckout->l('Approval pending', 'translations'),
                    'accountLinkingInProgress' => $this->psCheckout->l('Account Linking in progress', 'translations'),
                    'waitingPaypalLinkingTitle' => $this->psCheckout->l('Onboarding almost done!', 'translations'),
                    'waitingPaypalLinking' => $this->psCheckout->l('Synchronization between your store and your PayPal account is in progress. Please wait.', 'translations'),
                    'emailValidationNeeded' => $this->psCheckout->l('Email validation needed', 'translations'),
                    'waitingEmail' => $this->psCheckout->l('A confirmation email has been sent. Check your inbox and click on the link to activate your account.', 'translations'),
                    'didntReceiveEmail' => $this->psCheckout->l('No confirmation email?', 'translations'),
                    'sendEmailAgain' => $this->psCheckout->l('Send it again', 'translations'),
                    'documentNeeded' => $this->psCheckout->l('Information needed', 'translations'),
                    'additionalDocumentsNeeded' => $this->psCheckout->l('Additional information is required to complete background check. Please go on your www.paypal.com account and check the notification bell on the top right to know which documents are needed. It could be:', 'translations'),
                    'photoIds' => $this->psCheckout->l('Credit Card information, bank account information and ID card', 'translations'),
                    'knowMoreAboutAccount' => $this->psCheckout->l('Know more about my account approval', 'translations'),
                    'undergoingCheck' => $this->psCheckout->l('Background check is currently undergoing', 'translations'),
                    'severalDays' => $this->psCheckout->l('It can take several days. If further information is needed, you will be notified. Please check your emails or your notification bell on the top right of your www.paypal.com account and follow the instructions.', 'translations'),
                    'youCanProcess' => $this->psCheckout->l('You can process', 'translations'),
                    'upTo' => $this->psCheckout->l('up to $500', 'translations'),
                    'transactionsUntil' => $this->psCheckout->l('in card transactions until your account is fully approved', 'translations'),
                    'accountDeclined' => $this->psCheckout->l('Account declined', 'translations'),
                    'cannotProcessCreditCard' => $this->psCheckout->l('Unfortunately, credit card payments cannot be processed for you at the moment. You will be able to reapply after 90 days. In the meantime, you can still receive payments via PayPal', 'translations'),
                    'accountDeclinedLink' => $this->psCheckout->l('Account declined FAQs', 'translations'),
                    'suspendedAlertTitle' => $this->psCheckout->l('Credit Card availability suspended', 'translations'),
                    'suspendedAlertLabel' => $this->psCheckout->l('Unfortunately, credit card payments cannot be processed for you at the moment. In the meantime, you can still receive payments via PayPal. Please proceed to some actions from your PayPal account, where you should have received a notification. If not, please contact PayPal help center.', 'translations'),
                    'suspendedButton' => $this->psCheckout->l('Manage your account', 'translations'),
                    'revokedAlertTitle' => $this->psCheckout->l('Credit Card availability revoked', 'translations'),
                    'revokedAlertLabel' => $this->psCheckout->l('Unfortunately, you have revoked PrestaShop Checkout permissions. Credit card payments cannot be processed for you. You can still receive payments via PayPal. Please, log out your PayPal account just below and link your account giving permission again. You can contact PayPal help center to have more information about your account.', 'translations'),
                    'revokedButton' => $this->psCheckout->l('Manage your account', 'translations'),
                ],
                'signin' => [
                    'logInWithYourPsAccount' => $this->psCheckout->l('Log in with your PrestaShop Checkout account', 'translations'),
                    'email' => $this->psCheckout->l('Email', 'translations'),
                    'password' => $this->psCheckout->l('Password', 'translations'),
                    'forgotPassword' => $this->psCheckout->l('Forgot password?', 'translations'),
                    'back' => $this->psCheckout->l('Back', 'translations'),
                    'signup' => $this->psCheckout->l('Sign up', 'translations'),
                    'login' => $this->psCheckout->l('Log in', 'translations'),
                ],
                'signup' => [
                    'createYourPsAccount' => $this->psCheckout->l('Create your PrestaShop Checkout account', 'translations'),
                    'email' => $this->psCheckout->l('Email', 'translations'),
                    'password' => $this->psCheckout->l('Password', 'translations'),
                    'termsOfUse' => $this->psCheckout->l('I agree to the ', 'translations'),
                    'termsOfUseLinkText' => $this->psCheckout->l('Terms and Conditions of Use of PrestaShop Checkout', 'translations'),
                    'termsOfUseLink' => $linkTranslations->getCheckoutDataPolicyLink(),
                    'termsOfUseError' => $this->psCheckout->l('I accept the terms of use', 'translations'),
                    'mentionsTermsText' => $this->psCheckout->l('By submitting this form, I agree that the data provided may be collected by PrestaShop S.A to create your PrestaShop Checkout account. By creating your account, you will receive commercial prospecting from PrestaShop', 'Translations'),
                    'mentionsTermsLinkTextPart1' => $this->psCheckout->l('except opposition here', 'translations'),
                    'mentionsTermsLinkTextPart2' => $this->psCheckout->l('Learn more about managing your data and rights.', 'translations'),
                    'mentionsTermsLinkPart2' => $linkTranslations->getCheckoutDataPolicyLink(),
                    'back' => $this->psCheckout->l('Back', 'translations'),
                    'signIn' => $this->psCheckout->l('Sign in', 'translations'),
                    'createAccount' => $this->psCheckout->l('Create account', 'translations'),
                ],
                'resetPassword' => [
                    'resetPassword' => $this->psCheckout->l('Reset password', 'translations'),
                    'youGotEmail' => $this->psCheckout->l('You’ve got an email.', 'translations'),
                    'sendEmail' => $this->psCheckout->l('We sent you an email with instructions to reset your password. Please check your inbox.', 'translations'),
                    'sendLink' => $this->psCheckout->l('We will send you a link to reset your password.', 'translations'),
                    'email' => $this->psCheckout->l('Email', 'translations'),
                    'goBackToLogin' => $this->psCheckout->l('Go back to login', 'translations'),
                    'reset' => $this->psCheckout->l('Reset', 'translations'),
                ],
                'customize' => [
                    'customThemeWarningMessage1' => $this->psCheckout->l('All custom themes might not be fully compatible. To avoid potential integration issues on payment page,', 'translations'),
                    'customThemeWarningMessage2' => $this->psCheckout->l('please check our list of customizable settings', 'translations'),
                    'payLater' => [
                        'title' => $this->psCheckout->l('Pay in installments with PayPal Pay Later', 'translations'),
                        'eligibilityWarning' => $this->psCheckout->l('This feature availability is based on eligibility criteria.', 'translations'),
                        'eligibilityLink' => $this->psCheckout->l('Read more', 'translations'),
                        'adviceContent' => $this->psCheckout->l('Displaying this message has shown a better conversion rate and a raise of Average Order Value.', 'translations'),
                        'homePage' => $this->psCheckout->l('Homepage', 'translations'),
                        'categoryPage' => $this->psCheckout->l('Category page', 'translations'),
                        'message' => [
                            'title' => $this->psCheckout->l('Choose page location for messages', 'translations'),
                        ],
                        'banner' => [
                            'title' => $this->psCheckout->l('Choose page location for banners', 'translations'),
                        ],
                        'button' => [
                            'title' => $this->psCheckout->l('Choose page location for buttons', 'translations'),
                        ],
                    ],
                ],
            ],
            'firebase' => [
                'error' => [
                    'emailExists' => $this->psCheckout->l('Email already exist.', 'translations'),
                    'missingEmail' => $this->psCheckout->l('The email is missing.', 'translations'),
                    'missingPassword' => $this->psCheckout->l('The password is missing.', 'translations'),
                    'invalidEmail' => $this->psCheckout->l('The email address is badly formatted.', 'translations'),
                    'invalidPassword' => $this->psCheckout->l('The password is invalid.', 'translations'),
                    'emailNotFound' => $this->psCheckout->l('The email is not found.', 'translations'),
                    'defaultError' => $this->psCheckout->l('Error, try later.', 'translations'),
                ],
            ],
            'banner' => [
                'paypalStatus' => [
                    'buttonSuccess' => $this->psCheckout->l('Thank you, close this message', 'translations'),
                    'buttonLegal' => $this->psCheckout->l('Send my legal documents now', 'translations'),
                    'allSet' => $this->psCheckout->l('You\'re all set !', 'translations'),
                    'congrats' => $this->psCheckout->l('Congrats ! You can start selling online now.', 'translations'),
                    'waitingFinalApprove' => $this->psCheckout->l('As soon as PayPal gets all your documents, you\'ll have to wait 48h for final approval.', 'translations'),
                    'oneMoreThing' => $this->psCheckout->l('One more thing : send documents to be fully approved by PayPal', 'translations'),
                    'psAccountConnected' => $this->psCheckout->l('Connect your PrestaShop account', 'translations'),
                    'paypalAccountConnected' => $this->psCheckout->l('Link your PayPal account', 'translations'),
                    'legalDocumentsSent' => $this->psCheckout->l('Send your legal documents to PayPal : ', 'translations'),
                    'upTo' => $this->psCheckout->l('in the meantime, you can sell now up to 500$ in card transactions.', 'translations'),
                    'onlyCC' => $this->psCheckout->l('in the meantime, you will only accept credit cards payments thought the PayPal branded credit card fields', 'translations'),
                    'confirmation' => $this->psCheckout->l('We have received the confirmation from PayPal. You can now process all card transactions with no limits.', 'translations'),
                ],
                'paypalIncompatibleCountry' => [
                    'title' => $this->psCheckout->l('PrestaShop Checkout transactions won\'t work in some of your configured countries, but there is a solution !', 'translations'),
                    'content' => $this->psCheckout->l('Please upgrade your settings for :', 'translations'),
                    'changeCodes' => $this->psCheckout->l('Change countries ISO Codes', 'translations'),
                    'changeActivation' => $this->psCheckout->l('Change countries activation for this payment module', 'translations'),
                    'more' => $this->psCheckout->l('Know more about compliant ISO Codes', 'translations'),
                ],
                'paypalIncompatibleCurrency' => [
                    'title' => $this->psCheckout->l('PrestaShop Checkout transactions won\'t work in some of your configured currencies, but there is a solution !', 'translations'),
                    'content' => $this->psCheckout->l('Please upgrade your settings for :', 'translations'),
                    'changeCodes' => $this->psCheckout->l('Change currencies ISO Codes', 'translations'),
                    'changeActivation' => $this->psCheckout->l('Change currencies activation for this payment module', 'translations'),
                    'more' => $this->psCheckout->l('Know more about compliant ISO Codes', 'translations'),
                ],
                'paypalValueProposition' => [
                    'titleFees' => $this->psCheckout->l('Competitive and transparent fees', 'translations'),
                    'titlePaymentMethods' => $this->psCheckout->l('10 payment methods included all-in-one', 'translations'),
                    'titleConversions' => $this->psCheckout->l('No redirection when paying by credit card', 'translations'),
                    'fees' => [
                        'title' => $this->psCheckout->l('Starting from', 'translations'),
                        'subtitle' => $this->psCheckout->l('per transaction depending on your country. ', 'translations'),
                        'linkLabel' => $this->psCheckout->l('Know more', 'translations'),
                        'linkHref' => $this->psCheckout->l('https://www.prestashop.com/en/prestashop-checkout', 'translations'),
                        'popup' => $this->psCheckout->l('For more informations about your fees please visit this page:', 'translations'),
                        'popupLinkLabel' => $this->psCheckout->l('PrestaShop Checkout 2.0', 'translations'),
                    ],
                    'conversions' => [
                        'title' => $this->psCheckout->l('Higher conversions', 'translations'),
                    ],
                ],
            ],
            'panel' => [
                'accounts' => [
                    'activateAllPayment' => $this->psCheckout->l('You need to connect to both PrestaShop and PayPal accounts to activate all payment methods', 'translations'),
                    'paypal' => [
                        'title' => $this->psCheckout->l('PayPal account', 'translations'),
                        'activate' => $this->psCheckout->l('Log in or sign up to PayPal', 'translations'),
                        'isLinked' => $this->psCheckout->l('Your PrestaShop account is linked to your PayPal account', 'translations'),
                        'merchantId' => $this->psCheckout->l('(merchant ID {0})', 'translations'),
                        'useAnotherAccount' => $this->psCheckout->l('Use another account', 'translations'),
                        'linkToPaypal' => $this->psCheckout->l('Link to PayPal account', 'translations'),
                        'linkToPsCheckoutFirst' => $this->psCheckout->l('Link to PrestaShop Checkout first', 'translations'),
                        'loading' => $this->psCheckout->l('Loading', 'translations'),
                        'onboardingLinkError' => $this->psCheckout->l('An error occured, the PayPal sign up or login window can\'t be opened. Please wait a bit and click again.', 'translations'),
                    ],
                    'checkout' => [
                        'title' => $this->psCheckout->l('PrestaShop Checkout account', 'translations'),
                        'connectedWith' => $this->psCheckout->l('You are now logged in with your', 'translations'),
                        'account' => $this->psCheckout->l('account', 'translations'),
                        'createNewAccount' => $this->psCheckout->l('Sign in or login to provide every payment method to your customer.', 'translations'),
                        'logIn' => $this->psCheckout->l('Log in', 'translations'),
                        'createAccount' => $this->psCheckout->l('Sign up', 'translations'),
                        'logOut' => $this->psCheckout->l('Log out', 'translations'),
                        'titleLogout' => $this->psCheckout->l('Are you sure you want to logout ?', 'translations'),
                        'descriptionLogout' => $this->psCheckout->l('Logging out will deactivate all payment methods. You will no longer be able to receive payments with PrestaShop Checkout.', 'translations'),
                        'cancel' => $this->psCheckout->l('Cancel', 'translations'),
                    ],
                ],
                'psx-form' => [
                    'additionalDetails' => $this->psCheckout->l('Additional Details', 'translations'),
                    'fillUp' => $this->psCheckout->l('Fill out the form to complete registration:', 'translations'),
                    'personalInformation' => $this->psCheckout->l('Personal information', 'translations'),
                    'genderMr' => $this->psCheckout->l('Mr', 'translations'),
                    'genderMrs' => $this->psCheckout->l('Mrs', 'translations'),
                    'firstName' => $this->psCheckout->l('First name', 'translations'),
                    'language' => $this->psCheckout->l('Language', 'translations'),
                    'lastName' => $this->psCheckout->l('Last name', 'translations'),
                    'qualification' => $this->psCheckout->l('Are you', 'translations'),
                    'merchant' => $this->psCheckout->l('A merchant', 'translations'),
                    'agency' => $this->psCheckout->l('An agency', 'translations'),
                    'freelancer' => $this->psCheckout->l('A freelancer', 'translations'),
                    'billingAddress' => $this->psCheckout->l('Billing address', 'translations'),
                    'storeName' => $this->psCheckout->l('Store name', 'translations'),
                    'address' => $this->psCheckout->l('Address', 'translations'),
                    'postCode' => $this->psCheckout->l('Postcode', 'translations'),
                    'town' => $this->psCheckout->l('Town', 'translations'),
                    'country' => $this->psCheckout->l('Country', 'translations'),
                    'state' => $this->psCheckout->l('State', 'translations'),
                    'businessPhone' => $this->psCheckout->l('Business phone', 'translations'),
                    'businessType' => $this->psCheckout->l('Business type', 'translations'),
                    'businessInformation' => $this->psCheckout->l('Business information', 'translations'),
                    'website' => $this->psCheckout->l('Website', 'translations'),
                    'companyTurnover' => $this->psCheckout->l('Company estimated monthly turnover', 'translations'),
                    'businessCategory' => $this->psCheckout->l('Business category', 'translations'),
                    'businessSubCategory' => $this->psCheckout->l('Business subcategory', 'translations'),
                    'optional' => $this->psCheckout->l('Optional', 'translations'),
                    'continue' => $this->psCheckout->l('Continue', 'translations'),
                    'back' => $this->psCheckout->l('Back', 'translations'),
                    'errors' => $this->psCheckout->l('Errors', 'translations'),
                    'privacyTextPart1' => $this->psCheckout->l('By submitting this form, I agree that the data provided may be collected by PrestaShop S.A to permit (i) the use of our services (ii) to improve your customer experience. Your data can be transmitted to our partner Paypal if you do not already have an account.', 'translations'),
                    'privacyTextPart2' => $this->psCheckout->l('Learn more about managing your data and rights.', 'translations'),
                    'privacyLink' => $linkTranslations->getCheckoutDataPolicyLink(),
                ],
                'active-payment' => [
                    'activePaymentMethods' => $this->psCheckout->l('Activate payment methods', 'translations'),
                    'changeOrder' => $this->psCheckout->l('Change payment methods order', 'translations'),
                    'enabled' => $this->psCheckout->l('Enabled', 'translations'),
                    'disabled' => $this->psCheckout->l('Disabled', 'translations'),
                    'available' => $this->psCheckout->l('Available', 'translations'),
                    'notAvailable' => $this->psCheckout->l('Not available', 'translations'),
                    'restricted' => $this->psCheckout->l('Restricted', 'translations'),
                    'creditCard' => $this->psCheckout->l('Credit card', 'translations'),
                    'paypal' => $this->psCheckout->l('PayPal', 'translations'),
                    'availableIn' => $this->psCheckout->l('available in :', 'translations'),
                    'allCountries' => $this->psCheckout->l('All countries', 'translations'),
                    'localPaymentMethods' => $this->psCheckout->l('Local payment methods', 'translations'),
                    'tipsTitle' => $this->psCheckout->l('TIPS', 'translations'),
                    'tipsContent' => $this->psCheckout->l('Boost your conversion rate by displaying PayPal as the first choice in the list of payment methods', 'translations'),
                ],
                'payment-acceptance' => [
                    'paymentMethod' => $this->psCheckout->l('Payment method', 'translations'),
                    'availability' => $this->psCheckout->l('Availability', 'translations'),
                    'activationStatus' => $this->psCheckout->l('Activation status', 'translations'),
                    'paymentAcceptanceTitle' => $this->psCheckout->l('Payment methods acceptance', 'translations'),
                    'creditCardsLabel' => $this->psCheckout->l('Credit and Debit Cards', 'translations'),
                    'tips' => $this->psCheckout->l('Tips', 'translations'),
                    'alertInfo' => $this->psCheckout->l('To test your payment method you can make a real transaction (prefer small amount), and once you have observed the money on your account, make a refund on the corresponding order page. Warning, you will not recover the fees.', 'translations'),
                ],
                'payment-mode' => [
                    'title' => $this->psCheckout->l('Payment methods activation', 'translations'),
                    'paymentAction' => $this->psCheckout->l('Transaction type', 'translations'),
                    'capture' => $this->psCheckout->l('Direct Sale', 'translations'),
                    'authorize' => $this->psCheckout->l('Capture at shipping', 'translations'),
                    'helpBoxPaymentMode' => $this->psCheckout->l('Authorize process holds all payments on customers’ account. Mark the order as « Shipped » or « Payment accepted » to capture payments. Local Payment Methods are not compatible with Authorize process.', 'translations'),
                    'infoAlertText' => $this->psCheckout->l('We recommend « Capture at shipping » if you are a lean manufacturer or a craft products seller', 'translations'),
                    'environment' => $this->psCheckout->l('Environment', 'translations'),
                    'sandboxMode' => $this->psCheckout->l('Test mode', 'translations'),
                    'useSandboxMode' => $this->psCheckout->l('Switch to test mode?', 'translations'),
                    'tipSandboxMode' => $this->psCheckout->l('Note that you cannot collect payments with test mode', 'translations'),
                    'productionMode' => $this->psCheckout->l('Production mode', 'translations'),
                    'useProductionMode' => $this->psCheckout->l('Use production mode', 'translations'),
                    'tipProductionMode' => $this->psCheckout->l('Production mode enables you to collect your payments.', 'translations'),
                ],
                'payment-method-activation' => [
                    'title' => $this->psCheckout->l('Alternative Credit Card Fields activation', 'translations'),
                    'label' => $this->psCheckout->l('PayPal Branded Credit Card Fields', 'translations'),
                    'disable' => $this->psCheckout->l('You can choose the type of credit card fields only if Credit card is activated in « Customize checkout experience » tab.', 'translations'),
                    'popover-difference-question' => $this->psCheckout->l('What is the difference between Integrated Credit Card fields and PayPal branded Credit Card Fields ?', 'translations'),
                    'popover-when-question' => $this->psCheckout->l('When to use PayPal branded Credit Card fields ?', 'translations'),
                    'popover-difference-answer-begin' => $this->psCheckout->l('Integrated Credit Card fields provide the best payment experience you can find in PrestaShop. Well integrated in your checkout process, not branded, with the fewest number of fields, and lowest fee rates (see them on ', 'translations'),
                    'popover-difference-answer-end' => $this->psCheckout->l(' ) : we highly recommend to use these ones, by default. But you need PayPal full approval for accepting Credit Cards payment with the fields. You can see the status of this approval in the ', 'translations'),
                    'popover-when-answer' => $this->psCheckout->l('If approval is pending or issues are encountered with the Integrated fields, you can activate these fields as a backup, only if Integrated fields are not available or deactivated. The fees are the same as PayPal payment method.', 'translations'),
                    'integrated-credit-card-fields' => $this->psCheckout->l('Integrated Credit Card Fields', 'translations'),
                    'paypal-branded-credit-card-fields' => $this->psCheckout->l('PayPal branded Credit Card Fields', 'translations'),
                ],
                'express-checkout' => [
                    'title' => $this->psCheckout->l('Define PayPal express checkout flow', 'translations'),
                    'pageLocation' => $this->psCheckout->l('Choose page location', 'translations'),
                    'orderPage' => $this->psCheckout->l('Order summary page', 'translations'),
                    'checkoutPage' => $this->psCheckout->l('Sign up on order page', 'translations'),
                    'productPage' => $this->psCheckout->l('Product page', 'translations'),
                    'recommended' => $this->psCheckout->l('Recommended', 'translations'),
                    'shippingCost' => $this->psCheckout->l('Shipping costs, if any, will be estimated in basket total. Delivery method selected by default will be the one set in first position on Carriers page.', 'translations'),
                    'alertTitle' => $this->psCheckout->l('TIPS', 'translations'),
                    'alertContent' => $this->psCheckout->l('Express Checkout Shortcut allows merging account creation and payment, to make your customers purchases effortless.', 'translations'),
                ],
                'button-customization' => [
                    'title' => $this->psCheckout->l('Design smart payment buttons', 'translations'),
                    'shape' => [
                        'title' => $this->psCheckout->l('Adjust shape for all buttons', 'translations'),
                        'select' => $this->psCheckout->l('Select the shape', 'translations'),
                        'pill' => $this->psCheckout->l('Pill', 'translations'),
                        'rect' => $this->psCheckout->l('Rectangle', 'translations'),
                    ],
                    'customize' => [
                        'title' => $this->psCheckout->l('Customize PayPal button', 'translations'),
                        'label' => [
                            'select' => $this->psCheckout->l('Choose label', 'translations'),
                            'pay' => $this->psCheckout->l('Pay with', 'translations'),
                            'checkout' => $this->psCheckout->l('Checkout', 'translations'),
                            'buynow' => $this->psCheckout->l('Buy Now', 'translations'),
                        ],
                        'color' => [
                            'select' => $this->psCheckout->l('Select background color', 'translations'),
                            'gold' => $this->psCheckout->l('Gold', 'translations'),
                            'blue' => $this->psCheckout->l('Blue', 'translations'),
                            'silver' => $this->psCheckout->l('Silver', 'translations'),
                            'white' => $this->psCheckout->l('White', 'translations'),
                            'black' => $this->psCheckout->l('Black', 'translations'),
                        ],
                        'tips' => [
                            'title' => $this->psCheckout->l('TIPS', 'translations'),
                            'content' => $this->psCheckout->l('Gold version shows better results on conversion rate', 'translations'),
                        ],
                        'save' => $this->psCheckout->l('Save', 'translations'),
                        'savedConfiguration' => $this->psCheckout->l('Button configuration saved with success !', 'translations'),
                    ],
                    'preview' => [
                        'title' => $this->psCheckout->l('Preview', 'translations'),
                        'paypal-button' => $this->psCheckout->l('PayPal button', 'translations'),
                        'local-payment-buttons' => $this->psCheckout->l('Local payment buttons', 'translations'),
                        'notice' => $this->psCheckout->l('As for local payment methods, buttons will be displayed according to the purchase context: country, amount, device, etc.', 'translations'),
                    ],
                ],
                'help' => [
                    'faq' => $this->psCheckout->l('FAQ', 'translations'),
                    'title' => $this->psCheckout->l('Help for PrestaShop Checkout', 'translations'),
                    'allowsYou' => $this->psCheckout->l('This module allows you to:', 'translations'),
                    'tip1' => $this->psCheckout->l('Connect your PrestaShop Checkout account and link your PayPal Account or create one if needed', 'translations'),
                    'tip2' => $this->psCheckout->l('Offer the widest range of payment methods: cards, PayPal, etc...', 'translations'),
                    'tip3' => $this->psCheckout->l('Benefit from all PayPal expertise and advantages', 'translations'),
                    'tip4' => $this->psCheckout->l('Give access to relevant local payment methods for customers around the globe', 'translations'),
                    'couldntFindAnswer' => $this->psCheckout->l('Couldn\'t find any answer to your question?', 'translations'),
                    'contactUs' => $this->psCheckout->l('Contact us', 'translations'),
                    'needHelp' => $this->psCheckout->l('Need help? Find here the documentation of this module', 'translations'),
                    'downloadDoc' => $this->psCheckout->l('Download PDF', 'translations'),
                    'noFaqAvailable' => $this->psCheckout->l('No faq available. Try later.', 'translations'),
                ],
            ],
            'account-settings-deeplink' => [
                'fraud-tool' => [
                    'title' => $this->psCheckout->l('Limit your fraud rate', 'translations'),
                    'description' => $this->psCheckout->l('PayPal algorithms automatically limit your fraud rate. There is a complete tool on PayPal to set specific rules and drive your performance concerning fraud and chargeback costs.', 'translations'),
                    'link-title' => $this->psCheckout->l('Setup fraud tool', 'translations'),
                    'icon-title' => $this->psCheckout->l('Fraud tool', 'translations'),
                ],
                'bank-account' => [
                    'title' => $this->psCheckout->l('Adjust your bank account', 'translations'),
                    'description' => $this->psCheckout->l('Within your PayPal account, you can add a bank account to be beneficiary of your money transfer.', 'translations'),
                    'link-title' => $this->psCheckout->l('Manage bank account', 'translations'),
                    'icon-title' => $this->psCheckout->l('Bank account', 'translations'),
                ],
                'currencies' => [
                    'title' => $this->psCheckout->l('Match currencies', 'translations'),
                    'description' => $this->psCheckout->l('You can manage the currencies of your PayPal account. Ideally, make them match with the available currencies of your store.', 'translations'),
                    'link-title' => $this->psCheckout->l('Manage currencies', 'translations'),
                    'icon-title' => $this->psCheckout->l('Currencies', 'translations'),
                ],
                'conversion-rules' => [
                    'title' => $this->psCheckout->l('Define the currency conversion rules', 'translations'),
                    'description' => $this->psCheckout->l('Let\'s choose the conversion rules for any transaction in a currency other than those activated on your account, should they be automatically converted or request your validation.', 'translations'),
                    'link-title' => $this->psCheckout->l('Manage conversion rules', 'translations'),
                    'icon-title' => $this->psCheckout->l('Conversion rules', 'translations'),
                ],
                'soft-descriptor' => [
                    'title' => $this->psCheckout->l('Configure your bank statements description', 'translations'),
                    'description' => $this->psCheckout->l('You can choose the short or long description that appears on your customer\'s bank statements. Let\'s make them sure to understand their transaction!', 'translations'),
                    'link-title' => $this->psCheckout->l('Set up description', 'translations'),
                    'icon-title' => $this->psCheckout->l('Details for Bank statements', 'translations'),
                ],
            ],
            'block' => [
                'reassurance' => [
                    'title' => $this->psCheckout->l('One module, all payments methods.', 'translations'),
                    'label1' => $this->psCheckout->l('Offer the widest range of payment methods: cards, PayPal, etc.', 'translations'),
                    'label2' => $this->psCheckout->l('Benefit from all PayPal expertise and advantages', 'translations'),
                    'label3' => $this->psCheckout->l('Give access to relevant local payment methods for customers around the globe', 'translations'),
                    'learnMore' => $this->psCheckout->l('Learn more', 'translations'),
                ],
                'feature-incoming' => [
                    'text' => $this->psCheckout->l('Pay in 4x, save credit cards, authorize mode, send payments direct link... and more to come! Let us know what you would love to be added to this module: any new feature or behavior improvement!', 'translations'),
                    'submitIdea' => $this->psCheckout->l('Submit idea', 'translations'),
                ],
                'dispute' => [
                    'pendingDispute' => '{disputeCount} ' . $this->psCheckout->l('pending dispute(s)', 'translations'),
                    'goToDispute' => $this->psCheckout->l('Go to the dispute management platform', 'translations'),
                ],
                'payment-status' => [
                    'live' => $this->psCheckout->l('Live', 'translations'),
                    'approvalPending' => $this->psCheckout->l('Approval pending', 'translations'),
                    'limited' => $this->psCheckout->l('Limited to $500', 'translations'),
                    'denied' => $this->psCheckout->l('Account declined', 'translations'),
                    'disabled' => $this->psCheckout->l('Disabled', 'translations'),
                    'revoked' => $this->psCheckout->l('Revoked', 'translations'),
                    'suspended' => $this->psCheckout->l('Suspended', 'translations'),
                    'paypalLabel' => $this->psCheckout->l('Accept payments through PayPal buttons on your checkout page.', 'translations'),
                    'paypalLabelEmailNotValid' => $this->psCheckout->l('Your account needs to be validated to accept PayPal payments. Please check your inbox for any email confirmation.', 'translations'),
                    'creditCardLabelLimited' => $this->psCheckout->l('You can process a limited amount in card transactions.', 'translations'),
                    'creditCardLabelSuspended' => $this->psCheckout->l('The capability can no longer be used, but there are remediation steps to regain access to the corresponding functionality.', 'translations'),
                    'creditCardLabelRevoked' => $this->psCheckout->l('The capability can no longer be used and there are no remediation steps available to regain the functionality.', 'translations'),
                    'creditCardLabelDenied' => $this->psCheckout->l('We cannot process credit card payments for you at the moment.', 'translations'),
                    'creditCardLabelLive' => $this->psCheckout->l('Process unlimited card payments. You can accept either credit or debit card.', 'translations'),
                    'creditCardLabelPending' => $this->psCheckout->l('Your account needs further checks to accept Credit and Debit Cards payment.', 'translations'),
                ],
                'rounding-banner' => [
                    'title' => $this->psCheckout->l('Roundings settings to change', 'translations'),
                    'content' => $this->psCheckout->l('Be careful, your roundings settings are not fully compatible with PrestaShop Checkout transaction processing. Some of the transactions could fail. But it is easy, your setting Round mode and Round type should be set on « Round up away from zero, when it is half way there » and « Round on each item » or click on the button bellow to make it automatically !', 'translations'),
                    'button' => $this->psCheckout->l('Change rounding settings', 'translations'),
                    'confirmationTitle' => $this->psCheckout->l('Settings updated !', 'translations'),
                    'confirmationLabel' => $this->psCheckout->l('Your rounding settings are now fully compatible', 'translations'),
                ],
                'reporting' => [
                    'pending' => $this->psCheckout->l('pending', 'translations'),
                    'title' => $this->psCheckout->l('All transactions', 'translations'),
                    'subTitle1' => $this->psCheckout->l('pending transaction(s)', 'translations'),
                    'subTitle2' => $this->psCheckout->l('transaction(s)', 'translations'),
                    'label' => $this->psCheckout->l('See below the transactions processed with PrestaShop Checkout, limited to the last 1000 to load them faster.', 'translations'),
                    'subtitleLinkLabel' => $this->psCheckout->l('See the full list of transactions on your PayPal account', 'translations'),
                    'goToPaypal' => $this->psCheckout->l('Go to PayPal', 'translations'),
                    'goToTransaction' => $this->psCheckout->l('Go to PayPal', 'translations'),
                    'orderPendingTableTitle' => $this->psCheckout->l('Pending transaction', 'translations'),
                    'transactionTableTitle' => $this->psCheckout->l('All transactions', 'translations'),
                    'type' => [
                        'payment' => $this->psCheckout->l('Payment', 'translations'),
                        'refund' => $this->psCheckout->l('Refund', 'translations'),
                    ],
                    'column' => [
                        'date' => $this->psCheckout->l('Date', 'translations'),
                        'orderId' => $this->psCheckout->l('Order Id', 'translations'),
                        'customer' => $this->psCheckout->l('Customer', 'translations'),
                        'type' => $this->psCheckout->l('Type', 'translations'),
                        'beforeCommission' => $this->psCheckout->l('Before commission', 'translations'),
                        'commission' => $this->psCheckout->l('Commission', 'translations'),
                        'total' => $this->psCheckout->l('Total', 'translations'),
                        'actions' => $this->psCheckout->l('Actions', 'translations'),
                    ],
                ],
            ],
            'paypal' => [
                'order' => [
                    'status' => [
                        'CREATED' => $this->psCheckout->l('Created', 'translations'),
                        'SAVED' => $this->psCheckout->l('Saved', 'translations'),
                        'APPROVED' => $this->psCheckout->l('Approved', 'translations'),
                        'PENDING_APPROVAL' => $this->psCheckout->l('Pending approval', 'translations'),
                        'VOIDED' => $this->psCheckout->l('Voided', 'translations'),
                        'COMPLETED' => $this->psCheckout->l('Completed', 'translations'),
                        'PAYER_ACTION_REQUIRED' => $this->psCheckout->l('Payer action required', 'translations'),
                        'PARTIALLY_COMPLETED' => $this->psCheckout->l('Partially completed', 'translations'),
                    ],
                ],
                'capture' => [
                    'status' => [
                        'COMPLETED' => $this->psCheckout->l('Completed', 'translations'),
                        'DECLINED' => $this->psCheckout->l('Declined', 'translations'),
                        'PARTIALLY_REFUNDED' => $this->psCheckout->l('Partially refunded', 'translations'),
                        'PENDING' => $this->psCheckout->l('Pending', 'translations'),
                        'REFUNDED' => $this->psCheckout->l('Refunded', 'translations'),
                        'FAILED' => $this->psCheckout->l('Failed', 'translations'),
                    ],
                ],
            ],
            'order' => [
                'summary' => [
                    'blockTitle' => $this->psCheckout->l('Payment gateway information', 'translations'),
                    'notificationFailed' => $this->psCheckout->l('Your payment has been declined by our payment gateway, please contact us via the link below.', 'translations'),
                    'notificationPendingApproval' => $this->psCheckout->l('Your payment needs to be approved, please click the button below.', 'translations'),
                    'notificationPayerActionRequired' => $this->psCheckout->l('Your payment needs to be authenticated, please click the button below.', 'translations'),
                    'fundingSource' => $this->psCheckout->l('Funding source', 'translations'),
                    'orderIdentifier' => $this->psCheckout->l('Order identifier', 'translations'),
                    'orderStatus' => $this->psCheckout->l('Order status', 'translations'),
                    'transactionIdentifier' => $this->psCheckout->l('Transaction identifier', 'translations'),
                    'transactionStatus' => $this->psCheckout->l('Transaction status', 'translations'),
                    'amountPaid' => $this->psCheckout->l('Amount paid', 'translations'),
                    'buttonApprove' => $this->psCheckout->l('Approve payment', 'translations'),
                    'buttonPayerAction' => $this->psCheckout->l('Authenticate payment', 'translations'),
                    'externalRedirection' => $this->psCheckout->l('You will be redirected to an external secured page of our payment gateway.', 'translations'),
                    'contactLink' => $this->psCheckout->l('If you have any question, please contact us.', 'translations'),
                    'paymentMethodStatus' => $this->psCheckout->l('Payment method status', 'translations'),
                    'paymentTokenSaved' => $this->psCheckout->l('was saved for future purchases', 'translations'),
                    'paymentTokenNotSaved' => $this->psCheckout->l('was not saved for future purchases', 'translations'),
                ],
            ],
            'google_pay' => [
                'shipping' => $this->psCheckout->l('Shipping', 'translations'),
                'tax' => $this->psCheckout->l('Tax', 'translations'),
                'total' => $this->psCheckout->l('Total', 'translations'),
                'subtotal' => $this->psCheckout->l('Subtotal', 'translations'),
                'handling' => $this->psCheckout->l('Handling', 'translations'),
                'discount' => $this->psCheckout->l('Discount', 'translations'),
            ],
            'apple_pay' => [
                'total' => $this->psCheckout->l('Total', 'translations'),
            ],
        ];

        return $translations;
    }
}
