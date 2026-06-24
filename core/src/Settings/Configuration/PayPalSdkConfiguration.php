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
use PsCheckout\Core\FundingSource\Eligibility\FundingSourceEligibilityServiceInterface;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;
use PsCheckout\Core\PayPal\OAuth\OAuthServiceInterface;
use PsCheckout\Core\PayPal\Order\Configuration\PayPalOrderIntent;
use PsCheckout\Core\Util\CountryResolverInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Environment\EnvInterface;
use PsCheckout\Presentation\Presenter\FundingSource\FundingSourcePresenterInterface;
use Psr\Log\LoggerInterface;

class PayPalSdkConfiguration
{
    const SDK_BO_ENDPOINT = '/sdk/ps_checkout-bo-sdk.umd.js';

    const SDK_FO_ENDPOINT = '/sdk/ps_checkout-fo-sdk.js';

    const SDK_MERCHANT_ENDPOINT = '/sdk/ps_checkout-merchant-sdk.umd.js';

    /**
     * google_pay, apple_pay and pui are not considered funding sources
     * and passing these values to disableFunding will crash PayPal SDK
     */
    const NOT_FUNDING_SOURCES = ['google_pay', 'apple_pay', 'pay_upon_invoice'];

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
     * @var FundingSourceEligibilityServiceInterface
     */
    private $eligibilityService;

    /**
     * @var CountryResolverInterface
     */
    private $countryResolver;

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
        FundingSourceEligibilityServiceInterface $eligibilityService,
        CountryResolverInterface $countryResolver,
        PayPalCustomerRepositoryInterface $payPalCustomerRepository,
        OAuthServiceInterface $oAuthService,
        LoggerInterface $logger,
        PayPalPayLaterConfiguration $payPalPayLaterConfiguration
    ) {
        $this->context = $context;
        $this->configuration = $configuration;
        $this->payPalConfiguration = $payPalConfiguration;
        $this->env = $env;
        $this->fundingSourcePresenter = $fundingSourcePresenter;
        $this->eligibilityService = $eligibilityService;
        $this->countryResolver = $countryResolver;
        $this->payPalCustomerRepository = $payPalCustomerRepository;
        $this->oAuthService = $oAuthService;
        $this->logger = $logger;
        $this->payPalPayLaterConfiguration = $payPalPayLaterConfiguration;
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

        $intent = $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_INTENT) ?: PayPalOrderIntent::CAPTURE;

        $params = [
            'clientId' => $this->env->getPaypalClientId(),
            'merchantId' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT),
            'currency' => $this->context->getCurrencyIsoCode(),
            'intent' => strtolower($intent),
            'commit' => 'order' === $this->getPageName() ? 'true' : 'false',
            'vault' => 'false',
            'integrationDate' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_INTEGRATION_DATE) ?: '2024-04-01',
            'dataPartnerAttributionId' => $this->env->getBnCode(),
            'dataCspNonce' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_CSP_NONCE) ?: '',
        ];

        $customer = $this->context->getCustomer();
        if (
            $this->configuration->getBoolean(PayPalConfiguration::PS_CHECKOUT_VAULTING)
            && $customer && $customer->isLogged() && $customer->id
            && 'order' === $this->getPageName()
        ) {
            try {
                $payPalCustomerId = $this->payPalCustomerRepository->getPayPalCustomerIdByCustomerId($customer->id);
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
            $params['buyerCountry'] = $this->countryResolver->getBuyerCountryIsoCode();
        }

        $fundingSourcesDisabled = $this->getFundingSourcesDisabled();

        if (!empty($fundingSourcesDisabled)) {
            $params['disableFunding'] = implode(',', $fundingSourcesDisabled);
        }

        $eligibleAlternativePaymentMethods = $this->eligibilityService->getEligibleFundingSources();

        if (array_key_exists('google_pay', $eligibleAlternativePaymentMethods)) {
            unset($eligibleAlternativePaymentMethods['google_pay']);
            $components[] = 'googlepay';
        }

        if (array_key_exists('apple_pay', $eligibleAlternativePaymentMethods)) {
            unset($eligibleAlternativePaymentMethods['apple_pay']);
            $components[] = 'applepay';
        }

        if (array_key_exists('pay_upon_invoice', $eligibleAlternativePaymentMethods)) {
            unset($eligibleAlternativePaymentMethods['pay_upon_invoice']);
            $components[] = 'legal';
        }

        if (!empty($eligibleAlternativePaymentMethods) && $this->shouldIncludeButtonsComponent()) {
            $locale = $this->getLocale();

            if ($locale) {
                $params['locale'] = $locale;
            }

            $components[] = 'payment-fields';
        }

        if (!empty($eligibleAlternativePaymentMethods)) {
            $params['enableFunding'] = implode(',', array_map(static function ($fundingSource) {
                return $fundingSource->getName();
            }, $eligibleAlternativePaymentMethods));
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

        switch ($pageName) {
            case 'cart':
            case 'category':
            case 'product':
                return $this->payPalPayLaterConfiguration->isPayLaterMessagingEnabled($pageName);
            case 'order':
                return $this->payPalPayLaterConfiguration->isPayLaterMessagingEnabled('checkout');
            case 'index':
                return $this->payPalPayLaterConfiguration->isPayLaterMessagingEnabled('homepage');
            default:
                return false;
        }
    }

    /**
     * @see https://developer.paypal.com/sdk/js/configuration/#disable-funding
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
    private function getLocale(): string
    {
        $countryIso = $this->countryResolver->getBuyerCountryIsoCode();
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
            case 'CA':
                return $languageIso === 'FR' ? 'fr_CA' : 'en_CA';
            default:
                return '';
        }
    }
}
