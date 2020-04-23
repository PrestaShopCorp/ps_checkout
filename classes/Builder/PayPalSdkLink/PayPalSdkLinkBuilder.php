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
        $language = (new LanguageAdapter())->getLanguage(\Context::getContext()->language->id);

        $components = [];

        if ($this->isHostedFieldsEnabled()) {
            $components[] = 'hosted-fields';
        }

        if ($this->isSmartButtonsEnabled()) {
            $components[] = 'buttons';
        }

        $disabledFundings = [];

        if ($this->isSmartButtonsEnabled() && $this->isSmartButtonsCardFundingDisabled()) {
            $disabledFundings[] = 'card';
        }

        $params = [
            'components' => implode(',', $components),
            'client-id' => (new PaypalEnv())->getPaypalClientId(),
            'merchant-id' => (new PaypalAccountRepository())->getMerchantId(),
            'locale' => $language['locale'],
            'currency' => \Context::getContext()->currency->iso_code,
            'intent' => strtolower(\Configuration::get(
                'PS_CHECKOUT_INTENT',
                null,
                null,
                (int) \Context::getContext()->shop->id
            )),
        ];

        if (false === empty($disabledFundings)) {
            $params['disable-funding'] = implode(',', $disabledFundings);
        }

        return self::BASE_LINK . '?' . urldecode(http_build_query($params));
    }

    public function displayExpressCheckout()
    {
        $this->isExpressCheckout = true;
    }

    public function displayOnlyHostedFields()
    {
        $this->isDisplayOnlyHostedFields = true;
    }

    public function displayOnlySmartButtons()
    {
        $this->isDisplayOnlySmartButtons = true;
    }

    /**
     * @return bool
     */
    private function isSmartButtonsCardFundingDisabled()
    {
        return true === $this->isExpressCheckout
            || true === $this->isDisplayOnlySmartButtons
            || true === $this->payPalAccountRepository->cardHostedFieldsIsAvailable();
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
}
