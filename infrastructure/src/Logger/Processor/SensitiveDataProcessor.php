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
        'id_token',
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
        if (isset($record['message']) && is_string($record['message'])) {
            $record['message'] = $this->maskTokenUrls($record['message']);
        }

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
        if ($depth > 10) {
            return $data;
        }

        $data = $this->maskVaultWebhookResource($data);
        $data = $this->maskRejectedIdentifierValue($data);

        foreach ($data as $key => $value) {
            $lowerKey = strtolower((string) $key);

            if (in_array($lowerKey, self::$sensitiveHeaders, true)
                || in_array($lowerKey, self::$sensitiveKeys, true)
            ) {
                $data[$key] = '[REDACTED]';

                continue;
            }

            if (in_array($lowerKey, self::$paymentIdentifierKeys, true)) {
                if (!is_array($value)) {
                    $data[$key] = $this->mask($value);

                    continue;
                }

                $value = $this->maskVault($value);
            }

            if (is_string($value) && strpos($value, 'Bearer ') === 0) {
                $data[$key] = '[REDACTED]';

                continue;
            }

            if ($lowerKey === 'vault' && is_array($value)) {
                $value = $this->maskVault($value);
            }

            if ($lowerKey === 'attributes' && is_array($value)) {
                $value = $this->maskCustomer($value);
            }

            if (is_string($value)) {
                $data[$key] = $this->maskEmbeddedJsonIdentifiers($this->maskTokenUrls($this->scrubJson($value, $depth)));

                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->scrub($value, $depth + 1);

                continue;
            }

            if ($value instanceof \Throwable) {
                $data[$key] = $this->normalizeException($value);
            }
        }

        return $data;
    }

    /**
     * Converts an exception to the array shape Monolog would log, with token URLs masked in every message.
     *
     * Monolog formats exception objects itself, after the processors ran, so a message that embeds the
     * request URL (e.g. Guzzle `cURL error 28: ... for https://.../payment-token/<merchant>/<vault id>`)
     * would otherwise reach the log in clear, through the `previous` chain as well.
     *
     * @param \Throwable $exception
     * @param int $depth
     *
     * @return array<string, mixed>
     */
    private function normalizeException(\Throwable $exception, $depth = 0)
    {
        $data = [
            'class' => get_class($exception),
            'message' => $this->maskTokenUrls($exception->getMessage()),
            'code' => $exception->getCode(),
            'file' => $exception->getFile() . ':' . $exception->getLine(),
        ];

        $trace = [];

        foreach ($exception->getTrace() as $frame) {
            if (isset($frame['file'], $frame['line'])) {
                $trace[] = $frame['file'] . ':' . $frame['line'];
            }
        }

        $data['trace'] = $trace;

        $previous = $exception->getPrevious();

        if ($previous !== null && $depth < 10) {
            $data['previous'] = $this->normalizeException($previous, $depth + 1);
        }

        return $data;
    }

    /**
     * Masks the payment token of a `VAULT.PAYMENT-TOKEN.*` webhook, whose `resource` is the token itself
     * (`resource.id` is the vault ID, `resource.customer.id` the PayPal customer ID) and whose `resourceId`
     * is logged next to it. These events are not routed to shops today, this is defence in depth.
     *
     * @param array<mixed> $node
     *
     * @return array<mixed>
     */
    private function maskVaultWebhookResource(array $node)
    {
        $eventType = null;

        foreach (['eventType', 'event_type'] as $eventTypeKey) {
            if (isset($node[$eventTypeKey]) && is_string($node[$eventTypeKey])) {
                $eventType = $node[$eventTypeKey];
            }
        }

        if ($eventType === null || strpos(strtoupper($eventType), 'VAULT.PAYMENT-TOKEN') !== 0) {
            return $node;
        }

        if (isset($node['resource']) && is_array($node['resource'])) {
            $node['resource'] = $this->maskVault($node['resource']);
        }

        if (isset($node['resourceId'])) {
            $node['resourceId'] = $this->mask($node['resourceId']);
        }

        return $node;
    }

    /**
     * Masks the rejected value echoed by a PayPal validation error (`details[].value`) when the rejected
     * field is a payment identifier (e.g. `/payment_source/card/vault_id` with `VAULT_TOKEN_NOT_FOUND`).
     *
     * @param array<mixed> $node
     *
     * @return array<mixed>
     */
    private function maskRejectedIdentifierValue(array $node)
    {
        if (!isset($node['field'], $node['value']) || !is_string($node['field'])) {
            return $node;
        }

        $field = strtolower($node['field']);

        if (preg_match('#(vault_id|vaultid|customer_id|/customer/id|/vault/id)$#', $field) === 1) {
            $node['value'] = $this->mask($node['value']);
        }

        return $node;
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

        return $this->maskCustomer($vault);
    }

    /**
     * Masks the PayPal customer ID of a node with a `customer` child (`attributes` or `vault`).
     *
     * @param array<mixed> $node
     *
     * @return array<mixed>
     */
    private function maskCustomer(array $node)
    {
        if (isset($node['customer']) && is_array($node['customer']) && isset($node['customer']['id'])) {
            $node['customer']['id'] = $this->mask($node['customer']['id']);
        }

        return $node;
    }

    /**
     * Masks the token path segment of payment token URLs found in any string value.
     *
     * Covers the module's own route (`/payment-token/<merchant>/<vault id>`) and the PayPal
     * vault routes (`/v3/vault/payment-tokens/<id>`, `/v3/vault/setup-tokens/<id>`), which
     * PayPal returns in the `links[].href` of the `vault` node of order and capture responses.
     *
     * @param string $value
     *
     * @return string
     */
    private function maskTokenUrls($value)
    {
        if (strpos($value, '-token') === false) {
            return $value;
        }

        $masked = preg_replace_callback(
            '#(/payment-token/[^/?\#"]+/|/vault/(?:payment|setup)-tokens/)([A-Za-z0-9_-]+)#',
            function (array $matches) {
                return $matches[1] . $this->maskString($matches[2]);
            },
            $value
        );

        return $masked === null ? '[REDACTED]' : $masked;
    }

    /**
     * Masks identifier pairs of a JSON fragment embedded in free text, e.g. the truncated body summary of a
     * Guzzle exception message (`... response: {"name":"UNPROCESSABLE_ENTITY","details":[{"field":"/payment_source/card/vault_id","value":"8kk8451t5rbxwbvp" (truncated...)`),
     * which `scrubJson()` cannot decode.
     *
     * @param string $value
     *
     * @return string
     */
    private function maskEmbeddedJsonIdentifiers($value)
    {
        if (strpos($value, '"') === false) {
            return $value;
        }

        $masked = preg_replace_callback(
            '#"(vault_id|vaultId|customer_id|id_token)"\s*:\s*"([^"]*)"#i',
            function (array $matches) {
                if (strtolower($matches[1]) === 'id_token') {
                    return '"' . $matches[1] . '":"[REDACTED]"';
                }

                return '"' . $matches[1] . '":"' . $this->maskString($matches[2]) . '"';
            },
            $value
        );

        if ($masked === null) {
            return '[REDACTED]';
        }

        $masked = preg_replace_callback(
            '#("field"\s*:\s*"[^"]*(?:vault_id|vaultId|customer_id|/customer/id|/vault/id)"\s*,\s*"value"\s*:\s*")([^"]*)"#i',
            function (array $matches) {
                return $matches[1] . $this->maskString($matches[2]) . '"';
            },
            $masked
        );

        return $masked === null ? '[REDACTED]' : $masked;
    }

    /**
     * Scrubs a JSON encoded string (e.g. an HTTP body). Returns it unchanged when it is not JSON or has nothing to scrub.
     * A scrubbed body is re-encoded compact: zero fractions are kept, a big integer becomes a string and an empty object `[]`.
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

        $decoded = json_decode($value, true, 512, JSON_BIGINT_AS_STRING);

        if (!is_array($decoded)) {
            return $value;
        }

        $scrubbed = $this->scrub($decoded, $depth + 1);

        if ($scrubbed === $decoded) {
            return $value;
        }

        $encoded = json_encode($scrubbed, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);

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
