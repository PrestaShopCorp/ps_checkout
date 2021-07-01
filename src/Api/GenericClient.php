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

namespace PrestaShop\Module\PrestashopCheckout\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Ring\Exception\RingException;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\Response\ResponseApiHandler;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

/**
 * Construct the client used to make call to maasland
 */
class GenericClient
{
    /**
     * Guzzle Client
     *
     * @var Client
     */
    protected $client;

    /**
     * Class Link in order to generate module link
     *
     * @var \Link
     */
    protected $link;

    /**
     * Enable or disable the catch of Maasland 400 error
     * If set to false, you will not be able to catch the error of maasland
     * guzzle will show a different error message.
     *
     * @var bool
     */
    protected $catchExceptions = false;

    /**
     * Set how long guzzle will wait a response before end it up
     *
     * @var int
     */
    protected $timeout = 10;

    /**
     * Api route
     *
     * @var string
     */
    protected $route;

    /**
     * Wrapper of method post from guzzle client
     *
     * @param array $options payload
     *
     * @return array return response or false if no response
     */
    protected function post(array $options = [])
    {
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

        /** @var \PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler $exceptionHandler */
        $exceptionHandler = $module->getService('ps_checkout.handler.exception');

        if (method_exists($this->client, 'getEmitter') && true === (bool) $this->getConfiguration(LoggerFactory::PS_CHECKOUT_LOGGER_HTTP, true)) {
            /** @var LoggerInterface $logger */
            $logger = $module->getService('ps_checkout.logger');

            $subscriber = new LogSubscriber(
                $logger,
                $this->getLogFormatter()
            );

            $this->client->getEmitter()->attach($subscriber);
        }

        try {
            $response = $this->getClient()->post($this->getRoute(), $options);
        } catch (RequestException $exception) {
            return $this->handleException(
                new PsCheckoutException(
                    $exception->getMessage(),
                    PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION,
                    $exception
                )
            );
        } catch (RingException $exception) {
            $e = new PsCheckoutException(
                $exception->getMessage(),
                PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION,
                $exception
            );
            $exceptionHandler->handle($e, false);

            return $this->handleException($e);
        } catch (\Exception $exception) {
            $exceptionHandler->handle($exception, false);

            return $this->handleException($exception);
        }

        $responseHandler = new ResponseApiHandler();
        $response = $responseHandler->handleResponse($response);

        return $response;
    }

    /**
     * Setter for route
     *
     * @param string $route
     */
    protected function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Setter for client
     *
     * @param Client $client
     */
    protected function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Setter for link
     *
     * @param \Link $link
     */
    protected function setLink(\Link $link)
    {
        $this->link = $link;
    }

    /**
     * Setter for timeout
     *
     * @param int $timeout
     */
    protected function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Setter for exceptions mode
     *
     * @param bool $bool
     */
    protected function setExceptionsMode($bool)
    {
        $this->catchExceptions = $bool;
    }

    /**
     * Getter for route
     *
     * @return string
     */
    protected function getRoute()
    {
        return $this->route;
    }

    /**
     * Getter for client
     *
     * @return Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * Getter for Link
     *
     * @return \Link
     */
    protected function getLink()
    {
        return $this->link;
    }

    /**
     * Getter for timeout
     *
     * @return int
     */
    protected function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Getter for exceptions mode
     *
     * @return bool
     */
    protected function getExceptionsMode()
    {
        return $this->catchExceptions;
    }

    /**
     * @todo To be moved elsewhere
     *
     * @param string $key
     * @param mixed $defaultValue
     *
     * @return mixed
     */
    private function getConfiguration($key, $defaultValue)
    {
        if (false === \Configuration::hasKey($key)) {
            return $defaultValue;
        }

        return \Configuration::get(
            $key,
            null,
            null,
            (int) \Context::getContext()->shop->id
        );
    }

    /**
     * @return string
     */
    private function getLogFormatter()
    {
        $formatter = $this->getConfiguration(LoggerFactory::PS_CHECKOUT_LOGGER_HTTP_FORMAT, 'DEBUG');

        if ('CLF' === $formatter) {
            return Formatter::CLF;
        }

        if ('SHORT' === $formatter) {
            return Formatter::SHORT;
        }

        return Formatter::DEBUG;
    }

    private function handleException(\Exception $exception)
    {
        $body = '';
        $httpCode = 500;
        $hasResponse = method_exists($exception, 'hasResponse') ? $exception->hasResponse() : false;

        if (true === $hasResponse && method_exists($exception, 'getResponse')) {
            $body = $exception->getResponse()->getBody();
            $httpCode = $exception->getResponse()->getStatusCode();
        }

        return [
            'status' => false,
            'httpCode' => $httpCode,
            'body' => $body,
            'exceptionCode' => $exception->getCode(),
            'exceptionMessage' => $exception->getMessage(),
        ];
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
