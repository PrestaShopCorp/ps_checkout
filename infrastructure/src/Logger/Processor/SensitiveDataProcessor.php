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

            if (is_string($value) && strpos($value, 'Bearer ') === 0) {
                $data[$key] = '[REDACTED]';

                continue;
            }

            if (is_array($value)) {
                $data[$key] = $this->scrub($value, $depth + 1);
            }
        }

        return $data;
    }
}
