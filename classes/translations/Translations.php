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
                'authentication' => $this->module->l('Authentication'),
                'customizeCheckout' => $this->module->l('Customize checkout experience'),
                'manageActivity' => $this->module->l('Manage Activity'),
                'advancedSettings' => $this->module->l('Advanced settings'),
                'help' => $this->module->l('Help'),
            ),
            'general' => array(
                'save' => $this->module->l('Save'),
                'testModeOn' => $this->module->l('Test mode is turned on'),
            ),
            'pages' => array(
                'accounts' => array(
                    'approvalPending' => $this->module->l('Approval pending'),
                    'waitingEmail' => $this->module->l('A confirmation email has been sent. Check your inbox and click on the link to activate your account.'),
                    'didntReceiveEmail' => $this->module->l('No confirmation email?'),
                    'sendEmailAgain' => $this->module->l('Send it again'),
                    'documentNeeded' => $this->module->l('Information needed'),
                    'additionalDocumentsNeeded' => $this->module->l('Additional information is required to complete background check. Please upload the following document(s):'),
                    'photoIds' => $this->module->l('Photo IDs, such as driving licence, for all beneficial owners'),
                    'uploadFile' => $this->module->l('Upload file(s)'),
                    'undergoingCheck' => $this->module->l('Background check is currently undergoing'),
                    'severalDays' => $this->module->l('It can take several days. If further information is needed, you will be notified.'),
                    'youCanProcess' => $this->module->l('You can process'),
                    'upTo' => $this->module->l('up to $500'),
                    'transactionsUntil' => $this->module->l('in card transactions until your account is fully approved'),
                    'approvalPendingLink' => $this->module->l('Approval pending FAQs'),
                    'accountDeclined' => $this->module->l('Account declined'),
                    'cannotProcessCreditCard' => $this->module->l('Unfortunately, credit card payments cannot be processed for you at the moment. You will be able to reapply after 90 days. In the meantime, you can still receive payments via PayPal'),
                    'accountDeclinedLink' => $this->module->l('Account declined FAQs'),
                ),
            ),
            'panel' => array(
                'account-list' => array(
                    'accountSettings' => $this->module->l('Account settings'),
                    'essentialsAccount' => $this->module->l('PrestaShop Checkout account'),
                    'activateAllPayment' => $this->module->l('You need to connect to both PrestaShop Checkout and PayPal accounts to activate all payment methods'),
                    'connectedWitdh' => $this->module->l('You are now logged in with your'),
                    'account' => $this->module->l('account'),
                    'createNewAccount' => $this->module->l('Sign in or login to provide every payment method to your customer.'),
                    'createAccount' => $this->module->l('Sign up'),
                    'or' => $this->module->l('or'),
                    'logIn' => $this->module->l('Log in'),
                    'logOut' => $this->module->l('Log out'),
                    'paypalAccount' => $this->module->l('PayPal account'),
                    'activatePayment' => $this->module->l('Log in or sign up to PayPal'),
                    'accountIsLinked' => $this->module->l('Your PrestaShop Checkout account is linked to your PayPal account'),
                    'linkToPaypal' => $this->module->l('Link to PayPal account'),
                    'linkToPsCheckoutFirst' => $this->module->l('Link to PrestaShop Checkout first'),
                    'loading' => $this->module->l('Loading'),
                    'useAnotherAccount' => $this->module->l('Use another account'),
                ),
                'active-payment' => array(
                    'activePaymentMethods' => $this->module->l('Activate payment methods'),
                    'paymentMethods' => $this->module->l('Payment methods'),
                    'changeOrder' => $this->module->l('Change order'),
                    'enabled' => $this->module->l('Enabled'),
                    'disabled' => $this->module->l('Disabled'),
                    'creditCard' => $this->module->l('Credit card'),
                    'paypal' => $this->module->l('PayPal'),
                    'localPaymentMethods' => $this->module->l('Local payment methods'),
                ),
                'payment-mode' => array(
                    'title' => $this->module->l('Payment methods activation'),
                    'paymentAction' => $this->module->l('Transaction type'),
                    'capture' => $this->module->l('Direct Sale'),
                    'authorize' => $this->module->l('Capture at shipping'),
                    'helpBoxPaymentMode' => $this->module->l('Authorize process holds all payments on customers’ account. Mark the order as « Shipped » or « Payment accepted » to capture payments. Local Payment Methods are not compatible with Authorize process.'),
                    'infoAlertText' => $this->module->l('We recommend « Capture at shipping » if you are a lean manufacturer or a craft products seller'),
                    'environment' => $this->module->l('Environment'),
                    'sandboxMode' => $this->module->l('Test mode'),
                    'useSandboxMode' => $this->module->l('Switch to test mode?'),
                    'tipSandboxMode' => $this->module->l('Note that you cannot collect payments with test mode'),
                    'productionMode' => $this->module->l('Production mode'),
                    'useProductionMode' => $this->module->l('Use production mode'),
                    'tipProductionMode' => $this->module->l('Production mode enables you to collect your payments.'),
                ),
                'help' => array(
                    'title' => $this->module->l('Help for'),
                ),
            ),
            'block' => array(
                'reassurance' => array(
                    'title' => $this->module->l('PrestaShop Checkout, all-in-one module for your payment options'),
                    'firstTip1' => $this->module->l('All payment methods'),
                    'firstTip2' => $this->module->l('accept cards, PayPal and much more.'),
                    'secondTip1' => $this->module->l('Benefit from all'),
                    'secondTip2' => $this->module->l('PayPal expertise and advantages'),
                    'secondTip3' => $this->module->l('(fraud prevention, secure technology, dispute resolution, …)'),
                    'thirdTip1' => $this->module->l('Offer the most relevant'),
                    'thirdTip2' => $this->module->l('Local Payment Methods'),
                    'thirdTip3' => $this->module->l('to customers across the globe.'),
                    'learnMore' => $this->module->l('Learn more'),
                ),
                'fraud-tool' => array(
                    'title' => $this->module->l('Fraud tool'),
                    'text' => $this->module->l('PayPal algorithms automatically limit your fraud rate.
                    If you want to go further in Fraud Management, there is a complete tool on the PayPal platform
                    to set specific rules and drive your performance concerning fraud and chargeback costs.'),
                    'discoverFraudTool' => $this->module->l('Go further'),
                ),
                'feature-incoming' => array(
                    'text' => $this->module->l('Checkout customization, transactions list, dispute management ... and more to come!'),
                ),
                'dispute' => array(
                    'pendingDispute' => $this->module->l('pending dispute(s)'),
                    'goToDispute' => $this->module->l('Go to the dispute management platform'),
                ),
            ),
        );

        return $translations;
    }
}
