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

use PsCheckout\Core\PayPal\ShippingCallback\Provider\ShippingCallbackCertProviderInterface;

class ShippingCallbackCertProvider implements ShippingCallbackCertProviderInterface
{
    /**
     * @var array<string, string>
     */
    private static $cache = [];

    /**
     * {@inheritDoc}
     */
    public function getCert(string $certUrl): string
    {
        if (isset(self::$cache[$certUrl])) {
            return self::$cache[$certUrl];
        }

        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => true,
                'verify_peer_name' => true,
            ],
            'http' => [
                'timeout' => 5,
                'method' => 'GET',
            ],
        ]);

        $cert = @file_get_contents($certUrl, false, $context);

        if ($cert === false || $cert === '') {
            throw new \RuntimeException(sprintf('Failed to download PayPal cert from %s', $certUrl));
        }

        self::$cache[$certUrl] = $cert;

        return $cert;
    }
}
