<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Builder\PayPalSdkLink;

use PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
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
     */
    public function __construct(PaypalAccountRepository $payPalAccountRepository, PayPalConfiguration $configuration)
    {
        $this->payPalAccountRepository = $payPalAccountRepository;
        $this->configuration = $configuration;
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

        if (false === $this->payPalAccountRepository->isCreditOrDebitCardsEnabled()) {
            $fundingSourcesDisabled[] = 'card';
        }

        if (false === $this->payPalAccountRepository->isPayPalCreditEnabled()) {
            $fundingSourcesDisabled[] = 'credit';
        }

        if (false === $this->payPalAccountRepository->isVenmoEnabled()) {
            $fundingSourcesDisabled[] = 'venmo';
        }

        if (false === $this->payPalAccountRepository->isSepaLastschriftEnabled()) {
            $fundingSourcesDisabled[] = 'sepa';
        }

        if (false === $this->payPalAccountRepository->isBancontactEnabled()) {
            $fundingSourcesDisabled[] = 'bancontact';
        }

        if (false === $this->payPalAccountRepository->isEpsEnabled()) {
            $fundingSourcesDisabled[] = 'eps';
        }

        if (false === $this->payPalAccountRepository->isGiropayEnabled()) {
            $fundingSourcesDisabled[] = 'giropay';
        }

        if (false === $this->payPalAccountRepository->isIdealEnabled()) {
            $fundingSourcesDisabled[] = 'ideal';
        }

        if (false === $this->payPalAccountRepository->isMyBankEnabled()) {
            $fundingSourcesDisabled[] = 'mybank';
        }

        if (false === $this->payPalAccountRepository->isPrzelewy24Enabled()) {
            $fundingSourcesDisabled[] = 'p24';
        }

        if (false === $this->payPalAccountRepository->isSofortEnabled()) {
            $fundingSourcesDisabled[] = 'sofort';
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
