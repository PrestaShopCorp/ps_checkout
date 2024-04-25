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

use Http\Client\Exception\HttpException;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\TransferException;
use Prestashop\ModuleLibGuzzleAdapter\ClientFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

class PsrHttpClientAdapter implements HttpClientInterface
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->client = (new ClientFactory())->getClient($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        try {
            $response = $this->client->sendRequest($request);
        } catch (\GuzzleHttp\Ring\Exception\ConnectException $exception) { // @phpstan-ignore-line
            // Guzzle 5.3 use RingPHP for the low level connection
            throw new NetworkException($exception->getMessage(), $request, $exception); // @phpstan-ignore-line
        } catch (\GuzzleHttp\Ring\Exception\RingException $exception) { // @phpstan-ignore-line
            // Guzzle 5.3 use RingPHP for the low level connection
            throw new TransferException($exception->getMessage(), 0, $exception); // @phpstan-ignore-line
        }

        // Guzzle 5.3 does not throw exceptions on 4xx and 5xx status codes
        if ($response->getStatusCode() >= 400) {
            throw new HttpException($response->getReasonPhrase(), $request, $response);
        }

        return $response;
    }
}
