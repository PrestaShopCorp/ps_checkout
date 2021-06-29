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
use PrestaShop\Module\PrestashopCheckout\FundingSource\FundingSourceConfigurationRepository;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalPayIn4XConfiguration;
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
     * @var PayPalPayIn4XConfiguration
     */
    private $payIn4XConfiguration;

    /**
     * @var FundingSourceConfigurationRepository
     */
    private $fundingSourceConfigurationRepository;

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
     * @todo To be refactored with Service Container and Dependency Injection
     *
     * @param PaypalAccountRepository $payPalAccountRepository
     * @param PayPalConfiguration $configuration
     * @param PayPalPayIn4XConfiguration $payIn4XConfiguration
     * @param FundingSourceConfigurationRepository $fundingSourceConfigurationRepository
     */
    public function __construct(
        PaypalAccountRepository $payPalAccountRepository,
        PayPalConfiguration $configuration,
        PayPalPayIn4XConfiguration $payIn4XConfiguration,
        FundingSourceConfigurationRepository $fundingSourceConfigurationRepository
    ) {
        $this->payPalAccountRepository = $payPalAccountRepository;
        $this->configuration = $configuration;
        $this->payIn4XConfiguration = $payIn4XConfiguration;
        $this->fundingSourceConfigurationRepository = $fundingSourceConfigurationRepository;
    }

    /**
     * @todo To be refactored with Service Container and Dependency Injection
     *
     * @return string
     */
    public function buildLink()
    {
        $components = [
            'buttons',
            'marks',
            'funding-eligibility',
        ];

        if ($this->payIn4XConfiguration->isOrderPageEnabled()
            || $this->payIn4XConfiguration->isProductPageEnabled()
        ) {
            $components[] = 'messages';
        }

        if ($this->payPalAccountRepository->cardHostedFieldsIsAvailable()) {
            $components[] = 'hosted-fields';
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
            // $params['buyer-country'] = \Context::getContext()->country->iso_code;
            // $params['locale'] = 'es_ES'; //@todo retrieve locale from PayPalContext
        }

        $fundingSourcesDisabled = $this->getFundingSourcesDisabled();

        if (false === empty($fundingSourcesDisabled)) {
            $params['disable-funding'] = implode(',', $fundingSourcesDisabled);
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
            return $controller->php_self;
        }

        return '';
    }
}
