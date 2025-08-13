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
use PsCheckout\Api\Http\Configuration\OrderShipmentTrackingConfigurationBuilderInterface;
use PsCheckout\Api\Http\Exception\PayPalError;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OrderShipmentTrackingHttpClient extends PsrHttpClientAdapter implements OrderShipmentTrackingHttpClientInterface
{
    public function __construct(OrderShipmentTrackingConfigurationBuilderInterface $configurationBuilder)
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
            $message = $this->extractMessage($body ?? []);

            if ($message) {
                (new PayPalError($message))->throwException($exception);
            }

            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function addTracking(array $payload): ResponseInterface
    {
        return $this->sendRequest(new Request('POST', "trackers", [], json_encode($payload)));
    }

    /**
     * {@inheritdoc}
     */
    public function updateTracking(string $trackerId, array $payload): ResponseInterface
    {
        return $this->sendRequest(new Request('PATCH', "trackers/{$trackerId}", [], json_encode($payload)));
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

        if (isset($body['message'])) {
            $message = is_array($body['message']) ? reset($body['message']) : $body['message'];

            if (is_string($message) && preg_match('/^[a-zA-Z0-9_-]+$/', $message) === 1) {
                return $message;
            }
        }

        if (isset($body['name']) && preg_match('/^[0-9A-Z_]+$/', $body['name']) === 1) {
            return $body['name'];
        }

        return '';
    }
}
