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

namespace PsCheckout\Core\FundingSource\Eligibility\Checker;

use PsCheckout\Core\FundingSource\ValueObject\FundingSource;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Core\Util\CountryResolverInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

abstract class BaseFundingSourceEligibilityChecker implements FundingSourceEligibilityCheckerInterface
{
    /** @var ContextInterface */
    protected $context;

    /** @var ConfigurationInterface */
    protected $configuration;

    /** @var CountryResolverInterface */
    protected $countryResolver;

    public function __construct(
        ContextInterface $context,
        ConfigurationInterface $configuration,
        CountryResolverInterface $countryResolver
    ) {
        $this->context = $context;
        $this->configuration = $configuration;
        $this->countryResolver = $countryResolver;
    }

    /**
     * @inheritDoc
     */
    public function supports(FundingSource $fundingSource): bool
    {
        return $fundingSource->getName() === $this->getSupportedName();
    }

    /**
     * @inheritDoc
     */
    public function isEligible(FundingSource $fundingSource): bool
    {
        $intent = $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_INTENT) ?: PayPalOrderIntent::CAPTURE;
        if (!in_array($intent, $this->getSupportedIntents(), true)) {
            return false;
        }

        if (!empty($this->getSupportedMerchantCountries())) {
            $merchantCountry = $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_COUNTRY_MERCHANT);
            if (!in_array($merchantCountry, $this->getSupportedMerchantCountries(), true)) {
                return false;
            }
        }

        $configurations = array_map(function ($assertion) {
            return $this->configuration->getBoolean($assertion);
        }, $this->assertConfigurations());
        if (!empty($configurations) && in_array(false, $configurations, true)) {
            return false;
        }

        $country = $this->countryResolver->getBuyerCountryIsoCode();
        if (!empty($fundingSource->getCountries()) && !in_array($country, $fundingSource->getCountries(), true)) {
            return false;
        }

        $currency = $this->context->getCurrencyIsoCode();
        if (!empty($this->getAllowedCurrenciesIsoCodes()) && !in_array($currency, $this->getAllowedCurrenciesIsoCodes(), true)) {
            return false;
        }

        $cartTotal = $this->context->getCartOrderTotal();
        if ($cartTotal !== null) {
            $minAmount = $this->getMinAmount($currency);
            if ($minAmount !== null && $cartTotal < $minAmount) {
                return false;
            }
            $maxAmount = $this->getMaxAmount($currency);
            if ($maxAmount !== null && $cartTotal > $maxAmount) {
                return false;
            }
        }

        return true;
    }

    /**
     * Name of the funding source handled by this checker (e.g. 'bancontact').
     */
    abstract protected function getSupportedName(): string;

    /**
     * Allowed currencies for this funding source.
     *
     * @return string[]
     */
    abstract protected function getAllowedCurrenciesIsoCodes(): array;

    /**
     * Supported intents for this funding source.
     *
     * @return string[]
     */
    abstract protected function getSupportedIntents(): array;

    /**
     * Boolean configuration assertions for this funding source.
     *
     * @return string[]
     */
    abstract protected function assertConfigurations(): array;

    /**
     * Supporter merchant countries for this funding source.
     *
     * @return string[]
     */
    abstract protected function getSupportedMerchantCountries(): array;

    /**
     * @inheritDoc
     */
    public function getMinAmount(string $currency): ?float
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getMaxAmount(string $currency): ?float
    {
        return null;
    }
}
