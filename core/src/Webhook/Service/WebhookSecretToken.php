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

namespace PsCheckout\Core\Webhook\Service;

use PsCheckout\Core\Webhook\WebhookException;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;

class WebhookSecretToken implements WebhookTokenInterface
{
    const PS_CHECKOUT_WEBHOOK_SECRET = 'PS_CHECKOUT_WEBHOOK_SECRET';

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    public function __construct(ConfigurationInterface $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function upsertToken(string $token): bool
    {
        if (empty($token)) {
            throw new WebhookException('Webhook token is empty', WebhookException::WEBHOOK_PAYLOAD_UNSUPPORTED);
        }

        $this->configuration->set(self::PS_CHECKOUT_WEBHOOK_SECRET, $token);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function validateToken(string $token): bool
    {
        $storedToken = $this->configuration->get(self::PS_CHECKOUT_WEBHOOK_SECRET);

        return $token === $storedToken;
    }
}
