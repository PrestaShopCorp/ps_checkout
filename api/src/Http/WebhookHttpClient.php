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
use PsCheckout\Core\Webhook\WebhookException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response;

class WebhookHttpClient extends PsrHttpClientAdapter implements WebhookHttpClientInterface
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
            $decodedBody = json_decode((string) $response->getBody(), true);
            $message = $this->extractMessage(is_array($decodedBody) ? $decodedBody : []);

            if ($message) {
                throw new WebhookException($message, $response->getStatusCode(), $exception);
            }

            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function verifyWebhook(string $rawBody, array $headers): bool
    {
        $response = $this->sendRequest(new Request('POST', 'webhooks/verify', $headers, $rawBody));

        return $response->getStatusCode() === Response::HTTP_OK;
    }

    /**
     * @param array $body
     *
     * @return string
     */
    private function extractMessage(array $body): string
    {
        if (isset($body['message'])) {
            return is_array($body['message']) ? implode(',', $body['message']) : $body['message'];
        }

        if (isset($body['error']) && preg_match('/^[0-9A-Z_]+$/', $body['error']) === 1) {
            return $body['error'];
        }

        return '';
    }
}
