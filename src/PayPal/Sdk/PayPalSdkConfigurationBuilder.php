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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Sdk;

use Exception;
use PrestaShop\Module\PrestashopCheckout\CommandBus\CommandBusInterface;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\Environment\Env;
use PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceConfigurationRepository;
use PrestaShop\Module\PrestashopCheckout\PayPal\OAuth\Query\GetPayPalGetUserIdTokenQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\OAuth\Query\GetPayPalGetUserIdTokenQueryResult;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayLaterConfiguration;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use Psr\Log\LoggerInterface;

/**
 * Build sdk link
 */
class PayPalSdkConfigurationBuilder
{
    /**
     * @var PayPalConfiguration
     */
    private $configuration;

    /**
     * @var PayPalPayLaterConfiguration
     */
    private $payLaterConfiguration;

    /**
     * @var FundingSourceConfigurationRepository
     */
    private $fundingSourceConfigurationRepository;

    /** @var ExpressCheckoutConfiguration */
    private $expressCheckoutConfiguration;

    /** @var ShopContext */
    private $shopContext;

    /** @var array */
    private static $cache = [];
    /**
     * @var CommandBusInterface
     */
    private $commandBus;
    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Env
     */
    private $env;

    /**
     * @param \Ps_checkout $module
     * @param PayPalConfiguration $configuration
     * @param PayPalPayLaterConfiguration $payLaterConfiguration
     * @param FundingSourceConfigurationRepository $fundingSourceConfigurationRepository
     * @param ExpressCheckoutConfiguration $expressCheckoutConfiguration
     * @param ShopContext $shopContext
     * @param PrestaShopContext $prestaShopContext
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Ps_checkout $module,
        Env $env,
        PayPalConfiguration $configuration,
        PayPalPayLaterConfiguration $payLaterConfiguration,
        FundingSourceConfigurationRepository $fundingSourceConfigurationRepository,
        ExpressCheckoutConfiguration $expressCheckoutConfiguration,
        ShopContext $shopContext,
        PrestaShopContext $prestaShopContext,
        LoggerInterface $logger
    ) {
        $this->configuration = $configuration;
        $this->payLaterConfiguration = $payLaterConfiguration;
        $this->fundingSourceConfigurationRepository = $fundingSourceConfigurationRepository;
        $this->expressCheckoutConfiguration = $expressCheckoutConfiguration;
        $this->shopContext = $shopContext;
        $this->commandBus = $module->getService('ps_checkout.bus.command');
        $this->prestaShopContext = $prestaShopContext;
        $this->logger = $logger;
        $this->env = $env;
    }

    /**
     * @return array
     */
    public function buildConfiguration()
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

        $params = [
            'clientId' => $this->env->getPaypalClientId(),
            'merchantId' => $this->configuration->getMerchantId(),
            'currency' => $this->prestaShopContext->getCurrencyIsoCode(),
            'intent' => strtolower($this->configuration->getIntent()),
            'commit' => 'order' === $this->getPageName() ? 'true' : 'false',
            'vault' => 'false',
            'integrationDate' => $this->configuration->getIntegrationDate(),
            'dataPartnerAttributionId' => $this->shopContext->getBnCode(),
            'dataCspNonce' => $this->configuration->getCSPNonce(),
        ];

        if ($this->configuration->isVaultingEnabled() && $this->prestaShopContext->customerIsLogged() && $this->prestaShopContext->getCustomerId() && 'order' === $this->getPageName()) {
            try {
                /** @var GetPayPalGetUserIdTokenQueryResult $queryResult */
                $queryResult = $this->commandBus->handle(new GetPayPalGetUserIdTokenQuery(new CustomerId($this->prestaShopContext->getCustomerId())));
                $params['dataUserIdToken'] = $queryResult->getUserIdToken();
            } catch (Exception $exception) {
                $this->logger->error('Failed to get PayPal User ID token.', ['exception' => $exception]);
            }
        }

        if ($this->configuration->is3dSecureEnabled()) {
            $params['dataEnable3ds'] = 'true';
        }

        if ('SANDBOX' === $this->configuration->getPaymentMode()) {
//            $params['debug'] = 'true';
            $params['buyerCountry'] = $this->getCountry();
        }

        $fundingSourcesDisabled = $this->getFundingSourcesDisabled();

