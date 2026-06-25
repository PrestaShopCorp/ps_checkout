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

namespace PsCheckout\Core\PayPal\ApplePay\Builder;

use PsCheckout\Core\Order\Builder\CheckoutContextInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class ApplePayApplicationDataBuilder implements ApplePayNodeBuilderInterface
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritDoc}
     *
     * Encodes a small opaque payload as Base64 so the merchant back-end can
     * correlate an Apple Pay token with its PrestaShop cart and environment.
     */
    public function build(CheckoutContextInterface $context): array
    {
        $payload = json_encode([
            'cart_id' => $context->getCartId(),
            'environment' => (string) $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYMENT_MODE),
        ]);

        return ['application_data' => base64_encode((string) $payload)];
    }
}
