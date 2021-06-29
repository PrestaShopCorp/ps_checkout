<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

use PrestaShop\Module\PrestashopCheckout\Adapter\LanguageAdapter;
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\PsxData\PsxDataMatrice;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;

/**
 * Build the payload for getting paypal onboarding link
 */
class OnboardingPayloadBuilder extends Builder
{
    /**
     * @var PsAccountRepository
     */
    private $psAccount;

    /**
     * @var LanguageAdapter
     */
    private $languageAdapter;

    /**
     * @param PsAccountRepository $psAccount
     * @param LanguageAdapter $languageAdapter
     */
    public function __construct(PsAccountRepository $psAccount, LanguageAdapter $languageAdapter)
    {
        parent::__construct();
        $this->psAccount = $psAccount;
        $this->languageAdapter = $languageAdapter;
    }

    /**
     * Build the full payload with customer details
     */
    public function buildFullPayload()
    {
        parent::buildFullPayload();

        $this->buildBaseNode();
        $this->buildFullPersonDetailsNode();
        $this->buildBusinessDetailsNode();
    }

    /**
     * Build payload without customer details
     */
    public function buildMinimalPayload()
    {
        parent::buildMinimalPayload();

        $this->buildBaseNode();
        $this->buildMinimalPersonDetailsNode();
    }

    /**
     * Build base node
     */
    public function buildBaseNode()
    {
        $language = $this->languageAdapter->getLanguage((int) \Context::getContext()->employee->id_lang);

        $locale = $language['locale'];

        $node = [
            'url' => $this->getCallBackUrl(),
            'preferred_language_code' => $locale,
            'primary_currency_code' => $this->getCurrencyIsoCode(),
        ];

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build full persone_details node
     */
    public function buildFullPersonDetailsNode()
    {
        $psxFormData = $this->psAccount->getPsxForm(true);

        $node['person_details'] = array_filter([
            'email_address' => $this->psAccount->getOnboardedAccount()->getEmail(),
            'name' => [
                'given_name' => $psxFormData['business_contact_first_name'],
                'surname' => $psxFormData['business_contact_last_name'],
                'prefix' => $psxFormData['business_contact_gender'],
            ],
        ]);

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build minimal persone_details node
     */
    public function buildMinimalPersonDetailsNode()
    {
        $node['person_details'] = array_filter([
            'email_address' => $this->psAccount->getOnboardedAccount()->getEmail(),
        ]);

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build business_details node
     */
    public function buildBusinessDetailsNode()
    {
        $psxFormData = $this->psAccount->getPsxForm(true);

        $node['business_details'] = array_filter([
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

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Generate the callback url used by the paypal button
     *
     * @return string callback link
     */
    private function getCallBackUrl()
    {
        return (new LinkAdapter())->getAdminLink('AdminPaypalOnboardingPrestashopCheckout');
    }

    /**
     * Get the iso code of the default currency for the shop
     *
     * @return string iso code
     */
    private function getCurrencyIsoCode()
    {
        $currencyId = (int) \Configuration::get(
            'PS_CURRENCY_DEFAULT',
            null,
            null,
            (int) \Context::getContext()->shop->id
        );
        $currency = \Currency::getCurrency($currencyId);

        return $currency['iso_code'];
    }
}
