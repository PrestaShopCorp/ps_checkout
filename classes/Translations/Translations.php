<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout\Translations;

class Translations
{
    /**
     * @var \Module
     */
    private $module = null;

    public function __construct(\Module $module)
    {
        $this->module = $module;
    }

    /**
     * Create all tranlations (backoffice)
     *
     * @return array translation list
     */
    public function getTranslations()
    {
        $locale = \Context::getContext()->language->locale;

        $translations[$locale] = array(
            'menu' => array(
                'authentication' => $this->module->l('Authentication', 'translations'),
                'customizeCheckout' => $this->module->l('Customize checkout experience', 'translations'),
                'manageActivity' => $this->module->l('Manage Activity', 'translations'),
                'advancedSettings' => $this->module->l('Advanced settings', 'translations'),
                'help' => $this->module->l('Help', 'translations'),
            ),
            'general' => array(
                'save' => $this->module->l('Save', 'translations'),
                'testModeOn' => $this->module->l('Test mode is turned on', 'translations'),
            ),
            'pages' => array(
                'accounts' => array(
                    'approvalPending' => $this->module->l('Approval pending', 'translations'),
                    'waitingEmail' => $this->module->l('A confirmation email has been sent. Check your inbox and click on the link to activate your account.', 'translations'),
                    'didntReceiveEmail' => $this->module->l('No confirmation email?', 'translations'),
                    'sendEmailAgain' => $this->module->l('Send it again', 'translations'),
                    'documentNeeded' => $this->module->l('Information needed', 'translations'),
                    'additionalDocumentsNeeded' => $this->module->l('Additional information is required to complete background check. Please go on your www.paypal.com account and check the notification bell on the top right to know which documents are needed. It could be:', 'translations'),
                    'photoIds' => $this->module->l('Credit Card information, bank account information and ID card', 'translations'),
                    'uploadFile' => $this->module->l('Upload file(s) on PayPal account', 'translations'),
                    'undergoingCheck' => $this->module->l('Background check is currently undergoing', 'translations'),
                    'severalDays' => $this->module->l('It can take several days. If further information is needed, you will be notified. Please check your emails or your notification bell on the top right of your www.paypal.com account and follow the instructions.', 'translations'),
                    'youCanProcess' => $this->module->l('You can process', 'translations'),
                    'upTo' => $this->module->l('up to $500', 'translations'),
                    'transactionsUntil' => $this->module->l('in card transactions until your account is fully approved', 'translations'),
                    'approvalPendingLink' => $this->module->l('Approval pending FAQs', 'translations'),
                    'accountDeclined' => $this->module->l('Account declined', 'translations'),
                    'cannotProcessCreditCard' => $this->module->l('Unfortunately, credit card payments cannot be processed for you at the moment. You will be able to reapply after 90 days. In the meantime, you can still receive payments via PayPal', 'translations'),
                    'accountDeclinedLink' => $this->module->l('Account declined FAQs', 'translations'),
                ),
            ),
            'panel' => array(
                'account-list' => array(
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
                ),
                'psx-form' => array(
                    'additionalDetails' => $this->module->l('Additional Details', 'translations'),
                    'fillUp' => $this->module->l('Please fill up the following form to complete your PrestaShop Checkout account creation.', 'translations'),
                    'personalInformation' => $this->module->l('Personal information', 'translations'),
                    'genderMr' => $this->module->l('Mr', 'translations'),
                    'genderMrs' => $this->module->l('Mrs', 'translations'),
                    'firstName' => $this->module->l('First name', 'translations'),
                    'nationality' => $this->module->l('Nationnality', 'translations'),
                    'lastName' => $this->module->l('Last name', 'translations'),
                    'billingAddress' => $this->module->l('Billing address', 'translations'),
                    'storeName' => $this->module->l('Store name', 'translations'),
                    'address' => $this->module->l('Address', 'translations'),
                    'postCode' => $this->module->l('Postcode', 'translations'),
                    'town' => $this->module->l('Town', 'translations'),
                    'country' => $this->module->l('Country', 'translations'),
                    'businessPhone' => $this->module->l('Business phone', 'translations'),
                    'businessType' => $this->module->l('Business type', 'translations'),
                    'businessInformation' => $this->module->l('Business information', 'translations'),
                    'website' => $this->module->l('Website', 'translations'),
                    'companySize' => $this->module->l('Company size', 'translations'),
                    'businessCategory' => $this->module->l('Business category', 'translations'),
                    'businessSubCategory' => $this->module->l('Business subcategory', 'translations'),
                    'continue' => $this->module->l('Continue', 'translations'),
                    'errors' => $this->module->l('Errors', 'translations'),
                ),
                'active-payment' => array(
                    'activePaymentMethods' => $this->module->l('Activate payment methods', 'translations'),
                    'paymentMethods' => $this->module->l('Payment methods', 'translations'),
                    'changeOrder' => $this->module->l('Change order', 'translations'),
                    'enabled' => $this->module->l('Enabled', 'translations'),
                    'disabled' => $this->module->l('Disabled', 'translations'),
                    'creditCard' => $this->module->l('Credit card', 'translations'),
                    'paypal' => $this->module->l('PayPal', 'translations'),
                    'localPaymentMethods' => $this->module->l('Local payment methods', 'translations'),
                ),
                'payment-acceptance' => array(
                    'paymentAcceptanceTitle' => $this->module->l('Payment methods acceptance', 'translations'),
                    'creditCardsLabel' => $this->module->l('Credit and Debit Cards', 'translations'),
                    'tips' => $this->module->l('Tips', 'translations'),
                    'alertInfo' => $this->module->l('To test your payment method you can make a real transaction (prefer small amount), and once you have observed the money on your account, make a refund on the corresponding order page. Warning, you will not recover the fees.', 'translations'),
                ),
                'payment-mode' => array(
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
                ),
                'help' => array(
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
                ),
            ),
            'block' => array(
                'reassurance' => array(
                    'title' => $this->module->l('PrestaShop Checkout, all-in-one module for your payment options', 'translations'),
                    'firstTip1' => $this->module->l('All payment methods', 'translations'),
                    'firstTip2' => $this->module->l('accept cards, PayPal and much more.', 'translations'),
                    'secondTip1' => $this->module->l('Benefit from all', 'translations'),
                    'secondTip2' => $this->module->l('PayPal expertise and advantages', 'translations'),
                    'secondTip3' => $this->module->l('(fraud prevention, secure technology, dispute resolution, …)', 'translations'),
                    'thirdTip1' => $this->module->l('Offer the most relevant', 'translations'),
                    'thirdTip2' => $this->module->l('Local Payment Methods', 'translations'),
                    'thirdTip3' => $this->module->l('to customers across the globe.', 'translations'),
                    'learnMore' => $this->module->l('Learn more', 'translations'),
                ),
                'fraud-tool' => array(
                    'title' => $this->module->l('Fraud tool', 'translations'),
                    'text' => $this->module->l('PayPal algorithms automatically limit your fraud rate.
                    If you want to go further in Fraud Management, there is a complete tool on the PayPal platform
                    to set specific rules and drive your performance concerning fraud and chargeback costs.', 'translations'),
                    'discoverFraudTool' => $this->module->l('Go further', 'translations'),
                ),
                'feature-incoming' => array(
                    'text' => $this->module->l('Checkout customization, transactions list, dispute management ... and more to come!', 'translations'),
                ),
                'dispute' => array(
                    'pendingDispute' => $this->module->l('pending dispute(s)', 'translations'),
                    'goToDispute' => $this->module->l('Go to the dispute management platform', 'translations'),
                ),
                'payment-status' => array(
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
                ),
            ),
        );

        return $translations;
    }
}
