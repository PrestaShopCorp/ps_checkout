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

namespace PrestaShop\Module\PrestashopCheckout\Builder\PayPalSdkLink;

use PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv;
use PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration;
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceConfigurationRepository;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayLaterConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

/**
 * Build sdk link
 */
class PayPalSdkLinkBuilder
{
    const BASE_LINK = 'https://www.paypal.com/sdk/js';

    /**
     * @var PaypalAccountRepository
     */
    private $payPalAccountRepository;

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

    /**
     * @todo To be removed
     *
     * @var bool
     */
    private $isExpressCheckout = false;

    /**
     * @todo To be removed
     *
     * @var bool
     */
    private $isDisplayOnlyHostedFields = false;

    /**
     * @todo To be removed
     *
     * @var bool
     */
    private $isDisplayOnlySmartButtons = false;

    /**
     * @param PaypalAccountRepository $payPalAccountRepository
     * @param PayPalConfiguration $configuration
     * @param PayPalPayLaterConfiguration $payLaterConfiguration
     * @param FundingSourceConfigurationRepository $fundingSourceConfigurationRepository
     * @param ExpressCheckoutConfiguration $expressCheckoutConfiguration
     */
    public function __construct(
        PaypalAccountRepository $payPalAccountRepository,
        PayPalConfiguration $configuration,
        PayPalPayLaterConfiguration $payLaterConfiguration,
        FundingSourceConfigurationRepository $fundingSourceConfigurationRepository,
        ExpressCheckoutConfiguration $expressCheckoutConfiguration
    ) {
        $this->payPalAccountRepository = $payPalAccountRepository;
        $this->configuration = $configuration;
        $this->payLaterConfiguration = $payLaterConfiguration;
        $this->fundingSourceConfigurationRepository = $fundingSourceConfigurationRepository;
        $this->expressCheckoutConfiguration = $expressCheckoutConfiguration;
    }

    /**
     * @todo To be refactored with Service Container and Dependency Injection
     *
     * @return string
     */
    public function buildLink()
    {
        $components = [
            'marks',
            'funding-eligibility',
        ];

        if ($this->shouldIncludeButtonsComponent()) {
            $components[] = 'buttons';
        }

        if ($this->shouldIncludeHostedFieldsComponent()) {
            $components[] = 'hosted-fields';
        }

        if ($this->shouldIncludeMessagesComponent()) {
            $components[] = 'messages';
        }

        $params = [
            'components' => implode(',', $components),
            'client-id' => (new PaypalEnv())->getPaypalClientId(),
            'merchant-id' => $this->payPalAccountRepository->getMerchantId(),
            'currency' => \Context::getContext()->currency->iso_code,
            'intent' => strtolower($this->configuration->getIntent()),
            'commit' => 'order' === $this->getPageName() ? 'true' : 'false',
            'vault' => 'false',
            'integration-date' => $this->configuration->getIntegrationDate(),
        ];

        if ('SANDBOX' === $this->configuration->getPaymentMode()) {
            $params['debug'] = 'true';
//            $params['buyer-country'] = $this->getCountry();
//            $params['locale'] = $this->getLocale();
        }

        $fundingSourcesDisabled = $this->getFundingSourcesDisabled();

        if (false === empty($fundingSourcesDisabled)) {
            $params['disable-funding'] = implode(',', $fundingSourcesDisabled);
        }

        if ($this->isPayLaterEnabled()) {
            $params['enable-funding'] = 'paylater';
        }

        return self::BASE_LINK . '?' . urldecode(http_build_query($params));
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

    /**
     * @todo To be removed
     */
    public function enableDisplayExpressCheckout()
    {
        $this->isExpressCheckout = true;
    }

    /**
     * @todo To be removed
     */
    public function enableDisplayOnlyHostedFields()
    {
        $this->isDisplayOnlyHostedFields = true;
    }

    /**
     * @todo To be removed
     */
    public function enableDisplayOnlySmartButtons()
    {
        $this->isDisplayOnlySmartButtons = true;
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
        if ('order' !== $this->getPageName()) {
            return false;
        }

        return $this->payPalAccountRepository->cardHostedFieldsIsAvailable();
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
     * @todo Used only on sandbox, to be removed when CountryProvider will be available or provide a way to use a ENV value
     *
     * @return string
     */
    private function getCountry()
    {
        $context = \Context::getContext();
        $code = '';

        if (\Validate::isLoadedObject($context->cart) && $context->cart->id_address_invoice) {
            $address = new \Address($context->cart->id_address_invoice);
            $country = new \Country($address->id_country);

            $code = strtoupper($country->iso_code);
        }

        if (\Validate::isLoadedObject($context->country)) {
            $code = strtoupper($context->country->iso_code);
        }

        if ($code === 'UK') {
            $code = 'GB';
        }

        return $code;
    }

    /**
     * @todo Used only on sandbox, to be removed when LanguageProvider will be available or provide a way to use a ENV value
     *
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
     * @todo Used only on sandbox, to be removed when LocaleProvider will be available or provide a way to use a ENV value
     *
     * @return string
     */
    private function getLocale()
    {
        if ('DE' === $this->getCountry()) {
            return 'DE' === $this->getLanguage() ? 'de_DE' : 'en_DE';
        }

        if ('US' === $this->getCountry()) {
            return 'en_US';
        }

        if ('GB' === $this->getCountry()) {
            return 'en_GB';
        }

        if ('ES' === $this->getCountry()) {
            return 'ES' === $this->getLanguage() ? 'es_ES' : 'en_ES';
        }

        if ('FR' === $this->getCountry()) {
            return 'FR' === $this->getLanguage() ? 'fr_FR' : 'en_FR';
        }

        if ('IT' === $this->getCountry()) {
            return 'IT' === $this->getLanguage() ? 'it_IT' : 'en_IT';
        }

        if ('NL' === $this->getCountry()) {
            return 'NL' === $this->getLanguage() ? 'nl_NL' : 'en_NL';
        }

        if ('PL' === $this->getCountry()) {
            return 'PL' === $this->getLanguage() ? 'pl_PL' : 'en_PL';
        }

        if ('PT' === $this->getCountry()) {
            return 'PT' === $this->getLanguage() ? 'pt_PT' : 'en_PT';
        }

        if ('AU' === $this->getCountry()) {
            return 'en_AU';
        }

        return '';
    }
}
