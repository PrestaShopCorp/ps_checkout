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

use GuzzleHttp\Event\Emitter;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use GuzzleLogMiddleware\LogMiddleware;
use PsCheckout\Core\Settings\Configuration\LoggerConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Environment\EnvInterface;
use PsCheckout\Infrastructure\Repository\PsAccountRepositoryInterface;
use Psr\Log\LoggerInterface;

class CheckoutClientConfigurationBuilder implements HttpClientConfigurationBuilderInterface
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

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        string $moduleVersion,
        ConfigurationInterface $configuration,
        LinkInterface $link,
        EnvInterface $env,
        PsAccountRepositoryInterface $psAccountRepository,
        LoggerInterface $logger
    ) {
        $this->moduleVersion = $moduleVersion;
        $this->configuration = $configuration;
        $this->link = $link;
        $this->env = $env;
        $this->psAccountRepository = $psAccountRepository;
        $this->logger = $logger;
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

        if (
            $this->configuration->getInteger(LoggerConfiguration::PS_CHECKOUT_LOGGER_HTTP)
            && defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION')
            && class_exists(HandlerStack::class)
            && class_exists(LogMiddleware::class)
        ) {
            $handlerStack = HandlerStack::create();
            $handlerStack->push(new LogMiddleware($this->logger));
            $configuration['handler'] = $handlerStack;
        } elseif (
            $this->configuration->getInteger(LoggerConfiguration::PS_CHECKOUT_LOGGER_HTTP)
            && defined('\GuzzleHttp\ClientInterface::VERSION')
            && class_exists(Emitter::class)
            && class_exists(LogSubscriber::class)
            && class_exists(Formatter::class)
        ) {
            $emitter = new Emitter();
            $emitter->attach(new LogSubscriber(
                $this->logger,
                Formatter::DEBUG
            ));

            $configuration['emitter'] = $emitter;
        }

        return $configuration;
    }

    /**
     * @see https://docs.guzzlephp.org/en/5.3/clients.html#verify
     *
     * @return true|string
     */
    protected function getVerify()
    {
        if (defined('_PS_CACHE_CA_CERT_FILE_') && file_exists(constant('_PS_CACHE_CA_CERT_FILE_'))) {
            return constant('_PS_CACHE_CA_CERT_FILE_');
        }

        return true;
    }
}
