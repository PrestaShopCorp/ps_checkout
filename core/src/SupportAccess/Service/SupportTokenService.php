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

namespace PsCheckout\Core\SupportAccess\Service;

use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class SupportTokenService
{
    const CONFIG_KEY = 'PS_CHECKOUT_SUPPORT_TOKEN';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Returns the support token, generating and storing it if it doesn't exist yet.
     */
    public function getOrCreateToken(): string
    {
        $token = $this->configuration->get(self::CONFIG_KEY);

        if (empty($token)) {
            $token = bin2hex(random_bytes(32));
            $this->configuration->set(self::CONFIG_KEY, $token);
        }

        return $token;
    }

    /**
     * Verifies that the provided token matches the stored one.
     * Uses hash_equals to prevent timing attacks.
     */
    public function validateToken(string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        $storedToken = $this->configuration->get(self::CONFIG_KEY);

        if (empty($storedToken)) {
            return false;
        }

        return hash_equals($storedToken, $token);
    }

    /**
     * Regenerates the support token (e.g. after a suspected leak).
     */
    public function rotateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->configuration->set(self::CONFIG_KEY, $token);

        return $token;
    }
}
