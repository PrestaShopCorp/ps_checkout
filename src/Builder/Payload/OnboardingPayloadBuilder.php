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
     * @var \Context
     */
    private $context;

    /**
     * @param PsAccountRepository $psAccount
     * @param LanguageAdapter $languageAdapter
     */
    public function __construct(PsAccountRepository $psAccount, LanguageAdapter $languageAdapter)
    {
        parent::__construct();
        $this->psAccount = $psAccount;
        $this->languageAdapter = $languageAdapter;
        $this->context = \Context::getContext();
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
        $this->buildIndividualOwnersNode();
        $this->buildBusinessEntityNode();
        $this->buildPartnerConfigOverrideNode();
        $this->buildFinancialInstrumentsNoce();
        $this->buildOperationsNode();
        $this->buildProductsNode();
        $this->buildLegalConsentsNode();
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

    private function buildIndividualOwnersNode()
    {

    }

    private function buildBusinessEntityNode()
    {
        $psxFormData = $this->psAccount->getPsxForm(true);

        $node['business_entity'] = array_filter([
            'business_type' => array_filter([
                'type' => $psxFormData['business_entity_type'],
                'subtype' => $psxFormData['business_entity_subtype'],
            ]),
            'business_industry' => array_filter([
                'category' => $psxFormData['business_category'],
                'mcc_code' => $psxFormData['business_industry_mcc_code'],
                'subcategory' => $psxFormData['business_subcategory'],
            ]),
            'business_incorporation' => array_filter([
                'incorporation_country_code' => $psxFormData['business_incorporation_country_code'],
                'incorporation_date' => $psxFormData['business_incorporation_date']
            ]),
            'names' => [
                0 => array_filter([
                    'business_name' => $psxFormData['shop_name'],
                    'id' => '',
                    'type' => 'LEGAL',
                ])
            ],
            'emails' => [
                0 => array_filter([
                    'type' => 'CUSTOMER_SERVICE',
                    'email' => $this->psAccount->getOnboardedAccount()->getEmail(),
                ])
            ],
            'website' => $this->context->shop->getBaseURL(),
            'addresses' => [
                0 => array_filter([
                    'address_line_1' => $psxFormData['business_address_line_1'],
                    'address_line_2' => $psxFormData['business_address_line_2'],
                    'address_line_3' => $psxFormData['business_address_line_3'],
                    'admin_area_1' => $psxFormData['business_address_admin_area_1'],
                    'admin_area_2' => $psxFormData['business_address_admin_area_2'],
                    'admin_area_3' => $psxFormData['business_address_admin_area_3'],
                    'admin_area_4' => $psxFormData['business_address_admin_area_4'],
                    'postal_code' => $psxFormData['business_address_zip'],
                    'address_details' => [
                        'street_number' => $psxFormData['business_address_street_number'],
                        'street_name' => $psxFormData['business_address_street_name'],
                        'street_type' => $psxFormData['business_address_street_type'],
                        'delivery_service' => $psxFormData['business_address_delivery_service'],
                        'building_name' => $psxFormData['business_address_building_name'],
                        'sub_building' => $psxFormData['business_address_sub_building'],
                    ],
                    'type' => $psxFormData['business_address_type'],
                ])
            ],
            'phones' => [
                0 => array_filter([
                    'country_code' => (string) $psxFormData['business_phone_country'],
                    'national_number' => $psxFormData['business_phone'],
                    'extension_number' => $psxFormData['business_phone_extension_number'],
                    'type' => 'CUSTOMER_SERVICE',
                ])
            ],
            'documents' => [
                0 => array_filter([
                    'id' => $psxFormData['business_document_id'],
                    'labels' => $psxFormData['business_document_labels'],
                    'name' => $psxFormData['business_document_name'],
                    'identification_number' => $psxFormData['business_document_identification_number'],
                    'issue_date' => $psxFormData['business_document_issue_date'],
                    'expiry_date' => $psxFormData['business_document_expiry_date'],
                    'issuing_country_code' => $psxFormData['business_document_issuing_country_code'],
                    'files' => array_map(function ($psxFormFile) {
                        return array_filter([
                            'id' => $psxFormFile['id'],
                            'reference_url' => $psxFormFile['reference_url'],
                            'content_type' => $psxFormFile['content_type'],
                            'create_time' => $psxFormFile['create_time'],
                            'size' => $psxFormFile['size'],
                        ]);
                    }, $psxFormData['business_document_files']),
                    'links' => array_map(function ($psxFormLink) {
                        return array_filter([
                            'href' => $psxFormLink['href'],
                            'rel' => $psxFormLink['rel'],
                            'method' => $psxFormLink['method'],
                        ]);
                    }, $psxFormData['business_document_links']),
                    'type' => $psxFormData['business_document_type'],
                ])
            ],
            'beneficial_owners' => [
                'individual_beneficial_owners' => array_map(function ($psxFormIndividualBeneficialOwner) {
                    return array_filter([
                        'id' => $psxFormIndividualBeneficialOwner['id'],
                        'names' => array_map(function($name) {
                            return $this->mapPersonNameDTO($name);
                        }, $psxFormIndividualBeneficialOwner['names']),
                        'citizenship' => $psxFormIndividualBeneficialOwner['citizenship'],
                        'addresses' => array_map(function($address) {
                            return $this->mapAddressDTO($address);
                        }, $psxFormIndividualBeneficialOwner['addresses']),
                        'phones' => array_map(function($phone) {
                            return array_filter([
                                'country_code' => $phone['country_code'],
                                'national_number' => $phone['national_number'],
                                'extension_number' => $phone['extension_number'],
                                'type' => $phone['type'],
                            ]);
                        }, $psxFormIndividualBeneficialOwner['phones']),
                        'birth_details' => [
                            'date_of_birth' => $psxFormIndividualBeneficialOwner['date_of_birth']
                        ],
                        'documents' => array_map(function($document) {
                            return $this->mapDocumentDTO($document);
                        }, $psxFormIndividualBeneficialOwner['documents']),
                        'percentage_of_ownership' => $psxFormIndividualBeneficialOwner['percentage_of_ownership'],
                    ]);
                }, $psxFormData['individual_beneficial_owners']),
                'business_beneficial_owners' => array_map(function ($psxFormBusinessBeneficialOwner) {
                    return array_filter([
                        'business_type' => array_filter([
                            'type' => $psxFormBusinessBeneficialOwner['business_type'],
                            'subtype' => $psxFormBusinessBeneficialOwner['business_subtype'],
                        ]),
                        'business_industry' => array_filter([
                            'category' => $psxFormBusinessBeneficialOwner['business_category'],
                            'mcc_code' => $psxFormBusinessBeneficialOwner['business_mcc_code'],
                            'subcategory' => $psxFormBusinessBeneficialOwner['business_subcategory'],
                        ]),
                        'business_incorporation' => array_filter([
                            'incorporation_country_code' => $psxFormBusinessBeneficialOwner['business_incorporation_country_code'],
                            'incorporation_date' => $psxFormBusinessBeneficialOwner['business_incorporation_date'],
                        ]),
                        'names' => array_filter(array_map(function ($name) {
                            return array_filter([
                                'business_name' => $name['business_name'],
                                'id' => $name['id'],
                                'type' => $name['type'],
                            ]);
                        }, $psxFormBusinessBeneficialOwner['business_names'])),
                        'emails' => array_filter(array_map(function ($email) {
                            return array_filter([
                                'type' => $email['type'],
                                'email' => $email['email'],
                            ]);
                        }, $psxFormBusinessBeneficialOwner['business_emails'])),
                        'website' => $psxFormBusinessBeneficialOwner['website'],
                        'addresses' => array_filter(array_map(function ($address) {
                            return $this->mapAddressDTO($address);
                        }, $psxFormBusinessBeneficialOwner['business_addresses'])),
                        'phones' => array_filter(array_map(function ($phone) {
                            return array_filter([
                                'country_code' => $phone['country_code'],
                                'national_number' => $phone['national_number'],
                                'extension_number' => $phone['extension_number'],
                                'type' => $phone['type'],
                            ]);
                        }, $psxFormBusinessBeneficialOwner['business_phones'])),
                        'documents' => array_map(function($document) {
                            return $this->mapDocumentDTO($document);
                        }, $psxFormBusinessBeneficialOwner['documents']),
                    ]);
                }, $psxFormData['business_beneficial_owners']),
            ],
            'office_bearers' => array_filter(array_map(function ($bearer) {
                return array_filter([
                    'id' => $bearer['id'],
                    'names' => array_filter(array_map(function ($name) {
                        $this->mapPersonNameDTO($name);
                    }, $bearer['names'])),
                    'citizenship' => $bearer['citizenship'],
                    'addresses' => array_filter(array_map(function ($address) {
                        return $this->mapAddressDTO($address);
                    }, $bearer['addresses'])),
                    'phones' => array_filter(array_map(function ($phone) {
                        return $this->mapPhoneDTO($phone);
                    }, $bearer['phones'])),
                    'birth_details' => [
                        'date_of_birth' => $bearer['date_of_birth']
                    ],
                    'documents' => array_filter(array_map(function ($document) {
                        return $this->mapDocumentDTO($document);
                    }, $bearer['documents'])),
                    'role' => $bearer['role'],
                ]);
            }, $psxFormData['business_office_bearers'])),
            'annual_sales_volume_range' => array_filter(
                [
                    'minimum_amount' => array_filter([
                        'currency_code' => $psxFormData['annual_sales_volume_range_currency_code'],
                        'value' => $psxFormData['annual_sales_volume_range_min_value'],
                    ]),
                    'maximum_amount' => array_filter([
                        'currency_code' => $psxFormData['annual_sales_volume_range_currency_code'],
                        'value' => $psxFormData['annual_sales_volume_range_max_value'],
                    ]),
                ]
            ),
            'average_monthly_volume_range' => array_filter(
                [
                    'minimum_amount' => array_filter([
                        'currency_code' => $psxFormData['average_monthly_volume_range_currency_code'],
                        'value' => $psxFormData['average_monthly_volume_range_min_value'],
                    ]),
                    'maximum_amount' => array_filter([
                        'currency_code' => $psxFormData['average_monthly_volume_range_currency_code'],
                        'value' => $psxFormData['average_monthly_volume_range_max_value'],
                    ]),
                ]
            ),
            'purpose_code' => $psxFormData['business_purpose_code'],
            'business_description' => $psxFormData['business_description'],
        ]);

        $this->getPayload()->addAndMergeItems($node);
    }

    private function mapDocumentDTO(array $document) {
        return array_filter([
            'id' => $document['id'],
            'labels' => $document['labels'],
            'name' => $document['name'],
            'identification_number' => $document['identification_number'],
            'issue_date' => $document['issue_date'],
            'expiry_date' => $document['expiry_date'],
            'issuing_country_code' => $document['issuing_country_code'],
            'files' => array_map(function ($file) {
                return array_filter([
                    'id' => $file['id'],
                    'reference_url' => $file['reference_url'],
                    'content_type' => $file['content_type'],
                    'create_time' => $file['create_time'],
                    'size' => $file['size'],
                ]);
            }, $document['files']),
            'links' => array_map(function ($link) {
                return array_filter([
                    'href' => $link['href'],
                    'rel' => $link['rel'],
                    'method' => $link['method'],
                ]);
            }, $document['links']),
            'type' => $document['type'],
        ]);
    }

    private function mapAddressDTO(array $address) {
        return array_filter([
            'address_line_1' => $address['address_line_1'],
            'address_line_2' => $address['address_line_2'],
            'address_line_3' => $address['address_line_3'],
            'admin_area_1' => $address['admin_area_1'],
            'admin_area_2' => $address['admin_area_2'],
            'admin_area_3' => $address['admin_area_3'],
            'admin_area_4' => $address['admin_area_4'],
            'postal_code' => $address['postal_code'],
            'address_details' => [
                'street_number' => $address['street_number'],
                'street_name' => $address['street_name'],
                'street_type' => $address['street_type'],
                'delivery_service' => $address['delivery_service'],
                'building_name' => $address['building_name'],
                'sub_building' => $address['sub_building'],
            ],
            'type' => $address['type'],
        ]);
    }

    private function mapPersonNameDTO(array $name) {
        return array_filter([
            'prefix' => $name['prefix'],
            'given_name' => $name['given_name'],
            'surname' => $name['surname'],
            'middle_name' => $name['middle_name'],
            'suffix' => $name['suffix'],
            'full_name' => $name['full_name'],
            'type' => $name['type'],
        ]);
    }

    private function mapPhoneDTO(array $phone)
    {
        return array_filter([
            'country_code' => (string) $phone['country_code'],
            'national_number' => $phone['country_code'],
            'extension_number' => $phone['country_code'],
            'type' => $phone['type'],
        ]);
    }
}
