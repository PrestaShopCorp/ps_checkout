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

class OrderStatesTranslations
{
    const STANDARD_ISO_CODE = 'en';
    const PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT = array(
        'en' => 'Waiting for PayPal payment',
    );
    const PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT = array(
        'en' => 'Waiting for Credit Card Payment',
    );
    const PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT = array(
        'en' => 'Waiting for Local Payment Method Payment ',
    );
    const PS_CHECKOUT_STATE_AUTHORIZED = array(
        'en' => 'Authorized. To be captured by merchant. ',
    );

    /**
     * Get the States Translations for the table order_state_lang
     *
     * @return array translation list
     */
    public function getTranslations($isoCode)
    {
        $isoCode = $this->confirmIsoCode($isoCode);

        return array(
            'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' => self::PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT[$isoCode],
            'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' => self::PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT[$isoCode],
            'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' => self::PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT[$isoCode],
            'PS_CHECKOUT_STATE_AUTHORIZED' => self::PS_CHECKOUT_STATE_AUTHORIZED[$isoCode],
        );
    }

    /**
     * Return an ISO which can get a result in the translations arrays
     *
     * @param string $isoCode
     *
     * @return string
     */
    private function confirmIsoCode($isoCode)
    {
        if (!array_key_exists($isoCode, self::PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT) ||
            !array_key_exists($isoCode, self::PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT) ||
            !array_key_exists($isoCode, self::PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT) ||
            !array_key_exists($isoCode, self::PS_CHECKOUT_STATE_AUTHORIZED)) {
            return self::STANDARD_ISO_CODE;
        }

        return (string) $isoCode;
    }
}
