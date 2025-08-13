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

namespace PsCheckout\Api\Http;

use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\HttpException;
use PsCheckout\Api\Http\Configuration\HttpClientConfigurationBuilderInterface;
use PsCheckout\Api\Http\Exception\PayPalError;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OrderHttpClient extends PsrHttpClientAdapter implements OrderHttpClientInterface
{
    public function __construct(HttpClientConfigurationBuilderInterface $configurationBuilder)
    {
        parent::__construct($configurationBuilder->build());
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return parent::sendRequest($request);
        } catch (HttpException $exception) {
            $response = $exception->getResponse();
            $body = json_decode($response->getBody(), true);
            $message = $this->extractMessage($body);

            if ($message) {
                (new PayPalError($message))->throwException($exception);
            }

            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createOrder(array $payload): ResponseInterface
    {
        return $this->sendRequest(new Request('POST', '/payments/order/create', [], json_encode($payload)));
    }

    /**
     * {@inheritdoc}
     */
    public function fetchOrder(array $payload): ResponseInterface
    {
        return $this->sendRequest(new Request('POST', '/payments/order/fetch', [], json_encode($payload)));
    }

    /**
     * {@inheritdoc}
     */
    public function captureOrder(array $payload): ResponseInterface
    {
        return $this->sendRequest(new Request('POST', '/payments/order/capture', [], json_encode($payload)));
    }

    /**
     * @inheritDoc
     */
    public function updateOrder(array $payload): ResponseInterface
    {
        return $this->sendRequest(new Request('POST', '/payments/order/update', [], json_encode($payload)));
    }

    /**
     * {@inheritdoc}
     */
    public function refundOrder(array $payload): ResponseInterface
    {
        return $this->sendRequest(new Request('POST', '/payments/order/refund', [], json_encode($payload)));
    }

    /**
     * {@inheritdoc}
     */
    public function getShopSignature(array $payload): array
    {
        $response = $this->sendRequest(new Request('POST', '/payments/shop/verify_webhook_signature', [], json_encode($payload)));

        return json_decode($response->getBody(), true);
    }

    /**
     * @param array $body
     *
     * @return string
     */
    private function extractMessage(array $body): string
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
}
