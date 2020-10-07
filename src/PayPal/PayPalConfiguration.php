<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Settings\RoundingSettings;

class PayPalConfiguration
{
    const INTENT = 'PS_CHECKOUT_INTENT';
    const PAYMENT_MODE = 'PS_CHECKOUT_MODE';
    const CARD_PAYMENT_ENABLED = 'PS_CHECKOUT_CARD_PAYMENT_ENABLED';
    const PS_ROUND_TYPE = 'PS_ROUND_TYPE';
    const PS_PRICE_ROUND_MODE = 'PS_PRICE_ROUND_MODE';
    const PAYMENT_METHODS_ORDER = 'PS_CHECKOUT_PAYMENT_METHODS_ORDER';
    const INTEGRATION_DATE = 'PS_CHECKOUT_INTEGRATION_DATE';
    const HOSTED_FIELDS_3DS_DISABLED = 'PS_CHECKOUT_3DS_DISABLED';
    const CSP_NONCE = 'PS_CHECKOUT_CSP_NONCE';
    const PS_CHECKOUT_PAYPAL_CB_INLINE = 'PS_CHECKOUT_PAYPAL_CB_INLINE';

    /**
     * @var PrestaShopConfiguration
     */
    private $configuration;

    public function __construct(PrestaShopConfiguration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Used to return the PS_CHECKOUT_INTENT from the Configuration
     *
     * @return string
     */
    public function getIntent()
    {
        return Intent::CAPTURE === $this->configuration->get(self::INTENT) ? Intent::CAPTURE : Intent::AUTHORIZE;
    }

    /**
     * Used to set the PS_CHECKOUT_INTENT in the Configuration
     *
     * @param $captureMode
     *
     * @throws PsCheckoutException
     */
    public function setIntent($captureMode)
    {
        if (!in_array($captureMode, [Intent::CAPTURE, Intent::AUTHORIZE])) {
            throw new \UnexpectedValueException(sprintf('The value should be an Intent constant, %s value sent', $captureMode));
        }

        $this->configuration->set(self::INTENT, $captureMode);
    }

    /**
     * Used to return the PS_CHECKOUT_PAYMENT_METHODS_ORDER from the Configuration
     *
     * @return string
     */
    public function getPaymentMethodsOrder()
    {
        return $this->configuration->get(self::PAYMENT_METHODS_ORDER);
    }

    /**
     * Used to set the PS_CHECKOUT_PAYMENT_METHODS_ORDER in the Configuration
     *
     * @param $paymentMethodsOrder
     */
    public function setPaymentMethodsOrder($paymentMethodsOrder)
    {
        $this->configuration->set(self::PAYMENT_METHODS_ORDER, $paymentMethodsOrder);
    }

    /**
     * Used to return the PS_CHECKOUT_MODE from the Configuration
     *
     * @return string
     */
    public function getPaymentMode()
    {
        return Mode::LIVE === $this->configuration->get(self::PAYMENT_MODE) ? Mode::LIVE : Mode::SANDBOX;
    }

    /**
     * Used to set the PS_CHECKOUT_MODE in the Configuration
     *
     * @param $paymentMode
     *
     * @throws PsCheckoutException
     */
    public function setPaymentMode($paymentMode)
    {
        if (!in_array($paymentMode, [Mode::LIVE, Mode::SANDBOX])) {
            throw new \UnexpectedValueException(sprintf('The value should be a Mode constant, %s value sent', $paymentMode));
        }

        $this->configuration->set(self::PAYMENT_MODE, $paymentMode);
    }

    /**
     * @param bool $status
     *
     * @throws PsCheckoutException
     */
    public function setCardPaymentEnabled($status)
    {
        $this->configuration->set(self::CARD_PAYMENT_ENABLED, (bool) $status);
    }

    /**
     * @return bool
     */
    public function isCardPaymentEnabled()
    {
        return (bool) $this->configuration->get(self::CARD_PAYMENT_ENABLED);
    }

    /**
     * @param bool $status
     *
     * @throws PsCheckoutException
     */
    public function setCardInlinePaypalEnabled($status)
    {
        $this->configuration->set(self::PS_CHECKOUT_PAYPAL_CB_INLINE, (bool) $status);
    }

    /**
     * @return bool
     */
    public function isCardInlinePaypalIsEnabled()
    {
        return (bool) $this->configuration->get(self::PS_CHECKOUT_PAYPAL_CB_INLINE);
    }

    /**
     * @param $roundType
     *
     * @throws PsCheckoutException
     */
    public function setRoundType($roundType)
    {
        if (!in_array($roundType, [RoundingSettings::ROUND_ON_EACH_ITEM])) {
            throw new \UnexpectedValueException(sprintf('The value should be a RoundingSettings constant, %s value sent', $roundType));
        }

        $this->configuration->set(self::PS_ROUND_TYPE, $roundType);
    }

    /**
     * @param string $priceRoundMode
     *
     * @throws PsCheckoutException
     */
    public function setPriceRoundMode($priceRoundMode)
    {
        if (!in_array($priceRoundMode, [RoundingSettings::ROUND_UP_AWAY_FROM_ZERO])) {
            throw new \UnexpectedValueException(sprintf('The value should be a RoundingSettings constant, %s value sent', $priceRoundMode));
        }

        $this->configuration->set(self::PS_PRICE_ROUND_MODE, $priceRoundMode);
    }

    /**
     * Check if the rounding configuration if correctly set
     *
     * PS_ROUND_TYPE need to be set to 1 (Round on each item)
     * PS_PRICE_ROUND_MODE need to be set to 2 (Round up away from zero, when it is half way there)
     *
     * @return bool
     */
    public function IsRoundingSettingsCorrect()
    {
        return $this->configuration->get(self::PS_ROUND_TYPE) === RoundingSettings::ROUND_ON_EACH_ITEM
            && $this->configuration->get(self::PS_PRICE_ROUND_MODE) === RoundingSettings::ROUND_UP_AWAY_FROM_ZERO;
    }

    /**
     * Used to return the PS_ROUND_TYPE from the Configuration
     *
     * @return string
     */
    public function getRoundType()
    {
        return $this->configuration->get(self::PS_ROUND_TYPE);
    }

    /**
     * Used to return the PS_PRICE_ROUND_MODE from the Configuration
     *
     * @return string
     */
    public function getPriceRoundMode()
    {
        return $this->configuration->get(self::PS_PRICE_ROUND_MODE);
    }

    /**
     * @return string
     */
    public function getIntegrationDate()
    {
        return $this->configuration->get(static::INTEGRATION_DATE, ['default' => \Ps_checkout::INTEGRATION_DATE]);
    }

    /**
     * @return string
     */
    public function getCSPNonce()
    {
        return $this->configuration->get(static::CSP_NONCE, ['default' => '']);
    }

    /**
     * @return bool
     */
    public function is3dSecureEnabled()
    {
        return false === (bool) $this->configuration->get(static::HOSTED_FIELDS_3DS_DISABLED);
    }

    /**
     * Get funding sources sorted according to configuration
     *
     * @return string[]
     */
    public function getFundingSources()
    {
        $paymentMethods = $this->configuration->get(self::PAYMENT_METHODS_ORDER);

        if (empty($paymentMethods)) {
            $paymentMethods = [
                ['name' => 'card'],
                ['name' => 'paypal'],
            ];
        } else {
            $paymentMethods = json_decode($paymentMethods, true);
        }

        $fundingSources = [
            'card',
            'paypal',
            'bancontact',
            'ideal',
            'gyropay',
            'eps',
            'mybank',
            'sofort',
            'p24',
        ];

        // If card is not enable and cb inline too -> no card
        if (!$this->isCardPaymentEnabled() && !$this->isCardInlinePaypalIsEnabled()) {
            unset($fundingSources[0]);
        } else {
            // If card is not in first position in configuration, move it at the end.
            if ('card' !== $paymentMethods[0]['name']) {
                unset($fundingSources[0]);
                $fundingSources[] = 'card';
            }
        }

        return array_values($fundingSources);
    }
}
