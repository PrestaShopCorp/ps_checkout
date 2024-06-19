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

namespace PrestaShop\Module\PrestashopCheckout\Http;

use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\RequestException;
use Http\Client\Exception\TransferException;
use PrestaShop\Module\PrestashopCheckout\Exception\PayPalException;
use PrestaShop\Module\PrestashopCheckout\PayPalError;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class MaaslandHttpClient implements HttpClientInterface
{
    /**
     * @var HttpClientInterface
     */
    private $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @param array $payload
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws NetworkException
     * @throws HttpException
     * @throws RequestException
     * @throws TransferException
     * @throws PayPalException
     */
    public function createOrder(array $payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/create', $options, json_encode($payload)));
    }

    /**
     * @param array $payload
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws NetworkException
     * @throws HttpException
     * @throws RequestException
     * @throws TransferException
     * @throws PayPalException
     */
    public function updateOrder(array $payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/update', $options, json_encode($payload)));
    }

    /**
     * @param array $payload
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws NetworkException
     * @throws HttpException
     * @throws RequestException
     * @throws TransferException
     * @throws PayPalException
     */
    public function fetchOrder(array $payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/fetch', $options, json_encode($payload)));
    }

    /**
     * @param array $payload
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws NetworkException
     * @throws HttpException
     * @throws RequestException
     * @throws TransferException
     * @throws PayPalException
     */
    public function captureOrder(array $payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/capture', $options, json_encode($payload)));
    }

    /**
     * @param array $payload
     * @param array $options
     *
     * @return ResponseInterface
     *
     * @throws NetworkException
     * @throws HttpException
     * @throws RequestException
     * @throws TransferException
     * @throws PayPalException
     */
    public function refundOrder(array $payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/refund', $options, json_encode($payload)));
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws NetworkException
     * @throws HttpException
     * @throws RequestException
     * @throws TransferException
     * @throws PayPalException
     */
    public function sendRequest(RequestInterface $request)
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $message = $this->extractMessage(json_decode($response->getBody(), true));

            if ($message) {
                (new PayPalError($message))->throwException($exception);
            }

            throw $exception;
        }

        return $response;
    }

    /**
     * @param array $body
     *
     * @return string
     */
    private function extractMessage(array $body)
    {
        if (isset($body['details'][0]['issue']) && preg_match('/^[0-9A-Z_]+$/', $body['details'][0]['issue']) === 1) {
            return $body['details'][0]['issue'];
        }

        if (isset($body['error']) && preg_match('/^[0-9A-Z_]+$/', $body['error']) === 1) {
            return $body['error'];
        }

        if (isset($body['message']) && preg_match('/^[0-9A-Z_]+$/', $body['message']) === 1) {
            return $body['message'];
        }

        if (isset($body['name']) && preg_match('/^[0-9A-Z_]+$/', $body['name']) === 1) {
            return $body['name'];
        }

        return '';
    }

    /**
     * Tells if the webhook came from the PSL
     *
     * @param array $payload
     *
     * @return array
     */
    public function getShopSignature(array $payload, array $options = [])
    {
        $response = $this->sendRequest(new Request('POST', '/payments/shop/verify_webhook_signature', $options, json_encode($payload)));

        return json_decode($response->getBody(), true);
    }

    /**
     * Used to notify PSL on settings update
     *
     * @return array
     *
     * @throws PayPalException
     */
    public function updateSettings(array $payload)
    {
        $response = $this->sendRequest(new Request('POST', '/payments/shop/update_settings', [], json_encode($payload)));

        return json_decode($response->getBody(), true);
    }
}
