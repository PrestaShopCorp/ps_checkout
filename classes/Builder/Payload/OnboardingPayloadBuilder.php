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

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

use PrestaShop\Module\PrestashopCheckout\PsxDataMatrice\PsxDataMatrice;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;

/**
 * Build the payload for getting paypal onboarding link
 */
class OnboardingPayloadBuilder implements PayloadBuilderInterface
{
    /**
     * @var Payload
     */
    private $payload;

    public function __construct()
    {
        $this->reset();
    }

    public function reset()
    {
        $this->payload = new Payload();
    }

    /**
     * Return the result of the payload
     * Clean the builder to be ready to producing a new payload
     *
     * @return Payload
     */
    public function getPayload()
    {
        $payload = $this->payload;
        $this->reset();

        return $payload;
    }

    /**
     * Build the full payload with customer details
     */
    public function buildFullPayload()
    {
        $this->reset();

        $this->buildBaseNode();
        $this->buildFullPersonDetailsNode();
        $this->buildBusinessDetailsNode();
    }

    /**
     * Build payload without customer details
     */
    public function buildMinimalPayload()
    {
        $this->reset();

        $this->buildBaseNode();
        $this->buildMinimalPersonDetailsNode();
    }

    /**
     * Build base node
     */
    public function buildBaseNode()
    {
        $language = \Language::getLanguage(\Context::getContext()->employee->id_lang);
        $locale = $language['locale'];

        $this->payload->items += [
            'url' => $this->getCallBackUrl(),
            'preferred_language_code' => str_replace('-', '_', $locale),
            'primary_currency_code' => $this->getCurrencyIsoCode(),
        ];
    }

    /**
     * Build full persone_details node
     */
    public function buildFullPersonDetailsNode()
    {
        $psAccount = new PsAccountRepository();
        $psxFormData = $psAccount->getPsxForm(true);

        $this->payload->items['person_details'] = array_filter([
            'email_address' => $psAccount->getOnboardedAccount()->getEmail(),
            'name' => [
                'given_name' => $psxFormData['business_contact_first_name'],
                'surname' => $psxFormData['business_contact_last_name'],
                'prefix' => $psxFormData['business_contact_gender'],
            ],
        ]);
    }

    /**
     * Build minimal persone_details node
     */
    public function buildMinimalPersonDetailsNode()
    {
        $psAccount = new PsAccountRepository();
        $psxFormData = $psAccount->getPsxForm(true);

        $this->payload->items['person_details'] = array_filter([
            'email_address' => $psAccount->getOnboardedAccount()->getEmail(),
        ]);
    }

    /**
     * Build business_details node
     */
    public function buildBusinessDetailsNode()
    {
        $psAccount = new PsAccountRepository();
        $psxFormData = $psAccount->getPsxForm(true);

        $this->payload->items['business_details'] = array_filter([
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
        ]);
    }

    /**
     * Generate the callback url used by the paypal button
     *
     * @return string callback link
     */
    private function getCallBackUrl()
    {
        $link = new \Link();

        return $link->getAdminLink('AdminPaypalOnboardingPrestashopCheckout');
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
