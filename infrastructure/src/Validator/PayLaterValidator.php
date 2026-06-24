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

namespace PsCheckout\Infrastructure\Validator;

use PsCheckout\Core\FundingSource\Constraint\FundingSourceConstraint;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class PayLaterValidator implements PayLaterValidatorInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;

    public function __construct(ContextInterface $context, PayPalConfiguration $payPalConfiguration)
    {
        $this->context = $context;
        $this->payPalConfiguration = $payPalConfiguration;
    }

    public function isPayLaterAvailable(): bool
    {
        $merchantCountry = $this->payPalConfiguration->getMerchantCountry();
        $countries = FundingSourceConstraint::getCountries('paylater');
        $currency = $this->context->getCurrency()->iso_code;
        $locale = $this->context->getLanguage()->locale;
        $customerCountry = $this->context->getCountry()->iso_code;

        // Define supported country-currency combinations for Pay Later messaging
        $supportedCountryCurrencyMap = [
            'AU' => 'AUD', // Australia
            'FR' => 'EUR', // France
            'DE' => 'EUR', // Germany
            'IT' => 'EUR', // Italy
            'ES' => 'EUR', // Spain
            'GB' => 'GBP', // United Kingdom
            'US' => 'USD', // United States,
            'CA' => 'CAD', // Canada
        ];

        // Define locale to country mapping for website locale validation
        $localeCountryMap = [
            'en-AU' => 'AU', // Australia
            'fr-FR' => 'FR', // France
            'de-DE' => 'DE', // Germany
            'it-IT' => 'IT', // Italy
            'es-ES' => 'ES', // Spain
            'en-GB' => 'GB', // United Kingdom
            'en-US' => 'US', // United States
            'fr-CA' => 'CA', // Canada
            'en-CA' => 'CA', // Canada
        ];

        return in_array($merchantCountry, $countries, true)
            && $merchantCountry === $customerCountry
            && isset($supportedCountryCurrencyMap[$customerCountry])
            && $supportedCountryCurrencyMap[$customerCountry] === $currency
            && isset($localeCountryMap[$locale])
            && $localeCountryMap[$locale] === $merchantCountry;
    }
}
