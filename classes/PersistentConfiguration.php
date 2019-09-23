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

use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Entity\PsAccount;

/**
 * Not really an entity.
 * Define and manage data regarding paypal account
 */
class PersistentConfiguration
{
    /**
     * Save / update paypal account in database
     *
     * @param PaypalAccount $paypalAccount
     *
     * @return bool
     */
    public function savePaypalAccount(PaypalAccount $paypalAccount)
    {
        return \Configuration::updateValue(PaypalAccount::PS_CHECKOUT_PAYPAL_ID_MERCHANT, $paypalAccount->getMerchantId())
            && \Configuration::updateValue(PaypalAccount::PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT, $paypalAccount->getEmail())
            && \Configuration::updateValue(PaypalAccount::PS_CHECKOUT_PAYPAL_EMAIL_STATUS, $paypalAccount->getEmailIsVerified())
            && \Configuration::updateValue(PaypalAccount::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS, $paypalAccount->getPaypalPaymentStatus())
            && \Configuration::updateValue(PaypalAccount::PS_CHECKOUT_CARD_PAYMENT_STATUS, $paypalAccount->getCardPaymentStatus());
    }

    /**
     * Save / update ps account in database
     *
     * @param PsAccount $psAccount
     *
     * @return bool
     */
    public function savePsAccount(PsAccount $psAccount)
    {
        return \Configuration::updateValue(PsAccount::PS_PSX_FIREBASE_EMAIL, $psAccount->getEmail())
            && \Configuration::updateValue(PsAccount::PS_PSX_FIREBASE_ID_TOKEN, $psAccount->getIdToken())
            && \Configuration::updateValue(PsAccount::PS_PSX_FIREBASE_LOCAL_ID, $psAccount->getLocalId())
            && \Configuration::updateValue(PsAccount::PS_PSX_FIREBASE_REFRESH_TOKEN, $psAccount->getRefreshToken())
            && \Configuration::updateValue(PsAccount::PS_CHECKOUT_PSX_FORM, $psAccount->getPsxForm());
    }
}
