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

namespace PsCheckout\Infrastructure\Provider;

use PsCheckout\Core\PayPal\ShippingCallback\Provider\CallbackHeaderProviderInterface;

class CallbackHeaderProvider implements CallbackHeaderProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function getHeaders(): array
    {
        return [
            'Paypal-Transmission-Id' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ID'] ?? null,
            'Paypal-Transmission-Time' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_TIME'] ?? null,
            'Paypal-Transmission-Sig' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_SIG'] ?? null,
            'Paypal-Transmission-Alg' => $_SERVER['HTTP_PAYPAL_TRANSMISSION_ALG'] ?? null,
            'Paypal-Cert-Url' => $_SERVER['HTTP_PAYPAL_CERT_URL'] ?? null,
            'Correlation-Id' => $_SERVER['HTTP_CORRELATION_ID'] ?? null,
        ];
    }
}
