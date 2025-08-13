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

namespace PsCheckout\Core\PayPal\ShippingTracking\Cache;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ShippingTrackingCache extends ChainAdapter implements ShippingTrackingCacheInterface
{
    /**
     * Cache TTL based on response status
     */
    const CACHE_TTL = [
        'success' => 3600,      // 1 hour for successful responses
        'error' => 300,         // 5 minutes for error responses
        'validation_error' => 1800, // 30 minutes for validation errors
        'default' => 600,       // 10 minutes default
    ];

    /**
     * Successful response indicators
     */
    const SUCCESS_INDICATORS = [
        'tracking_number',
        'carrier',
        'order_id',
    ];

    public function __construct(
        ArrayAdapter $arrayCache,
        FilesystemAdapter $filesystemCache
    ) {
        parent::__construct([$filesystemCache, $arrayCache]);
    }

    /**
     * {@inheritDoc}
     */
    public function has(string $cacheKey): bool
    {
        return parent::hasItem($cacheKey);
    }

    /**
     * {@inheritDoc}
     */
    public function getValue(string $cacheKey)
    {
        return parent::getItem($cacheKey)->get();
    }

    /**
     * {@inheritDoc}
     */
    public function set(string $cacheKey, array $value, $ttl = null): bool
    {
        if (!$ttl) {
            $status = $value['status'] ?? 'default';
            $ttl = self::CACHE_TTL[$status] ?? self::CACHE_TTL['default'];
        }

        $cacheItem = $this->getItem($cacheKey)->set($value)->expiresAfter($ttl);

        return $this->save($cacheItem);
    }

    /**
     * {@inheritDoc}
     */
    public function shouldSkipApiCall(array $cachedResponse, array $currentPayload = []): bool
    {
        if (empty($cachedResponse)) {
            return false;
        }

        $status = $cachedResponse['status'] ?? '';
        $response = $cachedResponse['response'] ?? [];
        $cachedPayload = $cachedResponse['payload'] ?? [];

        // If we have a current payload, compare with cached payload
        if (!empty($currentPayload) && !empty($cachedPayload)) {
            // If payloads are different, don't skip (need to update)
            if (md5(json_encode($currentPayload)) !== md5(json_encode($cachedPayload))) {
                return false;
            }
        }

        // Skip API call if we have a successful response with same payload
        if ($status === 'success') {
            return $this->isSuccessfulResponse((array) $response);
        }

        // For error responses, check if it's a permanent error
        if ($status === 'validation_error') {
            return $this->isPermanentError($response);
        }

        // Don't skip for temporary errors
        return false;
    }

    /**
     * Check if response indicates success
     *
     * @param array $response
     *
     * @return bool
     */
    private function isSuccessfulResponse(array $response): bool
    {
        // If response has error indicators, it's not successful
        if (isset($response['name']) || isset($response['message']) || isset($response['details'])) {
            return false;
        }

        // Check for success indicators
        foreach (self::SUCCESS_INDICATORS as $indicator) {
            if (isset($response[$indicator])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if error is permanent (validation errors that won't change)
     *
     * @param array $response
     *
     * @return bool
     */
    private function isPermanentError(array $response): bool
    {
        if (!isset($response['details'])) {
            return false;
        }

        $permanentErrorCodes = [
            'INVALID_REQUEST',
            'VALIDATION_ERROR',
            'INVALID_PARAMETER_VALUE',
            'MISSING_REQUIRED_PARAMETER',
        ];

        foreach ($response['details'] as $detail) {
            $issue = $detail['issue'] ?? '';
            if (in_array($issue, $permanentErrorCodes)) {
                return true;
            }
        }

        return false;
    }
}
