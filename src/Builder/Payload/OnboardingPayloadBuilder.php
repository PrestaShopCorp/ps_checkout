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
     * @var array
     */
    private $psxFormData;

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
        $this->psxFormData = $this->psAccount->getPsxForm(true);
    }

    /**
     * Build the full payload with customer details
     */
    public function buildFullPayload()
    {
        parent::buildFullPayload();

        $this->buildBaseNode();
        $this->buildIndividualOwnersNode();
        $this->buildBusinessEntityNode();
        $this->buildPartnerConfigOverrideNode();
        $this->buildFinancialInstrumentsNode();
        $this->buildOperationsNode();
        $this->buildProductsNode();
        $this->buildLegalConsentsNode();
    }

    /**
     * Build payload without customer details
     */
    public function buildMinimalPayload()
    {
        $this->buildFullPayload();
    }

    /**
     * Build base node
     */
    public function buildBaseNode()
    {
        $language = $this->languageAdapter->getLanguage((int) \Context::getContext()->employee->id_lang);

        $locale = $language['locale'];

        $node = [
            'email' => $this->psAccount->getOnboardedAccount()->getEmail(),
            'preferred_language_code' => $locale,
            'tracking_id' => $this->psxFormData['tracking_id'],
            'primary_currency_code' => $this->getCurrencyIsoCode(),
            'url' => $this->getCallBackUrl(),
        ];

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
        $node['individual_owners'] = array_filter(array_map(
            function ($individualOwner) {
                return array_filter(
                    [
                        'id' => $individualOwner['id'],
                        'names' => array_map(function ($name) {
                            return $this->mapPersonNameDTO($name);
                        }, $individualOwner['names']),
                        'citizenship' => $individualOwner['citizenship'],
                        'addresses' => array_map(function ($address) {
                            return $this->mapAddressDTO($address);
                        }, $individualOwner['addresses']),
                        'phones' => array_map(function ($phones) {
                            return $this->mapPhoneDTO($phones);
                        }, $individualOwner['phones']),
                        'birth_details' => [
                            'date_of_birth' => $individualOwner['date_of_birth'],
                        ],
                        'documents' => array_map(function ($document) {
                            return $this->mapDocumentDTO($document);
                        }, $individualOwner['documents']),
                        'type' => $individualOwner['type'],
                    ]
                );
            }, $this->psxFormData['business_individual_owners']));

        $this->getPayload()->addAndMergeItems($node);
    }

    private function buildBusinessEntityNode()
    {
        $node['business_entity'] = array_filter([
            'business_type' => array_filter([
                'type' => $this->psxFormData['business_entity_type'],
                'subtype' => $this->psxFormData['business_entity_subtype'],
            ]),
            'business_industry' => array_filter([
                'category' => $this->psxFormData['business_category'],
                'mcc_code' => $this->psxFormData['business_industry_mcc_code'],
                'subcategory' => $this->psxFormData['business_subcategory'],
            ]),
            'business_incorporation' => array_filter([
                'incorporation_country_code' => $this->psxFormData['business_incorporation_country_code'],
                'incorporation_date' => $this->psxFormData['business_incorporation_date']
            ]),
            'names' => [
                0 => array_filter([
                    'business_name' => $this->psxFormData['shop_name'],
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
                    'address_line_1' => $this->psxFormData['business_address_line_1'],
                    'address_line_2' => $this->psxFormData['business_address_line_2'],
                    'address_line_3' => $this->psxFormData['business_address_line_3'],
                    'admin_area_1' => $this->psxFormData['business_address_admin_area_1'],
                    'admin_area_2' => $this->psxFormData['business_address_admin_area_2'],
                    'admin_area_3' => $this->psxFormData['business_address_admin_area_3'],
                    'admin_area_4' => $this->psxFormData['business_address_admin_area_4'],
                    'postal_code' => $this->psxFormData['business_address_zip'],
                    'address_details' => [
                        'street_number' => $this->psxFormData['business_address_street_number'],
                        'street_name' => $this->psxFormData['business_address_street_name'],
                        'street_type' => $this->psxFormData['business_address_street_type'],
                        'delivery_service' => $this->psxFormData['business_address_delivery_service'],
                        'building_name' => $this->psxFormData['business_address_building_name'],
                        'sub_building' => $this->psxFormData['business_address_sub_building'],
                    ],
                    'type' => $this->psxFormData['business_address_type'],
                ])
            ],
            'phones' => [
                0 => array_filter([
                    'country_code' => (string) $this->psxFormData['business_phone_country'],
                    'national_number' => $this->psxFormData['business_phone'],
                    'extension_number' => $this->psxFormData['business_phone_extension_number'],
                    'type' => 'CUSTOMER_SERVICE',
                ])
            ],
            'documents' => [
                0 => array_filter([
                    'id' => $this->psxFormData['business_document_id'],
                    'labels' => $this->psxFormData['business_document_labels'],
                    'name' => $this->psxFormData['business_document_name'],
                    'identification_number' => $this->psxFormData['business_document_identification_number'],
                    'issue_date' => $this->psxFormData['business_document_issue_date'],
                    'expiry_date' => $this->psxFormData['business_document_expiry_date'],
                    'issuing_country_code' => $this->psxFormData['business_document_issuing_country_code'],
                    'files' => array_map(function ($psxFormFile) {
                        return $this->mapFileDTO($psxFormFile);
                    }, $this->psxFormData['business_document_files']),
                    'links' => array_map(function ($psxFormLink) {
                        return $this->mapLinkDTO($psxFormLink);
                    }, $this->psxFormData['business_document_links']),
                    'type' => $this->psxFormData['business_document_type'],
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
                            return $this->mapPhoneDTO($phone);
                        }, $psxFormIndividualBeneficialOwner['phones']),
                        'birth_details' => [
                            'date_of_birth' => $psxFormIndividualBeneficialOwner['date_of_birth']
                        ],
                        'documents' => array_map(function($document) {
                            return $this->mapDocumentDTO($document);
                        }, $psxFormIndividualBeneficialOwner['documents']),
                        'percentage_of_ownership' => $psxFormIndividualBeneficialOwner['percentage_of_ownership'],
                    ]);
                }, $this->psxFormData['individual_beneficial_owners']),
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
                            return $this->mapPhoneDTO($phone);
                        }, $psxFormBusinessBeneficialOwner['business_phones'])),
                        'documents' => array_map(function($document) {
                            return $this->mapDocumentDTO($document);
                        }, $psxFormBusinessBeneficialOwner['documents']),
                    ]);
                }, $this->psxFormData['business_beneficial_owners']),
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
            }, $this->psxFormData['business_office_bearers'])),
            'annual_sales_volume_range' => array_filter(
                [
                    'minimum_amount' => array_filter([
                        'currency_code' => $this->psxFormData['annual_sales_volume_range_currency_code'],
                        'value' => $this->psxFormData['annual_sales_volume_range_min_value'],
                    ]),
                    'maximum_amount' => array_filter([
                        'currency_code' => $this->psxFormData['annual_sales_volume_range_currency_code'],
                        'value' => $this->psxFormData['annual_sales_volume_range_max_value'],
                    ]),
                ]
            ),
            'average_monthly_volume_range' => array_filter(
                [
                    'minimum_amount' => array_filter([
                        'currency_code' => $this->psxFormData['average_monthly_volume_range_currency_code'],
                        'value' => $this->psxFormData['average_monthly_volume_range_min_value'],
                    ]),
                    'maximum_amount' => array_filter([
                        'currency_code' => $this->psxFormData['average_monthly_volume_range_currency_code'],
                        'value' => $this->psxFormData['average_monthly_volume_range_max_value'],
                    ]),
                ]
            ),
            'purpose_code' => $this->psxFormData['business_purpose_code'],
            'business_description' => $this->psxFormData['business_description'],
        ]);

        $this->getPayload()->addAndMergeItems($node);
    }

    private function buildFinancialInstrumentsNode()
    {
        $node['financial_instruments'] = array_filter([
            'banks' => array_filter(array_map(function ($bank) {
                    return $this->mapBankDTO($bank);
                }, $this->psxFormData['financial_instruments_banks'])
            ),
        ]);

        $this->getPayload()->addAndMergeItems($node);
    }

    private function buildOperationsNode()
    {
        $node['operations'] = array_filter([
            'banks' => array_filter(array_map(function ($operation) {
                    return array_filter([
                        'operation' => $operation['operation'],
                        'api_integration_preference' => array_filter([
                            'classic_api_integration' => [],
                            'rest_api_integration' => array_filter([
                                'integration_method' => $operation['rest_integration_method'],
                                'integration_type' => $operation['rest_integration_type'],
                                'first_party_details' => [
                                    'features' => $operation['first_party_features'],
                                    'seller_nonce' => $operation['first_party_seller_nonce'],
                                ],
                                'third_party_details' => $operation['third_party_features'],
                            ])
                        ]),
                        'billing_agreement' => [
                            'description' => $operation['billing_agreement_description'],
                            'billing_experience_preference' => [
                                'experience_id' => $operation['billing_agreement_experience_id'],
                                'billing_context_set' => (bool) $operation['billing_agreement_context_set'],
                            ],
                            'merchant_custom_data' => $operation['billing_agreement_merchant_custom_data'],
                            'approval_url' => $operation['billing_agreement_approval_url'],
                            'ec_token' => $operation['billing_agreement_ec_token'],
                        ],
                    ]);
                }, $this->psxFormData['operations'])
            ),
        ]);

        $this->getPayload()->addAndMergeItems($node);
    }

    private function buildProductsNode()
    {
        $node['products'] = array_filter($this->psxFormData['products']);

        $this->getPayload()->addAndMergeItems($node);
    }

    private function buildLegalConsentsNode()
    {
        $node['legal_consents'] = array_filter(array_map(function ($legalConsent) {
            return array_filter([
                'type' => $legalConsent['type'],
                'granted' => (bool) $legalConsent['granted'],
            ]);
        }, $this->psxFormData['legal_consents']));

        $this->getPayload()->addAndMergeItems($node);
    }

    private function buildPartnerConfigOverrideNode()
    {
        $node['partner_config_override'] = array_filter([
            'partner_logo_url' => $this->psxFormData['partner_logo_url'],
            'return_url' => $this->psxFormData['return_url'],
            'return_url_description' => $this->psxFormData['return_url_description'],
            'action_renewal_url' => $this->psxFormData['action_renewal_url'],
            'show_add_credit_card' => (bool) $this->psxFormData['show_add_credit_card'],
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
                return $this->mapFileDTO($file);
            }, $document['files']),
            'links' => array_map(function ($link) {
                return $this->mapLinkDTO($link);
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

    private function mapAddressPortableDTO(array $address) {
        return array_filter([
            'address_line_1' => $address['address_line_1'],
            'address_line_2' => $address['address_line_2'],
            'admin_area_1' => $address['admin_area_1'],
            'admin_area_2' => $address['admin_area_2'],
            'postal_code' => $address['postal_code'],
            'country_code' => $address['country_code'],
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

    private function mapFileDTO(array $file)
    {
        return array_filter([
            'id' => $file['id'],
            'reference_url' => $file['reference_url'],
            'content_type' => $file['content_type'],
            'create_time' => $file['create_time'],
            'size' => $file['size'],
        ]);
    }

    private function mapLinkDTO(array $link)
    {
        return array_filter([
            'href' => $link['href'],
            'rel' => $link['rel'],
            'method' => $link['method'],
        ]);
    }

    private function mapBankDTO(array $bank)
    {
        return array_filter([
            'nick_name' => $bank['nick_name'],
            'account_number' => $bank['account_number'],
            'account_type' => $bank['account_type'],
            'identifiers' => array_filter(array_map(function ($identifier) {
                return array_filter([
                   'type' => $identifier['type'],
                   'mandate' => $identifier['mandate'],
                ]);
            }, $bank['identifiers'])),
            'branch_location' => $this->mapAddressPortableDTO($bank['address']),
            'mandate' => [
                'accepted' => (bool) $bank['mandate_accepted'],
            ],
        ]);
    }
}
