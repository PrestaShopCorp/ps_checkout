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

use PrestaShop\Module\PrestashopCheckout\Adapter\LanguageAdapter;
use PrestaShop\Module\PrestashopCheckout\Environment\PaypalEnv;
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
     */
    public function __construct()
    {
        $this->payPalAccountRepository = new PaypalAccountRepository();
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
            'merchant-id' => (new PaypalAccountRepository())->getMerchantId(),
            'currency' => \Context::getContext()->currency->iso_code,
            'intent' => strtolower(\Configuration::get(
                'PS_CHECKOUT_INTENT',
                null,
                null,
                (int) \Context::getContext()->shop->id
            )),
            'commit' => 'order' === $this->getPageName() ? 'true' : 'false',
            'vault' => 'false',
            'integration-date' => \Configuration::get('PS_CHECKOUT_INTEGRATION_DATE'),
        ];

        if ('SANDBOX' === \Configuration::get('PS_CHECKOUT_MODE')) {
            $params['debug'] = 'true';
            $params['buyer-country'] = \Context::getContext()->country->iso_code;
            $language = (new LanguageAdapter())->getLanguage((int) \Context::getContext()->language->id);
            $params['locale'] = $language['locale'];
        }

        $fundingSourcesDisabled = $this->getFundingSourcesDisabled();

        if (false === empty($fundingSourcesDisabled)) {
            $params['disable-funding'] = implode(',', $fundingSourcesDisabled);
        }

        $cardsDisabled = $this->getCardsDisabled();

        if (false === empty($cardsDisabled)) {
            $params['disable-card'] = implode(',', $cardsDisabled);
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
     * @see https://developer.paypal.com/docs/business/checkout/reference/javascript-sdk/#disable-card
     *
     * @return array
     */
    private function getCardsDisabled()
    {
        $cardsDisabled = [];

        if (false === $this->payPalAccountRepository->isCardVisaEnabled()) {
            $cardsDisabled[] = 'visa';
        }

        if (false === $this->payPalAccountRepository->isMasterCardEnabled()) {
            $cardsDisabled[] = 'mastercard';
        }

        if (false === $this->payPalAccountRepository->isCardAmexEnabled()) {
            $cardsDisabled[] = 'amex';
        }

        if (false === $this->payPalAccountRepository->isCardDiscoverEnabled()) {
            $cardsDisabled[] = 'discover';
        }

        if (false === $this->payPalAccountRepository->isCardJcbEnabled()) {
            $cardsDisabled[] = 'jcb';
        }

        if (false === $this->payPalAccountRepository->isCardEloEnabled()) {
            $cardsDisabled[] = 'elo';
        }

        if (false === $this->payPalAccountRepository->isCardHiperEnabled()) {
            $cardsDisabled[] = 'hiper';
        }

        return $cardsDisabled;
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
