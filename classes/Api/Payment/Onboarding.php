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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PaymentClient;

/**
 * Handle onbarding request
 */
class Onboarding extends PaymentClient
{
    /**
     * Generate the paypal link to onboard merchant
     *
     * @return string|bool onboarding link
     */
    public function getOnboardingLink($email, $locale)
    {
        $this->setRoute('/payments/onboarding/onboard');

        $response = $this->post([
            'json' => json_encode([
                'url' => $this->getCallBackUrl(),
                'person_details' => [
                    'email_address' => $email,
                ],
                'preferred_language_code' => str_replace('-', '_', $locale),
                'primary_currency_code' => $this->getCurrencyIsoCode(),
            ]),
        ]);

        if (false === isset($response['links']['1']['href'])) {
            return false;
        }

        return $response['links']['1']['href'];
    }

    /**
     * Generate the callback url used by the paypal button
     *
     * @return string callback link
     */
    private function getCallBackUrl()
    {
        return $this->link->getAdminLink('AdminPaypalOnboardingPrestashopCheckout');
    }

    /**
     * Get the iso code of the default currency for the shop
     *
     * @return string iso code
     */
    private function getCurrencyIsoCode()
    {
        $currency = \Currency::getCurrency(\Configuration::get('PS_CURRENCY_DEFAULT'));

        return $currency['iso_code'];
    }
}
