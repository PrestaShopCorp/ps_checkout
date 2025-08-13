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

namespace PsCheckout\Core\Settings\Configuration;

use Exception;
use PsCheckout\Core\FundingSource\Constraint\FundingSourceConstraint;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\OAuth\OAuthServiceInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Environment\EnvInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;
use Psr\Log\LoggerInterface;

class PayPalSdkConfiguration
{
    const SDK_BO_ENDPOINT = '/sdk/ps_checkout-bo-sdk.umd.js';

    const SDK_FO_ENDPOINT = '/sdk/ps_checkout-fo-sdk.js';

    /**
     * google_pay and apple_pay are not considered funding sources
     * and passing these values to disableFunding will crash PayPal SDK
     */
    const NOT_FUNDING_SOURCES = ['google_pay', 'apple_pay'];

    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;

    /**
     * @var EnvInterface
     */
    private $env;

    /**
     * @var FundingSourcePresenterInterface
     */
    private $fundingSourcePresenter;

    /**
     * @var PayPalCustomerRepositoryInterface
     */
    private $payPalCustomerRepository;

    /**
     * @var OAuthServiceInterface
     */
    private $oAuthService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ContextInterface $context
     * @param ConfigurationInterface $configuration
     * @param PayPalConfiguration $payPalConfiguration
     * @param EnvInterface $env
     * @param FundingSourcePresenterInterface $fundingSourcePresenter
     * @param PayPalCustomerRepositoryInterface $payPalCustomerRepository
     * @param OAuthServiceInterface $oAuthService
     * @param LoggerInterface $logger
     */
    public function __construct(
        ContextInterface $context,
        ConfigurationInterface $configuration,
        PayPalConfiguration $payPalConfiguration,
        EnvInterface $env,
        FundingSourcePresenterInterface $fundingSourcePresenter,
        PayPalCustomerRepositoryInterface $payPalCustomerRepository,
        OAuthServiceInterface $oAuthService,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->configuration = $configuration;
        $this->payPalConfiguration = $payPalConfiguration;
        $this->env = $env;
        $this->fundingSourcePresenter = $fundingSourcePresenter;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->oAuthService = $oAuthService;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function buildConfiguration(): array
    {
        $components = [
            'marks',
            'funding-eligibility',
        ];

        if ($this->shouldIncludeButtonsComponent()) {
            $components[] = 'buttons';
        }

        if ($this->shouldIncludeHostedFieldsComponent()) {
            $components[] = 'card-fields';
        }

        if ($this->shouldIncludeMessagesComponent()) {
            $components[] = 'messages';
        }

        if ($this->shouldIncludeGooglePayComponent()) {
            $components[] = 'googlepay';
        }

        if ($this->shouldIncludeApplePayComponent()) {
            $components[] = 'applepay';
        }

        $params = [
            'clientId' => $this->env->getPaypalClientId(),
            'merchantId' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT),
            'currency' => $this->context->getCurrencyIsoCode(),
            'intent' => strtolower($this->configuration->get(PayPalConfiguration::PS_CHECKOUT_INTENT)),
            'commit' => 'order' === $this->getPageName() ? 'true' : 'false',
            'vault' => 'false',
            'integrationDate' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_INTEGRATION_DATE) ?: '2024-04-01',
            'dataPartnerAttributionId' => $this->env->getBnCode(),
            'dataCspNonce' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_CSP_NONCE) ?: '',
        ];

        if (
            $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_VAULTING) &&
            $this->context->getCustomer() &&
            $this->context->getCustomer()->isLogged() &&
            $this->context->getCustomer()->id &&
            'order' === $this->getPageName()
        ) {
            try {
                $payPalCustomerId = $this->payPalCustomerRepository->getPayPalCustomerIdByCustomerId($this->context->getCustomer()->id);
                $merchantId = $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT);

                $userIdToken = $this->oAuthService->getUserIdToken($merchantId, $payPalCustomerId);

                $params['dataUserIdToken'] = $userIdToken;
            } catch (Exception $exception) {
                $this->logger->error('Failed to get PayPal User ID token.', ['exception' => $exception]);
            }
        }

        if ($this->payPalConfiguration->is3dSecureEnabled()) {
            $params['dataEnable3ds'] = 'true';
        }

        if (PayPalConfiguration::MODE_SANDBOX === $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE)) {
            $params['buyerCountry'] = $this->getCountryIsoCode();
        }

        $fundingSourcesDisabled = $this->getFundingSourcesDisabled();

        if (!empty($fundingSourcesDisabled)) {
            $params['disableFunding'] = implode(',', $fundingSourcesDisabled);
        }

        $eligibleAlternativePaymentMethods = $this->getEligibleAlternativePaymentMethods();

        if (!empty($eligibleAlternativePaymentMethods) && $this->shouldIncludeButtonsComponent()) {
            $locale = $this->getLocale();

            if ($locale) {
                $params['locale'] = $locale;
            }

            $components[] = 'payment-fields';
        }

        if ($this->isPayLaterEnabled()) {
            $eligibleAlternativePaymentMethods[] = 'paylater';
        }

        if (!empty($eligibleAlternativePaymentMethods)) {
            $params['enableFunding'] = implode(',', $eligibleAlternativePaymentMethods);
        }

        $params['components'] = implode(',', $components);

        return $params;
    }

    /**
     * @return bool
     */
    private function shouldIncludeButtonsComponent(): bool
    {
        $pageName = $this->getPageName();

        if ('cart' === $pageName
            && (
                $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_ORDER_PAGE) ||
                $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_CHECKOUT_PAGE) ||
                $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CART_PAGE_BUTTON)
            )
        ) {
            return true;
        }

        if ('product' === $pageName
            && (
                $this->configuration->getBoolean(PayPalExpressCheckoutConfiguration::PS_CHECKOUT_EC_PRODUCT_PAGE) ||
                $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BUTTON)
            )
        ) {
            return true;
        }

        return 'order' === $pageName;
    }

    /**
     * @return bool
     */
    private function shouldIncludeHostedFieldsComponent(): bool
    {
        if ('order' !== $this->getPageName() || in_array('card', $this->getFundingSourcesDisabled(), true)) {
            return false;
        }

        return $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_CARD_HOSTED_FIELDS_ENABLED) &&
            in_array($this->configuration->get(PayPalConfiguration::PS_CHECKOUT_CARD_HOSTED_FIELDS_STATUS), ['SUBSCRIBED', 'LIMITED'], true);
    }

    /**
     * @return bool
     */
    private function shouldIncludeMessagesComponent(): bool
    {
        $pageName = $this->getPageName();

        if ('index' === $pageName && $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER)) {
            return true;
        }

        if ('category' === $pageName && $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER)) {
            return true;
        }

        if (
            in_array($pageName, ['cart', 'order']) &&
            (
                $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE) ||
                $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BANNER)
            )
        ) {
            return true;
        }

        if (
            'product' === $pageName &&
            (
                $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE) ||
                $this->configuration->getBoolean(PayPalPayLaterConfiguration::PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE_BANNER)
            )
        ) {
            return true;
        }

        return false;
    }

    private function shouldIncludeGooglePayComponent(): bool
    {
        $countryIso = $this->getCountryIsoCode();
        $fundingSource = $this->fundingSourcePresenter->getOneBy(['name' => 'google_pay', 'id_shop' => $this->context->getShop()->id]);

        return
            $fundingSource &&
            $fundingSource->getIsEnabled() &&
            $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_GOOGLE_PAY) &&
            in_array($countryIso, FundingSourceConstraint::getCountries('google_pay'), true) &&
            in_array($this->context->getCurrencyIsoCode(), FundingSourceConstraint::getCurrencies('google_pay'), true);
    }

    private function shouldIncludeApplePayComponent(): bool
    {
        $countryIso = $this->getCountryIsoCode();
        $fundingSource = $this->fundingSourcePresenter->getOneBy(['name' => 'apple_pay', 'id_shop' => $this->context->getShop()->id]);

        return $fundingSource &&
            $fundingSource->getIsEnabled() &&
            $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_APPLE_PAY) &&
            $this->isApplePayDomainRegistered() &&
            in_array($countryIso, FundingSourceConstraint::getCountries('apple_pay'), true) &&
            in_array($this->context->getCurrencyIsoCode(), FundingSourceConstraint::getCurrencies('apple_pay'), true);
    }

    /**
     * @see https://developer.paypal.com/docs/checkout/reference/customize-sdk/#disable-funding
     *
     * @return array
     */
    private function getFundingSourcesDisabled(): array
    {
        $fundingSourcesDisabled = [];

        $fundingSources = $this->fundingSourcePresenter->getAllForSpecificShop($this->context->getShop()->id);

        foreach ($fundingSources as $fundingSource) {
            if (!$fundingSource->getIsEnabled() && !in_array($fundingSource->getName(), self::NOT_FUNDING_SOURCES, true)) {
                $fundingSourcesDisabled[] = $fundingSource->getName();
            }
        }

        return $fundingSourcesDisabled;
    }

    private function getEligibleAlternativePaymentMethods(): array
    {
        $fundingSourcesEnabled = [];

        $fundingSources = $this->fundingSourcePresenter->getAllForSpecificShop($this->context->getShop()->id);

        $countryIso = $this->getCountryIsoCode();
        $currencyIso = $this->context->getCurrencyIsoCode();

        foreach ($fundingSources as $fundingSource) {
            if (!$fundingSource->getIsEnabled()) {
                continue;
            }

            switch (true) {
                case $fundingSource->getName() === 'bancontact' && $countryIso === 'BE' && $currencyIso === 'EUR':
                case $fundingSource->getName() === 'blik' && $countryIso === 'PL' && $currencyIso === 'PLN':
                case $fundingSource->getName() === 'eps' && $countryIso === 'AT' && $currencyIso === 'EUR':
                case $fundingSource->getName() === 'ideal' && $countryIso === 'NL' && $currencyIso === 'EUR':
                case $fundingSource->getName() === 'mybank' && $countryIso === 'IT' && $currencyIso === 'EUR':
                case $fundingSource->getName() === 'p24' && $countryIso === 'PL' && in_array($currencyIso, ['EUR', 'PLN'], true):
                    $fundingSourcesEnabled[] = $fundingSource->getName();

                    break;
            }
        }

        return $fundingSourcesEnabled;
    }

    private function isApplePayDomainRegistered(): bool
    {
        return $this->configuration->getBoolean(
            $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE) === PayPalConfiguration::MODE_SANDBOX ?
                PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX :
                PayPalConfiguration::PS_CHECKOUT_DOMAIN_REGISTERED_LIVE
        );
    }

    private function isPayLaterEnabled(): bool
    {
        $fundingSource = $this->fundingSourcePresenter->getOneBy(['name' => 'paylater', 'id_shop' => $this->context->getShop()->id]);

        return $fundingSource && $fundingSource->getIsEnabled();
    }

    /**
     * @return string
     */
    private function getPageName(): string
    {
        $controller = $this->context->getController();

        if (empty($controller)) {
            return '';
        }

        if (isset($controller->php_self)) {
            return 'order-opc' === $controller->php_self ? 'order' : $controller->php_self;
        }

        return '';
    }

    /**
     * @return string
     */
    private function getCountryIsoCode(): string
    {
        $code = '';

        if (\Validate::isLoadedObject($this->context->getCountry())) {
            $code = strtoupper($this->context->getCountry()->iso_code);
        }

        $cart = $this->context->getCart();

        if (\Validate::isLoadedObject($cart)) {
            $taxAddressType = $this->configuration->get('PS_TAX_ADDRESS_TYPE');
            $taxAddressId = property_exists($cart, $taxAddressType) ? $cart->{$taxAddressType} : $cart->id_address_delivery;
            $address = new \Address($taxAddressId);
            $country = new \Country($address->id_country);

            if ($country->id && $country->iso_code) {
                $code = strtoupper($country->iso_code);
            }
        }

        if ($code === 'UK') {
            $code = 'GB';
        }

        return $code;
    }

    /**
     * @return string
     */
    private function getLocale(): string
    {
        $countryIso = $this->getCountryIsoCode();
        $languageIso = $this->context->getLanguage() ? strtoupper($this->context->getLanguage()->iso_code) : '';

        switch ($countryIso) {
            case 'DE':
                return $languageIso === 'DE' ? 'de_DE' : 'en_DE';
            case 'US':
                return 'en_US';
            case 'GB':
                return 'en_GB';
            case 'ES':
                return $languageIso === 'ES' ? 'es_ES' : 'en_ES';
            case 'FR':
                return $languageIso === 'FR' ? 'fr_FR' : 'en_FR';
            case 'IT':
                return $languageIso === 'IT' ? 'it_IT' : 'en_IT';
            case 'NL':
                return $languageIso === 'NL' ? 'nl_NL' : 'en_NL';
            case 'PL':
                return $languageIso === 'PL' ? 'pl_PL' : 'en_PL';
            case 'PT':
                return $languageIso === 'PT' ? 'pt_PT' : 'en_PT';
            case 'AU':
                return 'en_AU';
            case 'AT':
                return $languageIso === 'DE' ? 'de_AT' : 'en_AT';
            default:
                return '';
        }
    }
}
