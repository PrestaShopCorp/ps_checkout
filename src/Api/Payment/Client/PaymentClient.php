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

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Environment\PaymentEnv;
use PrestaShop\Module\PrestashopCheckout\Exception\HttpTimeoutException;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;

/**
 * Construct the client used to make call to maasland
 */
class PaymentClient extends GenericClient
{
    public function __construct(\Link $link, Client $client = null)
    {
        $this->setLink($link);

        // Client can be provided for tests
        if (null === $client) {
            $client = new Client([
                'base_url' => (new PaymentEnv())->getPaymentApiUrl(),
                'defaults' => [
                    'verify' => $this->getVerify(),
                    'timeout' => $this->timeout,
                    'exceptions' => $this->catchExceptions,
                    'headers' => [
                        'Content-Type' => 'application/vnd.checkout.v1+json', // api version to use (psl side)
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . (new Token())->getToken(),
                        'Shop-Id' => (new ShopUuidManager())->getForShop((int) \Context::getContext()->shop->id),
                        'Hook-Url' => $this->link->getModuleLink(
                            'ps_checkout',
                            'DispatchWebHook',
                            [],
                            true,
                            null,
                            (int) \Context::getContext()->shop->id
                        ),
                        'Bn-Code' => (new ShopContext())->getBnCode(),
                        'Module-Version' => \Ps_checkout::VERSION, // version of the module
                        'Prestashop-Version' => _PS_VERSION_, // prestashop version
                    ],
                ],
            ]);
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
     */
    private function postWithRetry(array $options, $delay = 2, $retries = 2)
    {
        try {
            $response = parent::post($options);

            if (false !== $response['status']) {
                return $response;
            }

            if (isset($response['exceptionCode'])
                && $response['exceptionCode'] === PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION
                && false !== strpos($response['exceptionMessage'], 'cURL error 28')
            ) {
                throw new HttpTimeoutException($response['exceptionMessage'], PsCheckoutException::PSL_TIMEOUT);
            }

            if (isset($response['body']['message'])
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