        if (false === empty($fundingSourcesDisabled)) {
            $params['disableFunding'] = implode(',', $fundingSourcesDisabled);
        }

        $eligibleAlternativePaymentMethods = $this->getEligibleAlternativePaymentMethods();

        if (false === empty($eligibleAlternativePaymentMethods) && $this->shouldIncludeButtonsComponent()) {
            $params['locale'] = $this->getLocale();
            $components[] = 'payment-fields';
        }

        if (isset($params['locale']) && empty($params['locale'])) {
            unset($params['locale']);
        }

        if ($this->isPayLaterEnabled()) {
            $eligibleAlternativePaymentMethods[] = 'paylater';
        }

        if (false === empty($eligibleAlternativePaymentMethods)) {
            $params['enableFunding'] = implode(',', $eligibleAlternativePaymentMethods);
        }

        $params['components'] = implode(',', $components);

        return $params;
    }

    /**
     * @see https://developer.paypal.com/docs/checkout/reference/customize-sdk/#disable-funding
     *
     * @return array
     */
    private function getFundingSourcesDisabled()
    {
        $fundingSourcesDisabled = [];

        $fundingSources = $this->fundingSourceConfigurationRepository->getAll();

        if (empty($fundingSources)) {
            return $fundingSourcesDisabled;
        }

        foreach ($fundingSources as $fundingSource) {
            if (!$fundingSource['active']) {
                $fundingSourcesDisabled[] = $fundingSource['name'];
            }
        }

        return $fundingSourcesDisabled;
    }

    private function getPageName()
    {
        $controller = \Context::getContext()->controller;

        if (empty($controller)) {
            return '';
        }

        if (isset($controller->php_self)) {
            return 'order-opc' === $controller->php_self ? 'order' : $controller->php_self;
        }

        return '';
    }

    private function isPayLaterEnabled()
    {
        $payLaterConfig = $this->fundingSourceConfigurationRepository->get('paylater');

        return $payLaterConfig === null || !empty($payLaterConfig) && (int) $payLaterConfig['active'] === 1;
    }

    /**
     * @return bool
     */
    private function shouldIncludeButtonsComponent()
    {
        if ('cart' === $this->getPageName()
            && (
                $this->expressCheckoutConfiguration->isOrderPageEnabled()
                || $this->expressCheckoutConfiguration->isCheckoutPageEnabled()
                || $this->payLaterConfiguration->isCartPageButtonActive()
            )
        ) {
            return true;
        }

        if ('product' === $this->getPageName()
            && (
                $this->expressCheckoutConfiguration->isProductPageEnabled()
                || $this->payLaterConfiguration->isProductPageButtonActive()
            )
        ) {
            return true;
        }

        return 'order' === $this->getPageName();
    }

    /**
     * @return bool
     */
    private function shouldIncludeHostedFieldsComponent()
    {
        if ('order' !== $this->getPageName() || in_array('card', $this->getFundingSourcesDisabled(), true)) {
            return false;
        }

        return $this->configuration->isHostedFieldsEnabled() && in_array($this->configuration->getCardHostedFieldsStatus(), ['SUBSCRIBED', 'LIMITED'], true);
    }

    /**
     * @return bool
     */
    private function shouldIncludeMessagesComponent()
    {
        if ('index' === $this->getPageName() && $this->payLaterConfiguration->isHomePageBannerActive()) {
            return true;
        }

        if ('category' === $this->getPageName() && $this->payLaterConfiguration->isCategoryPageBannerActive()) {
            return true;
        }

        if ('cart' === $this->getPageName() && ($this->payLaterConfiguration->isOrderPageMessageActive() || $this->payLaterConfiguration->isOrderPageBannerActive())) {
            return true;
        }

        if ('order' === $this->getPageName() && ($this->payLaterConfiguration->isOrderPageMessageActive() || $this->payLaterConfiguration->isOrderPageBannerActive())) {
            return true;
        }

        if ('product' === $this->getPageName() && ($this->payLaterConfiguration->isProductPageMessageActive() || $this->payLaterConfiguration->isProductPageBannerActive())) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    private function getCountry()
    {
        $context = \Context::getContext();
        $code = '';
        $taxAddressType = \Configuration::get('PS_TAX_ADDRESS_TYPE');

        if (\Validate::isLoadedObject($context->country)) {
            $code = strtoupper($context->country->iso_code);
        }

        if (\Validate::isLoadedObject($context->cart)) {
            $taxAddressId = property_exists($context->cart, $taxAddressType) ? $context->cart->{$taxAddressType} : $context->cart->id_address_delivery;
            $address = new \Address($taxAddressId);
            $country = new \Country($address->id_country);

            $code = \Validate::isLoadedObject($country) ? strtoupper($country->iso_code) : $code;
        }

        if ($code === 'UK') {
            $code = 'GB';
        }

        return $code;
    }

    /**
     * @return string
     */
    private function getLanguage()
    {
        $context = \Context::getContext();
        $code = '';

        if (\Validate::isLoadedObject($context->language)) {
            $code = strtoupper($context->language->iso_code);
        }

        return $code;
    }

    /**
     * @return string
     */
    private function getLocale()
    {
        $country = $this->getCountry();
        $language = $this->getLanguage();

        if ('DE' === $country) {
            return 'DE' === $language ? 'de_DE' : 'en_DE';
        }

        if ('US' === $country) {
            return 'en_US';
        }

        if ('GB' === $country) {
            return 'en_GB';
        }

        if ('ES' === $country) {
            return 'ES' === $language ? 'es_ES' : 'en_ES';
        }

        if ('FR' === $country) {
            return 'FR' === $language ? 'fr_FR' : 'en_FR';
        }

        if ('IT' === $country) {
            return 'IT' === $language ? 'it_IT' : 'en_IT';
        }

        if ('NL' === $country) {
            return 'NL' === $language ? 'nl_NL' : 'en_NL';
        }

        if ('PL' === $country) {
            return 'PL' === $language ? 'pl_PL' : 'en_PL';
        }

        if ('PT' === $country) {
            return 'PT' === $language ? 'pt_PT' : 'en_PT';
        }

        if ('AU' === $country) {
            return 'en_AU';
        }

        if ('AT' === $country) {
            return 'DE' === $language ? 'de_AT' : 'en_AT';
        }

        return '';
    }

    private function getEligibleAlternativePaymentMethods()
    {
        $fundingSourcesEnabled = [];

        $fundingSources = $this->fundingSourceConfigurationRepository->getAll();

        if (empty($fundingSources)) {
            return $fundingSourcesEnabled;
        }

        $context = \Context::getContext();
        $country = $this->getCountry();

        foreach ($fundingSources as $fundingSource) {
            if ($fundingSource['active']
                && $fundingSource['name'] === 'bancontact'
                && $country === 'BE'
                && $context->currency->iso_code === 'EUR'
            ) {
                $fundingSourcesEnabled[] = $fundingSource['name'];
            }
            if ($fundingSource['active']
                && $fundingSource['name'] === 'blik'
                && $country === 'PL'
                && $context->currency->iso_code === 'PLN'
            ) {
                $fundingSourcesEnabled[] = $fundingSource['name'];
            }
            if ($fundingSource['active']
                && $fundingSource['name'] === 'eps'
                && $country === 'AT'
                && $context->currency->iso_code === 'EUR'
            ) {
                $fundingSourcesEnabled[] = $fundingSource['name'];
            }
            if ($fundingSource['active']
                && $fundingSource['name'] === 'giropay'
                && $country === 'DE'
                && $context->currency->iso_code === 'EUR'
            ) {
                $fundingSourcesEnabled[] = $fundingSource['name'];
            }
            if ($fundingSource['active']
                && $fundingSource['name'] === 'ideal'
                && $country === 'NL'
                && $context->currency->iso_code === 'EUR'
            ) {
                $fundingSourcesEnabled[] = $fundingSource['name'];
            }
            if ($fundingSource['active']
                && $fundingSource['name'] === 'mybank'
                && $country === 'IT'
                && $context->currency->iso_code === 'EUR'
            ) {
                $fundingSourcesEnabled[] = $fundingSource['name'];
            }
            if ($fundingSource['active']
                && $fundingSource['name'] === 'p24'
                && $country === 'PL'
                && in_array($context->currency->iso_code, ['EUR', 'PLN'], true)
            ) {
                $fundingSourcesEnabled[] = $fundingSource['name'];
            }
        }

        return $fundingSourcesEnabled;
    }
}
