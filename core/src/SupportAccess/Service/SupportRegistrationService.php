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
use PsCheckout\Infrastructure\Repository\PsAccountRepositoryInterface;

/**
 * Registers the shop with the PrestaShop internal support tool so that
 * support agents can fetch module logs without requiring merchant credentials.
 *
 * Registration is fire-and-forget: failures are silently ignored so they
 * never affect the merchant experience. Re-registration is attempted at
 * most once every COOLDOWN_SECONDS seconds.
 */
class SupportRegistrationService
{
    const CONFIG_KEY_REGISTER_URL = 'PS_CHECKOUT_SUPPORT_TOOL_REGISTER_URL';
    const CONFIG_KEY_API_KEY = 'PS_CHECKOUT_SUPPORT_API_KEY';
    const CONFIG_KEY_LAST_REGISTERED = 'PS_CHECKOUT_SUPPORT_REGISTERED_AT';
    const COOLDOWN_SECONDS = 86400; // 24 h

    /** @var SupportTokenService */
    private $supportTokenService;

    /** @var PsAccountRepositoryInterface */
    private $psAccountRepository;

    /** @var ConfigurationInterface */
    private $configuration;

    public function __construct(
        SupportTokenService $supportTokenService,
        PsAccountRepositoryInterface $psAccountRepository,
        ConfigurationInterface $configuration
    ) {
        $this->supportTokenService = $supportTokenService;
        $this->psAccountRepository = $psAccountRepository;
        $this->configuration = $configuration;
    }

    /**
     * Attempts to register this shop with the support tool.
     * Does nothing if the registration URL is not configured.
     * Skips silently if the cooldown has not elapsed.
     *
     * @param string $shopUrl Public URL of the shop (e.g. https://merchant.com)
     */
    public function tryRegister(string $shopUrl): void
    {
        $registerUrl = $this->configuration->get(self::CONFIG_KEY_REGISTER_URL);
        if (empty($registerUrl)) {
            return;
        }

        $lastRegistered = (int) $this->configuration->get(self::CONFIG_KEY_LAST_REGISTERED);
        if ($lastRegistered > 0 && (time() - $lastRegistered) < self::COOLDOWN_SECONDS) {
            return;
        }

        try {
            $shopUuid = $this->psAccountRepository->getShopUuid();
        } catch (\Exception $e) {
            return;
        }

        if (empty($shopUuid)) {
            return;
        }

        $apiKey = $this->configuration->get(self::CONFIG_KEY_API_KEY);
        $token = $this->supportTokenService->getOrCreateToken();

        $payload = json_encode([
            'shopUuid' => $shopUuid,
            'shopUrl'  => rtrim($shopUrl, '/'),
            'token'    => $token,
        ]);

        $headers = implode("\r\n", [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'X-Support-Api-Key: ' . $apiKey,
        ]);

        $context = stream_context_create([
            'http' => [
                'method'        => 'POST',
                'header'        => $headers,
                'content'       => $payload,
                'timeout'       => 5,
                'ignore_errors' => true,
            ],
        ]);

        $response = @file_get_contents($registerUrl, false, $context);

        if ($response === false) {
            return;
        }

        $data = json_decode($response, true);
        if (!empty($data['ok'])) {
            $this->configuration->set(self::CONFIG_KEY_LAST_REGISTERED, (string) time());
        }
    }
}
