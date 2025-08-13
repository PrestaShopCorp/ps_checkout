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

namespace PsCheckout\Presentation\Presenter\Settings\Front;

use PsCheckout\Core\Settings\Configuration\PayPalCardConfiguration;
use PsCheckout\Infrastructure\Adapter\Context;
use PsCheckout\Presentation\Presenter\PresenterInterface;

class SupportedCardBrandsPresenter implements PresenterInterface
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function present(): array
    {
        $country = $this->context->getCountry();
        $currency = $this->context->getCurrency();

        if (($country === null || !$country->id) || ($currency === null || !$currency->id)) {
            return [];
        }

        $countryIso = $country->iso_code === 'GB' ? 'UK' : $country->iso_code;
        $currencyIso = $currency->iso_code;

        if ($this->hasSupportedCardBrandsByCountry($countryIso)) {
            return array_values(array_intersect(
                $this->getSupportedCardBrandsByCountry($countryIso),
                $this->getSupportedCardBrandsByCurrency($currencyIso),
                $this->getSupportedCardBrandsByCountryAndCurrency($countryIso, $currencyIso)
            ));
        }

        return array_values(array_intersect(
            $this->getSupportedCardBrandsByCountry($countryIso),
            $this->getSupportedCardBrandsByCurrency($currencyIso)
        ));
    }

    /**
     * @param string $countryIso
     * @param string $currencyIso
     *
     * @return array an array of card brands that are supported for the given country and currency
     */
    private function getSupportedCardBrandsByCountryAndCurrency(string $countryIso, string $currencyIso): array
    {
        $supportedCardBrandsByCountryAndCurrency = PayPalCardConfiguration::SUPPORTED_CARD_BRANDS_BY_COUNTRY_AND_CURRENCY;

        return isset($supportedCardBrandsByCountryAndCurrency[$countryIso][$currencyIso])
            ? $supportedCardBrandsByCountryAndCurrency[$countryIso][$currencyIso]
            : ['MASTERCARD', 'VISA'];
    }

    /**
     * @param string $countryIso
     *
     * @return array an array of card brands that are supported for the given country
     */
    private function getSupportedCardBrandsByCountry(string $countryIso): array
    {
        $supportedCardBrandsByCountry = PayPalCardConfiguration::SUPPORTED_CARD_BRANDS_BY_COUNTRY;

        return isset($supportedCardBrandsByCountry[$countryIso])
            ? $supportedCardBrandsByCountry[$countryIso]
            : [];
    }

    /**
     * @param string $currencyIso
     *
     * @return array an array of card brands that are supported for the given currency
     */
    private function getSupportedCardBrandsByCurrency(string $currencyIso): array
    {
        $supportedCardBrandsByCurrency = PayPalCardConfiguration::SUPPORTED_CARD_BRANDS_BY_CURRENCY;

        return isset($supportedCardBrandsByCurrency[$currencyIso])
            ? $supportedCardBrandsByCurrency[$currencyIso]
            : [];
    }

    /**
     * @param string $countryIso
     *
     * @return bool whether the given country has supported card brands by country and currency
     */
    private function hasSupportedCardBrandsByCountry(string $countryIso): bool
    {
        $supportedCardBrandsByCountryAndCurrency = PayPalCardConfiguration::SUPPORTED_CARD_BRANDS_BY_COUNTRY_AND_CURRENCY;

        return isset($supportedCardBrandsByCountryAndCurrency[$countryIso]);
    }
}
