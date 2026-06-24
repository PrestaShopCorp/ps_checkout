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
use PsCheckout\Core\Settings\Configuration\LoggerConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Http\Middleware\HttpLoggingMiddleware;
use PsCheckout\Infrastructure\Http\Subscriber\HttpLoggingSubscriber;

abstract class AbstractHttpClientConfigurationBuilder implements HttpClientConfigurationBuilderInterface
{
    /**
     * @var HttpLoggingMiddleware
     */
    protected $httpLoggingMiddleware;

    /**
     * Attaches the HTTP logging handler to the Guzzle client configuration when HTTP logging is enabled.
     * Guzzle 6/7 (PS 8, PS 9): pushes HttpLoggingMiddleware onto a HandlerStack.
     * Guzzle 5.3 (PS 1.7): attaches HttpLoggingSubscriber to an Emitter.
     *
     * @param array $config
     * @param ConfigurationInterface $configuration
     *
     * @return void
     */
    protected function applyLoggingMiddleware(array &$config, ConfigurationInterface $configuration)
    {
        if (!$configuration->getInteger(LoggerConfiguration::PS_CHECKOUT_LOGGER_HTTP)) {
            return;
        }

        // Guzzle 6/7 — middleware handler stack (PS 8, PS 9)
        if (defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION') && class_exists(HandlerStack::class)) {
            $handlerStack = HandlerStack::create();
            $handlerStack->push($this->httpLoggingMiddleware);
            $config['handler'] = $handlerStack;

            return;
        }

        // Guzzle 5.3 — event emitter/subscriber (PS 1.7)
        if (defined('\GuzzleHttp\ClientInterface::VERSION') && class_exists(Emitter::class)) {
            $emitter = new Emitter();
            $emitter->attach(new HttpLoggingSubscriber($this->httpLoggingMiddleware->getLogger()));
            $config['emitter'] = $emitter;
        }
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
