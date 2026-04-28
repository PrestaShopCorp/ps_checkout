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

namespace PsCheckout\Api\Http\Configuration;

use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Environment\EnvInterface;
use PsCheckout\Infrastructure\Http\Middleware\HttpLoggingMiddleware;
use PsCheckout\Infrastructure\Repository\PsAccountRepository;

class WebhookHttpClientConfigurationBuilder extends AbstractHttpClientConfigurationBuilder
{
    const TIMEOUT = 10;

    /**
     * @var EnvInterface
     */
    private $webhookEnv;

    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @var string
     */
    private $moduleVersion;

    public function __construct(
        EnvInterface $webhookEnv,
        PsAccountRepository $psAccountRepository,
        HttpLoggingMiddleware $httpLoggingMiddleware,
        ConfigurationInterface $configuration,
        LinkInterface $link,
        string $moduleVersion
    ) {
        $this->webhookEnv = $webhookEnv;
        $this->psAccountRepository = $psAccountRepository;
        $this->httpLoggingMiddleware = $httpLoggingMiddleware;
        $this->configuration = $configuration;
        $this->link = $link;
        $this->moduleVersion = $moduleVersion;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $configuration = [
            'base_url' => $this->webhookEnv->getWebhookApiUrl(),
            'verify' => $this->getVerify(),
            'timeout' => static::TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->psAccountRepository->getIdToken(),  // Token we get from PsAccounts
                'Checkout-Shop-Id' => $this->psAccountRepository->getShopUuid(),  // Shop UUID we get from PsAccounts
                'Checkout-Hook-Url' => $this->link->getModuleLink('DispatchWebHook'),
                'Checkout-Module-Version' => $this->moduleVersion, // version of the module
                'Checkout-Prestashop-Version' => _PS_VERSION_, // prestashop version
                'PayPal-Merchant-Id' => $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT),
            ],
        ];

        $this->applyLoggingMiddleware($configuration, $this->configuration);

        return $configuration;
    }
}
