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
use PsCheckout\Infrastructure\Repository\PsAccountRepositoryInterface;

class CheckoutClientConfigurationBuilder extends AbstractHttpClientConfigurationBuilder
{
    const TIMEOUT = 10;

    /** @var string */
    private $moduleVersion;

    /** @var ConfigurationInterface */
    private $configuration;

    /** @var LinkInterface */
    private $link;

    /** @var EnvInterface */
    private $env;

    /** @var PsAccountRepositoryInterface */
    private $psAccountRepository;

    public function __construct(
        string $moduleVersion,
        ConfigurationInterface $configuration,
        LinkInterface $link,
        EnvInterface $env,
        PsAccountRepositoryInterface $psAccountRepository,
        HttpLoggingMiddleware $httpLoggingMiddleware
    ) {
        $this->moduleVersion = $moduleVersion;
        $this->configuration = $configuration;
        $this->link = $link;
        $this->env = $env;
        $this->psAccountRepository = $psAccountRepository;
        $this->httpLoggingMiddleware = $httpLoggingMiddleware;
    }

    /**
     * @return array
     */
    public function build(): array
    {
        $configuration = [
            'base_url' => $this->env->getCheckoutApiUrl(),
            'verify' => $this->getVerify(),
            'timeout' => self::TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->psAccountRepository->getIdToken(),  // Token we get from PsAccounts
                'Checkout-Shop-Id' => $this->psAccountRepository->getShopUuid(),  // Shop UUID we get from PsAccounts
                'Checkout-Hook-Url' => $this->link->getModuleLink('DispatchWebHook'),
                'Checkout-Bn-Code' => $this->env->getBnCode(),
                'Checkout-Module-Version' => $this->moduleVersion,
                'Checkout-Prestashop-Version' => _PS_VERSION_,
            ],
        ];

        $this->applyLoggingMiddleware($configuration, $this->configuration);

        return $configuration;
    }
}
