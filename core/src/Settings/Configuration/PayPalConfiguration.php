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

namespace PsCheckout\Core\Settings\Configuration;

use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class PayPalConfiguration
{
    const ROUND_ON_EACH_ITEM = '1';

    const ROUND_UP_AWAY_FROM_ZERO = '2';

    const MODE_LIVE = 'LIVE';

    const MODE_SANDBOX = 'SANDBOX';

    const PS_ROUND_TYPE = 'PS_ROUND_TYPE';

    const PS_PRICE_ROUND_MODE = 'PS_PRICE_ROUND_MODE';

    const PS_CHECKOUT_INTENT = 'PS_CHECKOUT_INTENT';

    const PS_CHECKOUT_PAYMENT_MODE = 'PS_CHECKOUT_MODE';

    const PS_CHECKOUT_CARD_PAYMENT_ENABLED = 'PS_CHECKOUT_CARD_PAYMENT_ENABLED';

    const PS_CHECKOUT_PAYPAL_BUTTON = 'PS_CHECKOUT_PAYPAL_BUTTON';

    const PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES = 'PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES';

    const PS_CHECKOUT_INTEGRATION_DATE = 'PS_CHECKOUT_INTEGRATION_DATE';

    const PS_CHECKOUT_CSP_NONCE = 'PS_CHECKOUT_CSP_NONCE';

    const PS_CHECKOUT_PAYPAL_ID_MERCHANT = 'PS_CHECKOUT_PAYPAL_ID_MERCHANT';

    const PS_CHECKOUT_PAYPAL_EMAIL_STATUS = 'PS_CHECKOUT_PAYPAL_EMAIL_STATUS';

    const PS_CHECKOUT_PAYPAL_PAYMENT_STATUS = 'PS_CHECKOUT_PAYPAL_PAYMENT_STATUS';

    const PS_CHECKOUT_CARD_HOSTED_FIELDS_STATUS = 'PS_CHECKOUT_CARD_PAYMENT_STATUS';

    const PS_CHECKOUT_CARD_HOSTED_FIELDS_ENABLED = 'PS_CHECKOUT_CARD_PAYMENT_ENABLED';

    const PS_CHECKOUT_DISPLAY_LOGO_PRODUCT = 'PS_CHECKOUT_DISPLAY_LOGO_PRODUCT';

    const PS_CHECKOUT_DISPLAY_LOGO_CART = 'PS_CHECKOUT_DISPLAY_LOGO_CART';

    const PS_CHECKOUT_VAULTING = 'PS_CHECKOUT_VAULTING';

    const PS_CHECKOUT_GOOGLE_PAY = 'PS_CHECKOUT_GOOGLE_PAY';

    const PS_CHECKOUT_APPLE_PAY = 'PS_CHECKOUT_APPLE_PAY';

    const PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX = 'PS_CHECKOUT_DOMAIN_REGISTERED_SANDBOX';

    const PS_CHECKOUT_DOMAIN_REGISTERED_LIVE = 'PS_CHECKOUT_DOMAIN_REGISTERED_LIVE';

    const PS_CHECKOUT_CUSTOMER_INTENT_VAULT = 'VAULT';

    const PS_CHECKOUT_CUSTOMER_INTENT_FAVORITE = 'FAVORITE';

    const PS_CHECKOUT_CUSTOMER_INTENT_USES_VAULTING = 'USES_VAULTING';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @param ConfigurationInterface $configuration
     */
    public function __construct(
        ConfigurationInterface $configuration
    ) {
        $this->configuration = $configuration;
    }

    /**
     * @return string
     */
    public function getCardFieldsContingencies(): string
    {
        switch ($this->configuration->get(self::PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES)) {
            case 'SCA_ALWAYS':
                return 'SCA_ALWAYS';
            case 'NONE':
                return 'NONE';
            default:
                return 'SCA_WHEN_REQUIRED';
        }
    }

    /**
     * @return bool
     */
    public function is3dSecureEnabled(): bool
    {
        return $this->getCardFieldsContingencies() !== 'NONE';
    }
}
