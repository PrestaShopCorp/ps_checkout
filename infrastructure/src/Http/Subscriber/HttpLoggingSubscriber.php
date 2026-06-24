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

namespace PsCheckout\Infrastructure\Http\Subscriber;

use GuzzleHttp\Event\BeforeEvent;
use GuzzleHttp\Event\CompleteEvent;
use GuzzleHttp\Event\ErrorEvent;
use GuzzleHttp\Event\SubscriberInterface;
use Psr\Log\LoggerInterface;

/**
 * Guzzle 5 event subscriber equivalent to HttpLoggingMiddleware for Guzzle 6/7.
 * Only loaded on PS 1.7 where Guzzle 5.3 is provided by the PrestaShop Core.
 */
class HttpLoggingSubscriber implements SubscriberInterface
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

    /**
     * Start times keyed by spl_object_hash of the request object.
     *
     * @var float[]
     */
    private $startTimes = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getEvents()
    {
        return [
            'before' => ['onBefore'],
            'complete' => ['onComplete'],
            'error' => ['onError'],
        ];
    }

    /**
     * @param BeforeEvent $event
     *
     * @return void
     */
    public function onBefore(BeforeEvent $event)
    {
        $request = $event->getRequest();
        $this->startTimes[spl_object_hash($request)] = microtime(true);

        $this->logger->debug('HTTP request', [
            'method' => $request->getMethod(),
            'url' => $request->getUrl(),
            'headers' => $this->filterRequestHeaders($request->getHeaders()),
            'body' => $this->readBody($request->getBody()),
        ]);
    }

    /**
     * @param CompleteEvent $event
     *
     * @return void
     */
    public function onComplete(CompleteEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $this->logger->info('HTTP response', [
            'method' => $request->getMethod(),
            'url' => $request->getUrl(),
            'status_code' => $response->getStatusCode(),
            'duration_ms' => $this->getDuration($request),
            'headers' => $this->filterResponseHeaders($response->getHeaders()),
            'body' => $this->readBody($response->getBody()),
        ]);

        unset($this->startTimes[spl_object_hash($request)]);
    }

    /**
     * @param ErrorEvent $event
     *
     * @return void
     */
    public function onError(ErrorEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getException();

        $context = [
            'method' => $request->getMethod(),
            'url' => $request->getUrl(),
            'duration_ms' => $this->getDuration($request),
            'error' => $exception->getMessage(),
        ];

        if ($exception->getResponse() !== null) {
            $context['status_code'] = $exception->getResponse()->getStatusCode();
            $context['headers'] = $this->filterResponseHeaders($exception->getResponse()->getHeaders());
            $context['body'] = $this->readBody($exception->getResponse()->getBody());
        }

        $this->logger->error('HTTP request failed', $context);

        unset($this->startTimes[spl_object_hash($request)]);
    }

    /**
     * @param object $request
     *
     * @return float
     */
    private function getDuration($request)
    {
        $hash = spl_object_hash($request);

        if (!isset($this->startTimes[$hash])) {
            return 0.0;
        }

        return round((microtime(true) - $this->startTimes[$hash]) * 1000);
    }

    /**
     * Reads a Guzzle 5 stream body without consuming it (seeks back to 0 if seekable).
     *
     * @param object|null $body GuzzleHttp\Stream\StreamInterface
     *
     * @return string
     */
    private function readBody($body)
    {
        if ($body === null) {
            return '';
        }

        if (!$body->isSeekable()) {
            return '';
        }

        $content = (string) $body;
        $body->seek(0);

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
