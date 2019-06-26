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
                    'test' => 'test',
                ),
            ),
        );

        return $translations;
    }
}
