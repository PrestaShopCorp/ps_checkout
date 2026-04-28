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

use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Environment\EnvInterface;
use PsCheckout\Infrastructure\Http\Middleware\HttpLoggingMiddleware;
use PsCheckout\Infrastructure\Repository\PsAccountRepository;

// TODO: Remove this class and references when maasland webhooks are no longer needed
class MaaslandOrderHttpClientConfigurationBuilder extends AbstractHttpClientConfigurationBuilder
{
    const TIMEOUT = 10;

    /**
     * @var EnvInterface
     */
    private $env;

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
        EnvInterface $env,
        PsAccountRepository $psAccountRepository,
        HttpLoggingMiddleware $httpLoggingMiddleware,
        ConfigurationInterface $configuration,
        LinkInterface $link,
        string $moduleVersion
    ) {
        $this->env = $env;
        $this->psAccountRepository = $psAccountRepository;
        $this->httpLoggingMiddleware = $httpLoggingMiddleware;
        $this->configuration = $configuration;
        $this->link = $link;
        $this->moduleVersion = $moduleVersion;
    }

    /**
     * @return array<string, mixed>
     */
    public function build(): array
    {
        $configuration = [
            'base_url' => $this->env->getMaaslandOrderApiUrl(),
            'verify' => $this->getVerify(),
            'timeout' => static::TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/vnd.checkout.v1+json', // api version to use (psl side)
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->psAccountRepository->getIdToken(),  // Token we get from PsAccounts
                'Shop-Id' => $this->psAccountRepository->getShopUuid(),  // Shop UUID we get from PsAccounts
                'Hook-Url' => $this->link->getModuleLink('DispatchWebHook'),
                'Bn-Code' => $this->env->getBnCode(),
                'Module-Version' => $this->moduleVersion, // version of the module
                'Prestashop-Version' => _PS_VERSION_, // prestashop version
            ],
        ];

        $this->applyLoggingMiddleware($configuration, $this->configuration);

        return $configuration;
    }
}
