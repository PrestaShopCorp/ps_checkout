<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Builder\PayPalSdkLink;

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
     * @var bool
     */
    private $isExpressCheckout = false;

    /**
     * @var bool
     */
    private $isDisplayOnlyHostedFields = false;

    /**
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
        $components = [];

        if ($this->isHostedFieldsEnabled()) {
            $components[] = 'hosted-fields';
        }

        if ($this->isSmartButtonsEnabled()) {
            $components[] = 'buttons';
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
        ];

        $fundingSourcesDisabled = $this->getFundingSourcesDisabled();

        if ($this->isSmartButtonsEnabled() && false === empty($fundingSourcesDisabled)) {
            $params['disable-funding'] = implode(',', $this->getFundingSourcesDisabled());
        }

        return self::BASE_LINK . '?' . urldecode(http_build_query($params));
    }

    public function enableDisplayExpressCheckout()
    {
        $this->isExpressCheckout = true;
    }

    public function enableDisplayOnlyHostedFields()
    {
        $this->isDisplayOnlyHostedFields = true;
    }

    public function enableDisplayOnlySmartButtons()
    {
        $this->isDisplayOnlySmartButtons = true;
    }

    /**
     * @return bool
     */
    private function isSmartButtonsEnabled()
    {
        return false === $this->isDisplayOnlyHostedFields
            && $this->payPalAccountRepository->paypalPaymentMethodIsValid();
    }

    /**
     * @return bool
     */
    private function isHostedFieldsEnabled()
    {
        return false === $this->isDisplayOnlySmartButtons
            && false === $this->isExpressCheckout
            && $this->payPalAccountRepository->cardHostedFieldsIsAvailable();
    }

    /**
     * @see https://developer.paypal.com/docs/checkout/reference/customize-sdk/#disable-funding
     *
     * @return array
     */
    private function getFundingSourcesDisabled()
    {
        $fundingSourcesDisabled = [];

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isCreditOrDebitCardsEnabled()) {
            $fundingSourcesDisabled[] = 'card';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isPayPalCreditEnabled()) {
            $fundingSourcesDisabled[] = 'credit';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isVenmoEnabled()) {
            $fundingSourcesDisabled[] = 'venmo';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isSepaLastschriftEnabled()) {
            $fundingSourcesDisabled[] = 'sepa';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isBancontactEnabled()) {
            $fundingSourcesDisabled[] = 'bancontact';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isEpsEnabled()) {
            $fundingSourcesDisabled[] = 'eps';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isGiropayEnabled()) {
            $fundingSourcesDisabled[] = 'giropay';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isIdealEnabled()) {
            $fundingSourcesDisabled[] = 'ideal';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isMyBankEnabled()) {
            $fundingSourcesDisabled[] = 'mybank';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isPrzelewy24Enabled()) {
            $fundingSourcesDisabled[] = 'p24';
        }

        if (true === $this->isExpressCheckout || false === $this->payPalAccountRepository->isSofortEnabled()) {
            $fundingSourcesDisabled[] = 'sofort';
        }

        return $fundingSourcesDisabled;
    }
}
