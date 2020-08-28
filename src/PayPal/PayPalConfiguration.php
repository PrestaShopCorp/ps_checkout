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

class PayPalConfiguration
{
    const INTENT = 'PS_CHECKOUT_INTENT';
    const PAYMENT_MODE = 'PS_CHECKOUT_MODE';
    const CARD_PAYMENT_ENABLED = 'PS_CHECKOUT_CARD_PAYMENT_ENABLED';

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
}
