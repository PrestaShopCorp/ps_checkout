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
use PsCheckout\Infrastructure\Repository\PsAccountRepository;
use Psr\Log\LoggerInterface;

class OrderHttpClientConfigurationBuilder implements HttpClientConfigurationBuilderInterface
{
    const TIMEOUT = 10;

    /**
     * @var EnvInterface
     */
    private $paymentEnv;

    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

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
        EnvInterface $paymentEnv,
        PsAccountRepository $psAccountRepository,
        LoggerInterface $logger,
        ConfigurationInterface $configuration,
        LinkInterface $link,
        string $moduleVersion
    ) {
        $this->paymentEnv = $paymentEnv;
        $this->psAccountRepository = $psAccountRepository;
        $this->logger = $logger;
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
            'base_url' => $this->paymentEnv->getPaymentApiUrl(),
            'verify' => $this->getVerify(),
            'timeout' => static::TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/vnd.checkout.v1+json', // api version to use (psl side)
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->psAccountRepository->getIdToken(),  // Token we get from PsAccounts
                'Shop-Id' => $this->psAccountRepository->getShopUuid(),  // Shop UUID we get from PsAccounts
                'Hook-Url' => $this->link->getModuleLink('DispatchWebHook'),
                'Bn-Code' => $this->paymentEnv->getBnCode(),
                'Module-Version' => $this->moduleVersion, // version of the module
                'Prestashop-Version' => _PS_VERSION_, // prestashop version
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
