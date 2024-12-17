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

namespace PrestaShop\Module\PrestashopCheckout\Builder\Configuration;

use GuzzleHttp\Event\Emitter;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use GuzzleLogMiddleware\LogMiddleware;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Environment\Env;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerConfiguration;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Routing\Router;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use Ps_checkout;
use Psr\Log\LoggerInterface;

class CheckoutClientConfigurationBuilder implements HttpClientConfigurationBuilderInterface
{
    const TIMEOUT = 10;

    /** @var Env */
    private $env;

    /** @var Router */
    private $router;

    /** @var ShopContext */
    private $shopContext;

    /** @var PsAccountRepository */
    private $psAccountRepository;

    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;
    /**
     * @var LoggerConfiguration
     */
    private $loggerConfiguration;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Env $env,
        Router $router,
        ShopContext $shopContext,
        PsAccountRepository $psAccountRepository,
        PrestaShopContext $prestaShopContext,
        LoggerConfiguration $loggerConfiguration,
        LoggerInterface $logger
    ) {
        $this->env = $env;
        $this->router = $router;
        $this->shopContext = $shopContext;
        $this->psAccountRepository = $psAccountRepository;
        $this->prestaShopContext = $prestaShopContext;
        $this->loggerConfiguration = $loggerConfiguration;
        $this->logger = $logger;
    }

    /**
     * @return array
     */
    public function build()
    {
        $configuration = [
            'base_url' => $this->env->getCheckoutApiUrl(),
            'verify' => $this->getVerify(),
            'timeout' => static::TIMEOUT,
            'headers' => [
//                'Content-Type' => 'application/vnd.checkout.v1+json', // api version to use (psl side)
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->psAccountRepository->getIdToken(),  // Token we get from PsAccounts
                'Checkout-Shop-Id' => $this->psAccountRepository->getShopUuid(),  // Shop UUID we get from PsAccounts
                'Checkout-Hook-Url' => $this->router->getDispatchWebhookLink($this->prestaShopContext->getShopId()),
                'Checkout-Bn-Code' => $this->shopContext->getBnCode(),
                'Checkout-Module-Version' => Ps_checkout::VERSION, // version of the module
                'Checkout-Prestashop-Version' => _PS_VERSION_, // prestashop version
            ],
        ];

        if ($this->loggerConfiguration->isHttpEnabled()) {
            if (
                defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION')
                && class_exists(HandlerStack::class)
                && class_exists(LogMiddleware::class)
            ) {
                $handlerStack = HandlerStack::create();
                $handlerStack->push(new LogMiddleware($this->logger));
                $configuration['handler'] = $handlerStack;
            } elseif (
                defined('\GuzzleHttp\ClientInterface::VERSION')
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
