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

namespace PsCheckout\Infrastructure\Http\Middleware;

use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class HttpLoggingMiddleware
{
    /**
     * Request headers to retain. Everything else is dropped to reduce noise.
     * Authorization is intentionally excluded (sensitive).
     *
     * @var string[]
     */
    private static $allowedRequestHeaders = [
        'host',
        'checkout-shop-id',
        'checkout-hook-url',
        'checkout-bn-code',
        'checkout-module-version',
        'checkout-prestashop-version',
        'paypal-merchant-id',
    ];

    /**
     * Response headers to retain. Focused on correlation and operationally
     * useful signals (rate-limiting, upstream latency, Cloudflare ray ID…).
     *
     * @var string[]
     */
    private static $allowedResponseHeaders = [
        'date',
        'retry-after',
        'x-request-id',
        'request-id',
        'cf-ray',
        'x-envoy-upstream-service-time',
    ];

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Returns a Guzzle middleware callable that logs HTTP requests and responses.
     *
     * @param callable $handler
     *
     * @return callable
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $startTime = microtime(true);

            $this->logger->debug('HTTP request', [
                'method' => $request->getMethod(),
                'url' => (string) $request->getUri(),
                'headers' => $this->filterRequestHeaders($request->getHeaders()),
                'body' => $this->readBody($request->getBody()),
            ]);

            return $handler($request, $options)->then(
                function (ResponseInterface $response) use ($request, $startTime) {
                    $this->logger->info('HTTP response', [
                        'method' => $request->getMethod(),
                        'url' => (string) $request->getUri(),
                        'status_code' => $response->getStatusCode(),
                        'duration_ms' => round((microtime(true) - $startTime) * 1000),
                        'headers' => $this->filterResponseHeaders($response->getHeaders()),
                        'body' => $this->readBody($response->getBody()),
                    ]);

                    return $response;
                },
                function ($reason) use ($request, $startTime) {
                    $context = [
                        'method' => $request->getMethod(),
                        'url' => (string) $request->getUri(),
                        'duration_ms' => round((microtime(true) - $startTime) * 1000),
                    ];

                    if ($reason instanceof RequestException && $reason->getResponse() !== null) {
                        $context['status_code'] = $reason->getResponse()->getStatusCode();
                        $context['headers'] = $this->filterResponseHeaders($reason->getResponse()->getHeaders());
                        $context['body'] = $this->readBody($reason->getResponse()->getBody());
                    }

                    $context['error'] = $reason instanceof \Exception ? $reason->getMessage() : (string) $reason;

                    $this->logger->error('HTTP request failed', $context);

                    if ($reason instanceof \Exception) {
                        throw $reason;
                    }

                    return new RejectedPromise($reason);
                }
            );
        };
    }

    /**
     * Reads a PSR-7 stream without consuming it (rewinds if seekable).
     *
     * @param StreamInterface|null $stream
     *
     * @return string
     */
    private function readBody($stream)
    {
        if ($stream === null) {
            return '';
        }

        if (!$stream->isSeekable()) {
            return '';
        }

        $content = (string) $stream;
        $stream->rewind();

        return $content;
    }

    /**
     * Returns only the allowed request headers (allowlist). Everything else is dropped.
     *
     * @param array $headers
     *
     * @return array
     */
    private function filterRequestHeaders(array $headers)
    {
        return $this->filterHeaders($headers, self::$allowedRequestHeaders);
    }

    /**
     * Returns only the allowed response headers (allowlist). Everything else is dropped.
     *
     * @param array $headers
     *
     * @return array
     */
    private function filterResponseHeaders(array $headers)
    {
        return $this->filterHeaders($headers, self::$allowedResponseHeaders);
    }

    /**
     * @param array    $headers
     * @param string[] $allowlist lowercase header names to retain
     *
     * @return array
     */
    private function filterHeaders(array $headers, array $allowlist)
    {
        $filtered = [];
        foreach ($headers as $name => $values) {
            if (in_array(strtolower($name), $allowlist, true)) {
                $filtered[$name] = $values;
            }
        }

        return $filtered;
    }
}
