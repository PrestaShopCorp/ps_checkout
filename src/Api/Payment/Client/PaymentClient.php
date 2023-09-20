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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment\Client;

use Context;
use GuzzleHttp\Event\Emitter;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Log\Formatter;
use GuzzleHttp\Subscriber\Log\LogSubscriber;
use GuzzleLogMiddleware\LogMiddleware;
use Link;
use Module;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Environment\PaymentEnv;
use PrestaShop\Module\PrestashopCheckout\Exception\HttpTimeoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerConfiguration;
use PrestaShop\Module\PrestashopCheckout\Routing\Router;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\Module\PrestashopCheckout\Version\Version;
use Prestashop\ModuleLibGuzzleAdapter\ClientFactory;
use Ps_checkout;
use Psr\Log\LoggerInterface;

/**
 * Construct the client used to make call to maasland
 */
class PaymentClient extends GenericClient
{
    /**
     * @param Link $link
     * @param object|null $client
     */
    public function __construct(Link $link, $client = null)
    {
        parent::__construct();

        $this->setLink($link);

        // Client can be provided for tests
        if (null === $client) {
            /** @var Ps_checkout $module */
            $module = Module::getInstanceByName('ps_checkout');

            /** @var Version $version */
            $version = $module->getService('ps_checkout.module.version');

            /** @var LoggerConfiguration $loggerConfiguration */
            $loggerConfiguration = $module->getService('ps_checkout.logger.configuration');

            /** @var LoggerInterface $logger */
            $logger = $module->getService('ps_checkout.logger');

            /** @var Router $router */
            $router = $module->getService('ps_checkout.prestashop.router');

            $clientConfiguration = [
                'base_url' => (new PaymentEnv())->getPaymentApiUrl(),
                'verify' => $this->getVerify(),
                'timeout' => $this->timeout,
                'exceptions' => $this->catchExceptions,
                'headers' => [
                    'Content-Type' => 'application/vnd.checkout.v1+json', // api version to use (psl side)
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,  // Token we get from PsAccounts
                    'Shop-Id' => $this->shopUid,                  // Shop UUID we get from PsAccounts
                    'Hook-Url' => $router->getDispatchWebhookLink((int) Context::getContext()->shop->id),
                    'Bn-Code' => (new ShopContext())->getBnCode(),
                    'Module-Version' => $version->getSemVersion(), // version of the module
                    'Prestashop-Version' => _PS_VERSION_, // prestashop version
                ],
            ];

            if (
                $loggerConfiguration->isHttpEnabled()
                && defined('\GuzzleHttp\ClientInterface::MAJOR_VERSION')
                && class_exists(HandlerStack::class)
                && class_exists(LogMiddleware::class)
            ) {
                $handlerStack = HandlerStack::create();
                $handlerStack->push(new LogMiddleware($logger));
                $clientConfiguration['handler'] = $handlerStack;
            } elseif (
                $loggerConfiguration->isHttpEnabled()
                && defined('\GuzzleHttp\ClientInterface::VERSION')
                && class_exists(Emitter::class)
                && class_exists(LogSubscriber::class)
                && class_exists(Formatter::class)
            ) {
                $emitter = new Emitter();
                $emitter->attach(new LogSubscriber(
                    $logger,
                    Formatter::DEBUG
                ));

                $clientConfiguration['emitter'] = $emitter;
            }

            $client = (new ClientFactory())->getClient($clientConfiguration);
        }

        $this->setClient($client);
    }

    /**
     * @param array $options
     *
     * @return array
     *
     * @throws HttpTimeoutException
     */
    protected function post(array $options = [])
    {
        $delay = isset($options['delay']) ? (int) $options['delay'] : 2;
        $retries = isset($options['retries']) ? (int) $options['retries'] : 2;
        unset($options['delay'], $options['retries']);

        return $this->postWithRetry($options, $delay, $retries);
    }

    /**
     * @param array $options
     * @param int $delay
     * @param int $retries
     *
     * @return array
     *
     * @throws HttpTimeoutException
     * @throws PsCheckoutException
     */
    private function postWithRetry(array $options, $delay = 2, $retries = 2)
    {
        try {
            $response = parent::post($options);

            if ($response['httpCode'] === 401 || false !== strpos($response['exceptionMessage'], 'Unauthorized')) {
                throw new PsCheckoutException('Unauthorized', PsCheckoutException::PSCHECKOUT_HTTP_UNAUTHORIZED);
            }

            if (false !== $response['status']) {
                return $response;
            }

            if (
                isset($response['exceptionCode'])
                && $response['exceptionCode'] === PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION
                && false !== strpos($response['exceptionMessage'], 'cURL error 28')
            ) {
                throw new HttpTimeoutException($response['exceptionMessage'], PsCheckoutException::PSL_TIMEOUT);
            } elseif (
                isset($response['exceptionCode'])
                && $response['exceptionCode'] === PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION
            ) {
                throw new PsCheckoutException($response['exceptionMessage'], PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION);
            }

            if (
                isset($response['body']['message'])
                && ($response['body']['message'] === 'Error: ETIMEDOUT' || $response['body']['message'] === 'Error: ESOCKETTIMEDOUT')
            ) {
                throw new HttpTimeoutException($response['body']['message'], PsCheckoutException::PSL_TIMEOUT);
            }
        } catch (HttpTimeoutException $exception) {
            if ($this->isRouteRetryable() && $retries > 0) {
                sleep($delay);

                return $this->postWithRetry($options, $delay, $retries - 1);
            }

            throw $exception;
        }

        return $response;
    }

    /**
     * @return bool
     */
    private function isRouteRetryable()
    {
        switch ($this->getRoute()) {
            case '/payments/order/capture':
            case '/payments/order/refund':
                return false;
        }

        return true;
    }
}
