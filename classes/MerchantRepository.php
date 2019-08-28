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

/**
 * Get some info about the merchant
 */
class MerchantRepository
{
    /**
     * Get the merchant id for the current onboarded merchant
     *
     * @return string|bool paypal id merchant or false if empty
     */
    public function getMerchantId()
    {
        return \Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT');
    }

    /**
     * Check if the merchant is valid
     *
     * @return bool true if the merchant email is confirmed + if psx/paypal onboarding is completed
     */
    public function merchantIsValid()
    {
        return $this->onbardingPsxIsCompleted()
            && $this->onbardingPaypalIsCompleted()
            && $this->paypalEmailIsValid();
    }

    /**
     * Get the status of the psx onboarding
     *
     * @return bool
     */
    public function onbardingPsxIsCompleted()
    {
        return !empty(\Configuration::get('PS_CHECKOUT_PSX_FORM'));
    }

    /**
     * Get the status of the firebase onboarding
     *
     * @return bool
     */
    public function onbardingFirebaseIsCompleted()
    {
        $idToken = (new FirebaseClient())->getToken();

        return !empty($idToken);
    }

    /**
     * Get the status of the paypal onboarding
     *
     * @return bool
     */
    public function onbardingPaypalIsCompleted()
    {
        return !empty($this->getMerchantId());
    }

    /**
     * Check if the paypal email was confirmed or not
     *
     * @return bool
     */
    public function paypalEmailIsValid()
    {
        return (bool) \Configuration::get('PS_CHECKOUT_PAYPAL_EMAIL_STATUS');
    }

    /**
     * Get if the payment method by hosted fields is enabled for the current merchant
     *
     * @return bool
     */
    public function cardPaymentMethodIsValid()
    {
        $cardStatus = \Configuration::get('PS_CHECKOUT_CARD_PAYMENT_STATUS');

        if ($cardStatus === Merchant::SUBSCRIBED
        || $cardStatus === Merchant::LIMITED) {
            return true;
        }

        return false;
    }

    /**
     * Get if the payment method by paypal is enabled for the current merchant
     *
     * @return bool
     */
    public function paypalPaymentMethodIsValid()
    {
        return (bool) \Configuration::get('PS_CHECKOUT_PAYPAL_PAYMENT_STATUS');
    }
}
