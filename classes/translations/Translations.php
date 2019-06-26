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
                'fees' => $this->module->l('Fees'),
                'help' => $this->module->l('Help'),
            ),
            'general' => array(
                'save' => $this->module->l('Save'),
            ),
            'pages' => array(
                'accounts' => array(
                    'approvalPending' => $this->module->l('Approval pending'),
                    'waitingEmail' => $this->module->l('We are waiting for email confirmation… Check your inbox to finalize creation.'),
                    'didntReceiveEmail' => $this->module->l('Didn’t receive any confirmation email?'),
                    'sendEmailAgain' => $this->module->l('Send email again'),
                    'documentNeeded' => $this->module->l('Documents needed'),
                    'additionalDocumentsNeeded' => $this->module->l('We need additional documents to complete our background check. Please prepare the following documents'),
                    'photoIds' => $this->module->l('Photo IDs, such as driving licence, for all beneficial owners'),
                    'uploadFile' => $this->module->l('Upload file'),
                    'undergoingCheck' => $this->module->l('Your case is currently undergoing necessary background check'),
                    'severalDays' => $this->module->l('This can take several days. If further information is needed, you will be notified.'),
                    'youCanProcess' => $this->module->l('You can process'),
                    'upTo' => $this->module->l('up to $500'),
                    'transactionsUntil' => $this->module->l('in card transactions until your account is fully approved to accept card payment.'),
                    'approvalPendingLink' => $this->module->l('Approval pending FAQs'),
                    'accountDeclined' => $this->module->l('Account declined'),
                    'cannotProcessCreditCard' => $this->module->l('We cannot process credit card payments for you at the moment. You can reapply after 90 days, in the meantine you can accept orders via PayPal.'),
                    'accountDeclinedLink' => $this->module->l('Account declined FAQs'),
                ),
            ),
            'panel' => array(
                'account-list' => array(
                    'accountSettings' => $this->module->l('Account settings'),
                    'essentialsAccount' => $this->module->l('PrestaShop Essentials account'),
                    'activateAllPayment' => $this->module->l('Activate all payment methods with your PrestaShop Essentials account.'),
                    'connectedWitdh' => $this->module->l('You are connected with'),
                    'createNewAccount' => $this->module->l('Create a new account or login with your current account.'),
                    'createAccount' => $this->module->l('Create account'),
                    'or' => $this->module->l('or'),
                    'logIn' => $this->module->l('Log in'),
                    'logOut' => $this->module->l('Log out'),
                    'paypalAccount' => $this->module->l('PayPal account'),
                    'activatePayment' => $this->module->l('To activate payment methods link your existing PayPal account or create a new one.'),
                    'accountIsLinked' => $this->module->l('Your PrestaShop Essentials account is linked with this PayPal account.'),
                    'linkToPaypal' => $this->module->l('Link to PayPal account'),
                    'loading' => $this->module->l('Loading'),
                    'useAnotherAccount' => $this->module->l('Use another account'),
                ),
                'active-payment' => array(
                    'activePaymentMethods' => $this->module->l('Active payment methods'),
                    'paymentMethods' => $this->module->l('Payment methods'),
                    'changeOrder' => $this->module->l('Change order'),
                    'enabled' => $this->module->l('Enabled'),
                    'disabled' => $this->module->l('Disabled'),
                    'creditCard' => $this->module->l('Credit card'),
                    'paypal' => $this->module->l('PayPal'),
                    'localPaymentMethods' => $this->module->l('Local payment methods'),
                ),
                'temp' => array(
                    '' => $this->module->l('test'),
                ),
            ),
            'block' => array(
                'reassurance' => array(
                    'title' => $this->module->l('PrestaShop Payments, all-in-one module for your payment options'),
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
                    'text' => $this->module->l('PayPal algorithms limits your fraud rate automatically.
                    If you want to go further in Fraud management, there is a complete
                    tool on PayPal platform to set specific rules and drive your performance
                    concerning fraud and chargeback costs.'),
                    'discoverFraudTool' => $this->module->l('Discover fraud tool'),
                ),
            ),
        );

        return $translations;
    }
}
