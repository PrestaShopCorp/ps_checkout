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
                'authentication' => $this->module->getModuleTranslation('Authentication'),
                'customizeCheckout' => $this->module->getModuleTranslation('Customize checkout experience'),
                'manageActivity' => $this->module->getModuleTranslation('Manage Activity'),
                'advancedSettings' => $this->module->getModuleTranslation('Advanced settings'),
                'help' => $this->module->getModuleTranslation('Help'),
            ),
            'general' => array(
                'save' => $this->module->getModuleTranslation('Save'),
                'testModeOn' => $this->module->getModuleTranslation('Test mode is turned on'),
            ),
            'pages' => array(
                'accounts' => array(
                    'approvalPending' => $this->module->getModuleTranslation('Approval pending'),
                    'waitingEmail' => $this->module->getModuleTranslation('A confirmation email has been sent. Check your inbox and click on the link to activate your account.'),
                    'didntReceiveEmail' => $this->module->getModuleTranslation('No confirmation email?'),
                    'sendEmailAgain' => $this->module->getModuleTranslation('Send it again'),
                    'documentNeeded' => $this->module->getModuleTranslation('Information needed'),
                    'additionalDocumentsNeeded' => $this->module->getModuleTranslation('Additional information is required to complete background check. Please upload the following document(s):'),
                    'photoIds' => $this->module->getModuleTranslation('Photo IDs, such as driving licence, for all beneficial owners'),
                    'uploadFile' => $this->module->getModuleTranslation('Upload file(s)'),
                    'undergoingCheck' => $this->module->getModuleTranslation('Background check is currently undergoing'),
                    'severalDays' => $this->module->getModuleTranslation('It can take several days. If further information is needed, you will be notified.'),
                    'youCanProcess' => $this->module->getModuleTranslation('You can process'),
                    'upTo' => $this->module->getModuleTranslation('up to $500'),
                    'transactionsUntil' => $this->module->getModuleTranslation('in card transactions until your account is fully approved'),
                    'approvalPendingLink' => $this->module->getModuleTranslation('Approval pending FAQs'),
                    'accountDeclined' => $this->module->getModuleTranslation('Account declined'),
                    'cannotProcessCreditCard' => $this->module->getModuleTranslation('Unfortunately, credit card payments cannot be processed for you at the moment. You will be able to reapply after 90 days. In the meantime, you can still receive payments via PayPal'),
                    'accountDeclinedLink' => $this->module->getModuleTranslation('Account declined FAQs'),
                ),
            ),
            'panel' => array(
                'account-list' => array(
                    'accountSettings' => $this->module->getModuleTranslation('Account settings'),
                    'essentialsAccount' => $this->module->getModuleTranslation('PrestaShop Checkout account'),
                    'activateAllPayment' => $this->module->getModuleTranslation('You need to connect to both PrestaShop Checkout and PayPal accounts to activate all payment methods'),
                    'connectedWitdh' => $this->module->getModuleTranslation('You are now logged in with your'),
                    'account' => $this->module->getModuleTranslation('account'),
                    'createNewAccount' => $this->module->getModuleTranslation('Sign in or login to provide every payment method to your customer.'),
                    'createAccount' => $this->module->getModuleTranslation('Sign up'),
                    'logIn' => $this->module->getModuleTranslation('Log in'),
                    'logOut' => $this->module->getModuleTranslation('Log out'),
                    'paypalAccount' => $this->module->getModuleTranslation('PayPal account'),
                    'activatePayment' => $this->module->getModuleTranslation('Log in or sign up to PayPal'),
                    'accountIsLinked' => $this->module->getModuleTranslation('Your PrestaShop Checkout account is linked to your PayPal account'),
                    'linkToPaypal' => $this->module->getModuleTranslation('Link to PayPal account'),
                    'linkToPsCheckoutFirst' => $this->module->getModuleTranslation('Link to PrestaShop Checkout first'),
                    'loading' => $this->module->getModuleTranslation('Loading'),
                    'useAnotherAccount' => $this->module->getModuleTranslation('Use another account'),
                ),
                'active-payment' => array(
                    'activePaymentMethods' => $this->module->getModuleTranslation('Activate payment methods'),
                    'paymentMethods' => $this->module->getModuleTranslation('Payment methods'),
                    'changeOrder' => $this->module->getModuleTranslation('Change order'),
                    'enabled' => $this->module->getModuleTranslation('Enabled'),
                    'disabled' => $this->module->getModuleTranslation('Disabled'),
                    'creditCard' => $this->module->getModuleTranslation('Credit card'),
                    'paypal' => $this->module->getModuleTranslation('PayPal'),
                    'localPaymentMethods' => $this->module->getModuleTranslation('Local payment methods'),
                ),
                'payment-acceptance' => array(
                    'paymentAcceptanceTitle' => $this->module->getModuleTranslation('Payment methods acceptance'),
                    'creditCardsLabel' => $this->module->getModuleTranslation('Credit and Debit Cards'),
                    'tips' => $this->module->getModuleTranslation('Tips'),
                    'alertInfo' => $this->module->getModuleTranslation('To test your payment method you can make a real transaction (prefer small amount), and once you have observed the money on your account, make a refund on the corresponding order page. Warning, you will not recover the fees.'),
                ),
                'payment-mode' => array(
                    'title' => $this->module->getModuleTranslation('Payment methods activation'),
                    'paymentAction' => $this->module->getModuleTranslation('Transaction type'),
                    'capture' => $this->module->getModuleTranslation('Direct Sale'),
                    'authorize' => $this->module->getModuleTranslation('Capture at shipping'),
                    'helpBoxPaymentMode' => $this->module->getModuleTranslation('Authorize process holds all payments on customers’ account. Mark the order as « Shipped » or « Payment accepted » to capture payments. Local Payment Methods are not compatible with Authorize process.'),
                    'infoAlertText' => $this->module->getModuleTranslation('We recommend « Capture at shipping » if you are a lean manufacturer or a craft products seller'),
                    'environment' => $this->module->getModuleTranslation('Environment'),
                    'sandboxMode' => $this->module->getModuleTranslation('Test mode'),
                    'useSandboxMode' => $this->module->getModuleTranslation('Switch to test mode?'),
                    'tipSandboxMode' => $this->module->getModuleTranslation('Note that you cannot collect payments with test mode'),
                    'productionMode' => $this->module->getModuleTranslation('Production mode'),
                    'useProductionMode' => $this->module->getModuleTranslation('Use production mode'),
                    'tipProductionMode' => $this->module->getModuleTranslation('Production mode enables you to collect your payments.'),
                ),
                'help' => array(
                    'title' => $this->module->getModuleTranslation('Help for PrestaShop Checkout'),
                    'allowsYou' => $this->module->getModuleTranslation('This module allows you to:'),
                    'tip1' => $this->module->getModuleTranslation('Connect your PrestaShop Checkout account and link your PayPal Account or create one if needed'),
                    'tip2' => $this->module->getModuleTranslation('Offer the widest range of payment methods: cards, PayPal, etc...'),
                    'tip3' => $this->module->getModuleTranslation('Benefit from all PayPal expertise and advantages'),
                    'tip4' => $this->module->getModuleTranslation('Give access to relevant local payment methods for customers around the globe'),
                    'couldntFindAnswer' => $this->module->getModuleTranslation('Couldn\'t find any answer to your question?'),
                    'contactUs' => $this->module->getModuleTranslation('Contact us'),
                    'needHelp' => $this->module->getModuleTranslation('Need help? Find here the documentation of this module'),
                    'downloadDoc' => $this->module->getModuleTranslation('Download PDF'),
                    'noFaqAvailable' => $this->module->getModuleTranslation('No faq available. Try later.'),
                ),
            ),
            'block' => array(
                'reassurance' => array(
                    'title' => $this->module->getModuleTranslation('PrestaShop Checkout, all-in-one module for your payment options'),
                    'firstTip1' => $this->module->getModuleTranslation('All payment methods'),
                    'firstTip2' => $this->module->getModuleTranslation('accept cards, PayPal and much more.'),
                    'secondTip1' => $this->module->getModuleTranslation('Benefit from all'),
                    'secondTip2' => $this->module->getModuleTranslation('PayPal expertise and advantages'),
                    'secondTip3' => $this->module->getModuleTranslation('(fraud prevention, secure technology, dispute resolution, …)'),
                    'thirdTip1' => $this->module->getModuleTranslation('Offer the most relevant'),
                    'thirdTip2' => $this->module->getModuleTranslation('Local Payment Methods'),
                    'thirdTip3' => $this->module->getModuleTranslation('to customers across the globe.'),
                    'learnMore' => $this->module->getModuleTranslation('Learn more'),
                ),
                'fraud-tool' => array(
                    'title' => $this->module->getModuleTranslation('Fraud tool'),
                    'text' => $this->module->getModuleTranslation('PayPal algorithms automatically limit your fraud rate.
                    If you want to go further in Fraud Management, there is a complete tool on the PayPal platform
                    to set specific rules and drive your performance concerning fraud and chargeback costs.'),
                    'discoverFraudTool' => $this->module->getModuleTranslation('Go further'),
                ),
                'feature-incoming' => array(
                    'text' => $this->module->getModuleTranslation('Checkout customization, transactions list, dispute management ... and more to come!'),
                ),
                'dispute' => array(
                    'pendingDispute' => $this->module->getModuleTranslation('pending dispute(s)'),
                    'goToDispute' => $this->module->getModuleTranslation('Go to the dispute management platform'),
                ),
                'payment-status' => array(
                    'live' => $this->module->getModuleTranslation('Live'),
                    'approvalPending' => $this->module->getModuleTranslation('Approval pending'),
                    'limited' => $this->module->getModuleTranslation('Limited to $500'),
                    'denied' => $this->module->getModuleTranslation('Account declined'),
                    'disabled' => $this->module->getModuleTranslation('Disabled'),
                    'paypalLabel' => $this->module->getModuleTranslation('Accept payments through PayPal buttons on your checkout page.'),
                    'paypalLabelEmailNotValid' => $this->module->getModuleTranslation('Your account needs to be validated to accept PayPal payments. Please check your inbox for any email confirmation.'),
                    'creditCardLabelLimited' => $this->module->getModuleTranslation('You can process a limited amount in card transactions.'),
                    'creditCardLabelDenied' => $this->module->getModuleTranslation('We cannot process credit card payments for you at the moment.'),
                    'creditCardLabelLive' => $this->module->getModuleTranslation('Process unlimited card payments. You can accept either credit or debit card.'),
                    'creditCardLabelPending' => $this->module->getModuleTranslation('Your account needs further checks to accept Credit and Debit Cards payment.'),
                ),
            ),
        );

        return $translations;
    }
}
