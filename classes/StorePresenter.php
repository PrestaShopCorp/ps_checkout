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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Api\Maasland;

/**
 * Present the store to the vuejs app (vuex)
 */
class StorePresenter
{
    /**
     * @var array
     */
    private $store = null;

    /**
     * @var \Module
     */
    private $module = null;

    public function __construct(\Module $module)
    {
        $this->setModule($module);
    }

    /**
     * Build the store required by vuex
     *
     * @return array store
     */
    public function present()
    {
        $this->setStore(array(
            'firebase' => $this->getFirebaseAccount(),
            'paypal' => $this->getPaypalAccount(),
            'config' => $this->getConfiguration(),
        ));

        return $this->getStore();
    }

    /**
     * Build the configuration module (vuex)
     *
     * @return array
     */
    private function getConfiguration()
    {
        $configuration = array(
            'module' => array(
                'paymentMethods' => $this->getPaymentsMethods(),
                'captureMode' => \Configuration::get('PS_CHECKOUT_INTENT'),
                'paymentMode' => \Configuration::get('PS_CHECKOUT_MODE'),
            ),
        );

        return $configuration;
    }

    /**
     * Get payment methods order
     *
     * @return array payment method
     */
    private function getPaymentsMethods()
    {
        $paymentMethods = \Configuration::get('PS_CHECKOUT_PAYMENT_METHODS_ORDER');

        if (true === empty($paymentMethods)) {
            $paymentMethods = array();

            array_push($paymentMethods, array(
                'name' => 'card',
            ));

            array_push($paymentMethods, array(
                'name' => 'paypal',
            ));
        } else {
            $paymentMethods = json_decode($paymentMethods, true);
        }

        return $paymentMethods;
    }

    /**
     * Generate the paypal onboarding link
     *
     * @return string|bool paypal onboarding link
     */
    private function getPaypalOnboardingLink()
    {
        $merchant = new MerchantRepository();

        if (true === $merchant->onbardingPaypalIsCompleted()) {
            return false;
        }

        $context = \Context::getContext();

        $email = $context->employee->email;
        $language = \Language::getLanguage($context->employee->id_lang);
        $locale = $language['locale'];

        $paypalOnboardingLink = (new Maasland($context->link))->getPaypalOnboardingLink($email, $locale);

        return $paypalOnboardingLink;
    }

    /**
     * Get firebase account detail for the firebase module (vuex)
     *
     * @return array
     */
    private function getFirebaseAccount()
    {
        $idToken = (new FirebaseClient())->getToken();

        $firebaseAccount = array(
            'account' => array(
                'email' => \Configuration::get('PS_CHECKOUT_FIREBASE_EMAIL'),
                'idToken' => $idToken,
                'localId' => \Configuration::get('PS_CHECKOUT_FIREBASE_LOCAL_ID'),
                'refreshToken' => \Configuration::get('PS_CHECKOUT_FIREBASE_REFRESH_TOKEN'),
                'onboardingCompleted' => !empty($idToken),
            ),
        );

        return $firebaseAccount;
    }

    /**
     * Construct the paypal module (vuex)
     *
     * @return array
     */
    private function getPaypalAccount()
    {
        $idMerchant = \Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT');

        $paypalAccount = array(
            'account' => array(
                'idMerchant' => $idMerchant,
                'paypalOnboardingLink' => $this->getPaypalOnboardingLink(),
                'onboardingCompleted' => !empty($idMerchant),
                'emailMerchant' => \Configuration::get('PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT'),
                'emailIsValid' => \Configuration::get('PS_CHECKOUT_PAYPAL_EMAIL_STATUS'),
                'cardIsActive' => \Configuration::get('PS_CHECKOUT_CARD_PAYMENT_STATUS'),
                'paypalIsActive' => \Configuration::get('PS_CHECKOUT_PAYPAL_PAYMENT_STATUS'),
            ),
        );

        return $paypalAccount;
    }

    /**
     * getter for the order
     *
     * @return array
     */
    private function getStore()
    {
        return $this->store;
    }

    /**
     * setter for the store
     *
     * @param array $store
     */
    private function setStore($store)
    {
        $this->store = $store;
    }

    /**
     * setter for module
     *
     * @param \Module
     */
    private function setModule($module)
    {
        $this->module = $module;
    }
}
