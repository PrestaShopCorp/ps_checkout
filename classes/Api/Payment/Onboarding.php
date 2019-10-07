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

use PrestaShop\Module\PrestashopCheckout\PsxDataMatrice\PsxDataMatrice;
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
    public function getOnboardingLink($locale)
    {
        $this->setRoute('/payments/onboarding/onboard');

        $psxFormData = json_decode(\Configuration::get('PS_CHECKOUT_PSX_FORM'), true);

        $payload = [
            'url' => $this->getCallBackUrl(),
            'person_details' => $this->getPersonDetails($psxFormData),
            'business_details' => $this->getBusinessDetails($psxFormData),
            'preferred_language_code' => str_replace('-', '_', $locale),
            'primary_currency_code' => $this->getCurrencyIsoCode(),
        ];

        if (getenv('PLATEFORM') === 'PSREADY') { // if on ready, do not send psx data on the payload
            unset($payload['person_details']['name']);
            unset($payload['business_details']);
        }

        $response = $this->post([
            'json' => json_encode($payload),
        ]);

        if (false === isset($response['links']['1']['href'])) {
            return false;
        }

        return $response['links']['1']['href'];
    }

    /**
     * Generate an array to be used on the Paypal Link
     *
     * @param array $psxFormData
     *
     * @return array
     */
    private function getPersonDetails($psxFormData)
    {
        $nameObj = [
            'email_address' => \Configuration::get('PS_PSX_FIREBASE_EMAIL'),
            'name' => [
                'given_name' => $psxFormData['business_contact_first_name'],
                'surname' => $psxFormData['business_contact_last_name'],
                'prefix' => $psxFormData['business_contact_gender'],
            ],
        ];

        return array_filter($nameObj);
    }

    /**
     * Generate an array to be used on the Paypal Link
     *
     * @param array $psxFormData
     *
     * @return array
     */
    private function getBusinessDetails($psxFormData)
    {
        $nameObj = [
            'business_address' => array_filter([
                'city' => $psxFormData['business_address_city'],
                'country_code' => $psxFormData['business_address_country'],
                'line1' => $psxFormData['business_address_street'],
                'postal_code' => $psxFormData['business_address_zip'],
                'state' => $psxFormData['business_address_state'],
            ]),
            'phone_contacts' => [
                0 => [
                'phone_number_details' => [
                        'country_code' => (string) $psxFormData['business_phone_country'],
                        'national_number' => $psxFormData['business_phone'],
                    ],
                    'phone_type' => 'HOME',
                ],
            ],
            'names' => [
                0 => [
                    'name' => $psxFormData['shop_name'],
                    'type' => 'LEGAL',
                ],
            ],
            'category' => $psxFormData['business_category'],
            'sub_category' => $psxFormData['business_sub_category'],
            'website_urls' => array_filter([
                $psxFormData['business_website'],
            ]),
            'business_type' => 'INDIVIDUAL',
            'average_monthly_volume_range' => (new PsxDataMatrice())->getCompanyEmrToAverageMonthlyVolumeRange($psxFormData['business_company_emr']),
        ];

        return array_filter($nameObj);
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
        $currency = \Currency::getCurrency((int) \Configuration::get('PS_CURRENCY_DEFAULT'));

        return $currency['iso_code'];
    }
}
