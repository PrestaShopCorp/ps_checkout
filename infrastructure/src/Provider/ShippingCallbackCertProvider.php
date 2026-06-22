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

use GuzzleHttp\Psr7\Request;
use PsCheckout\Api\Http\HttpClientInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Cache\ShippingCallbackCache;
use PsCheckout\Core\PayPal\ShippingCallback\Cache\ShippingCallbackCacheInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Provider\ShippingCallbackCertProviderInterface;

class ShippingCallbackCertProvider implements ShippingCallbackCertProviderInterface
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    /**
     * @var ShippingCallbackCacheInterface
     */
    private $cache;

    public function __construct(HttpClientInterface $httpClient, ShippingCallbackCacheInterface $cache)
    {
        $this->httpClient = $httpClient;
        $this->cache = $cache;
    }

    /**
     * {@inheritDoc}
     */
    public function getCert(string $certUrl): string
    {
        $cacheKey = md5($certUrl);

        if ($this->cache->has($cacheKey)) {
            return (string) $this->cache->get($cacheKey);
        }

        $cert = $this->fetchCert($certUrl);
        $ttl = $this->computeTtlFromCert($cert);
        if ($ttl > 0) {
            $this->cache->set($cacheKey, $cert, $ttl);
        }

        return $cert;
    }

    /**
     * @throws \RuntimeException
     */
    private function fetchCert(string $certUrl): string
    {
        try {
            $response = $this->httpClient->sendRequest(new Request('GET', $certUrl));
            $cert = (string) $response->getBody();
        } catch (\Exception $exception) {
            throw new \RuntimeException(
                sprintf('Failed to download PayPal cert from %s', $certUrl),
                0,
                $exception
            );
        }

        if ($cert === '') {
            throw new \RuntimeException(sprintf('Failed to download PayPal cert from %s', $certUrl));
        }

        return $cert;
    }

    /**
     * Compute cache TTL from the cert's notAfter date.
     * Falls back to ShippingCallbackCache::DEFAULT_TTL when the cert cannot be parsed.
     * The result is clamped to [MIN_TTL, MAX_TTL] so a near-expiry or very
     * long-lived cert does not cause either constant re-fetching or stale data.
     */
    private function computeTtlFromCert(string $certPem): int
    {
        $cert = openssl_x509_read($certPem);
        if ($cert !== false) {
            $parsed = openssl_x509_parse($cert);
            if (is_array($parsed) && isset($parsed['validTo_time_t'])) {
                $remaining = (int) $parsed['validTo_time_t'] - time();
                if ($remaining > 0) {
                    return max(
                        ShippingCallbackCache::MIN_TTL,
                        min($remaining, ShippingCallbackCache::MAX_TTL)
                    );
                }

                return 0;
            }
        }

        return ShippingCallbackCache::DEFAULT_TTL;
    }
}
