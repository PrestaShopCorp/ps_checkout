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

namespace PrestaShop\Module\PrestashopCheckout\Repository;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Entity\PaypalAccount;
use PrestaShop\Module\PrestashopCheckout\Updater\PaypalAccountUpdater;

/**
 * Repository for PaypalAccount
 */
class PaypalAccountRepository
{
    /** @var PrestaShopConfiguration */
    private $configuration;

    /**
     * @param PrestaShopConfiguration $configuration
     */
    public function __construct(PrestaShopConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get the current paypal account onboarded
     *
     * @return PaypalAccount
     */
    public function getOnboardedAccount()
    {
        return new PaypalAccount(
            $this->getMerchantId(),
            $this->getMerchantEmail(),
            $this->getMerchantEmailStatus(),
            $this->getPaypalPaymentStatus(),
            $this->getCardHostedFieldsStatus(),
            $this->getMerchantCountry()
        );
    }

    /**
     * Get the status of the paypal onBoarding
     *
     * @return bool
     */
    public function onBoardingIsCompleted()
    {
        return !empty($this->getMerchantId());
    }

    /**
     * Check if the paypal email was confirmed or not
     *
     * @return bool
     */
    public function paypalEmailIsValid()
    {
        return (bool) $this->getMerchantEmailStatus();
    }

    /**
     * Get if the payment method by hosted fields is enabled for the current merchant
     *
     * @return bool
     */
    public function cardHostedFieldsIsAllowed()
    {
        $cardStatus = $this->getCardHostedFieldsStatus();

        return $cardStatus === PaypalAccountUpdater::SUBSCRIBED || $cardStatus === PaypalAccountUpdater::LIMITED;
    }

    /**
     * Get if hosted fields are enabled by Paypal and merchant
     *
     * @return bool
     */
    public function cardHostedFieldsIsAvailable()
    {
        return $this->isHostedFieldsEnabled()
            && $this->cardHostedFieldsIsAllowed();
    }

    /**
     * Get if the payment method by paypal is enabled for the current merchant
     *
     * @return bool
     */
    public function paypalPaymentMethodIsValid()
    {
        return (bool) $this->getPaypalPaymentStatus();
    }

    /**
     * Get the merchant id for the current onboarded merchant
     *
     * @return string|bool
     */
    public function getMerchantId()
    {
        return $this->configuration->get(PaypalAccount::PS_CHECKOUT_PAYPAL_ID_MERCHANT);
    }

    /**
     * Get the merchant email
     *
     * @return string|bool
     */
    public function getMerchantEmail()
    {
        return $this->configuration->get(PaypalAccount::PS_CHECKOUT_PAYPAL_EMAIL_MERCHANT);
    }

    /**
     * Get the merchant country ISO code
     *
     * @return string|bool
     */
    public function getMerchantCountry()
    {
        return $this->configuration->get(PaypalAccount::PS_CHECKOUT_PAYPAL_COUNTRY_MERCHANT);
    }

    /**
     * Get the merchant email status
     *
     * @return string|bool
     */
    public function getMerchantEmailStatus()
    {
        return $this->configuration->get(PaypalAccount::PS_CHECKOUT_PAYPAL_EMAIL_STATUS);
    }

    /**
     * Get the paypal payment method status for the current merchant
     *
     * @return string|bool
     */
    public function getPaypalPaymentStatus()
    {
        return $this->configuration->get(PaypalAccount::PS_CHECKOUT_PAYPAL_PAYMENT_STATUS);
    }

    /**
     * Get the card payment status for the current merchant
     *
     * @return string
     */
    public function getCardHostedFieldsStatus()
    {
        return $this->configuration->get(PaypalAccount::PS_CHECKOUT_CARD_HOSTED_FIELDS_STATUS);
    }

    /**
     * Merchant can disable hosted fields in module configuration
     *
     * @return bool
     */
    public function isHostedFieldsEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_CARD_HOSTED_FIELDS_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCreditOrDebitCardsEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_CARDS_ENABLED);
    }

    /**
     * @return bool
     */
    public function isPayPalCreditEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_PAYPAL_CREDIT_ENABLED);
    }

    /**
     * @return bool
     */
    public function isVenmoEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_VENMO_ENABLED);
    }

    /**
     * @return bool
     */
    public function isSepaLastschriftEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_SEPA_LASTSCHRIFT_CREDIT_ENABLED);
    }

    /**
     * @return bool
     */
    public function isBancontactEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_BANCONTACT_ENABLED);
    }

    /**
     * @return bool
     */
    public function isEpsEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_EPS_ENABLED);
    }

    /**
     * @return bool
     */
    public function isGiropayEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_GIROPAY_ENABLED);
    }

    /**
     * @return bool
     */
    public function isIdealEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_IDEAL_ENABLED);
    }

    /**
     * @return bool
     */
    public function isMyBankEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_MYBANK_ENABLED);
    }

    /**
     * @return bool
     */
    public function isPrzelewy24Enabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_PRZELEWY24_ENABLED);
    }

    /**
     * @return bool
     */
    public function isSofortEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_FUNDING_SOURCE_SOFORT_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCardVisaEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_CARD_VISA_ENABLED);
    }

    /**
     * @return bool
     */
    public function isMasterCardEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_MASTER_CARD_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCardAmexEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_CARD_AMEX_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCardDiscoverEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_CARD_DISCOVER_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCardJcbEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_CARD_JCB_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCardEloEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_CARD_ELO_ENABLED);
    }

    /**
     * @return bool
     */
    public function isCardHiperEnabled()
    {
        return $this->isPaymentMethodEnabled(PaypalAccount::PS_CHECKOUT_CARD_HIPER_ENABLED);
    }

    /**
     * @param string $paymentMethod
     *
     * @return bool
     */
    private function isPaymentMethodEnabled($paymentMethod)
    {
        if (false === \Configuration::hasKey($paymentMethod)) {
            return true;
        }

        return (bool) $this->configuration->get($paymentMethod);
    }
}
