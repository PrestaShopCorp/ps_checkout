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

namespace PsCheckout\Infrastructure\Logger\Processor;

class SensitiveDataProcessor
{
    private static $sensitiveHeaders = [
        'authorization',
        'cookie',
        'set-cookie',
        'x-auth-token',
        'x-api-key',
    ];

    private static $sensitiveKeys = [
        'token',
        'password',
        'secret',
        'bearer',
    ];

    /**
     * Payment identifiers that are partially masked (last 4 characters kept for support correlation).
     *
     * @var string[]
     */
    private static $paymentIdentifierKeys = [
        'vault_id',
        'vaultid',
        'customer_id',
    ];

    /**
     * @param array $record
     *
     * @return array
     */
    public function __invoke(array $record)
    {
        $record['context'] = $this->scrub($record['context']);

        return $record;
    }

    /**
     * @param array $data
     * @param int $depth
     *
     * @return array
     */
    private function scrub(array $data, $depth = 0)
    {
        if ($depth > 5) {
            return $data;
        }

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            if (in_array($lowerKey, self::$sensitiveHeaders, true)
                || in_array($lowerKey, self::$sensitiveKeys, true)
            ) {
                $data[$key] = '[REDACTED]';

                continue;
            }

            if (in_array($lowerKey, self::$paymentIdentifierKeys, true)) {
                $data[$key] = $this->mask($value);

                continue;
            }

            if (is_string($value) && strpos($value, 'Bearer ') === 0) {
                $data[$key] = '[REDACTED]';

                continue;
            }

            if ($lowerKey === 'vault' && is_array($value)) {
                $value = $this->maskVault($value);
            }

            if ($lowerKey === 'url' && is_string($value)) {
                $data[$key] = $this->maskUrl($value);

                continue;
            }

            if (is_string($value)) {
                $data[$key] = $this->scrubJson($value, $depth);

                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->scrub($value, $depth + 1);
            }
        }

        return $data;
    }

    /**
     * Masks the vault and vault customer IDs of a PayPal `attributes.vault` node.
     *
     * @param array<mixed> $vault
     *
     * @return array<mixed>
     */
    private function maskVault(array $vault)
    {
        if (isset($vault['id'])) {
            $vault['id'] = $this->mask($vault['id']);
        }

        if (isset($vault['customer']) && is_array($vault['customer']) && isset($vault['customer']['id'])) {
            $vault['customer']['id'] = $this->mask($vault['customer']['id']);
        }

        return $vault;
    }

    /**
     * Masks the vault ID path segment of payment token URLs.
     *
     * @param string $url
     *
     * @return string
     */
    private function maskUrl($url)
    {
        $masked = preg_replace_callback(
            '#(/payment-token/[^/]+/)([^/?\#]+)#',
            function (array $matches) {
                return $matches[1] . $this->maskString($matches[2]);
            },
            $url
        );

        return $masked === null ? '[REDACTED]' : $masked;
    }

    /**
     * Scrubs a JSON encoded string (e.g. an HTTP body). Returns it unchanged when it is not JSON or has nothing to scrub.
     *
     * @param string $value
     * @param int $depth
     *
     * @return string
     */
    private function scrubJson($value, $depth)
    {
        $trimmed = ltrim($value);

        if ($trimmed === '' || ($trimmed[0] !== '{' && $trimmed[0] !== '[')) {
            return $value;
        }

        $decoded = json_decode($value, true);

        if (!is_array($decoded)) {
            return $value;
        }

        $scrubbed = $this->scrub($decoded, $depth + 1);

        if ($scrubbed === $decoded) {
            return $value;
        }

        $encoded = json_encode($scrubbed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        return $encoded === false ? '[REDACTED]' : $encoded;
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private function mask($value)
    {
        if (!is_scalar($value) || $value === '') {
            return $value;
        }

        return $this->maskString((string) $value);
    }

    /**
     * Keeps the last 4 characters so support can match the value against the database.
     *
     * @param string $value
     *
     * @return string
     */
    private function maskString($value)
    {
        if (strlen($value) <= 4) {
            return '[REDACTED]';
        }

        return '****' . substr($value, -4);
    }
}
