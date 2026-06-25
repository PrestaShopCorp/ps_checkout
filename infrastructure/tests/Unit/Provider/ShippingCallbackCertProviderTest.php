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

namespace Tests\Unit\PsCheckout\Infrastructure\Provider;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use PsCheckout\Api\Http\HttpClientInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Cache\ShippingCallbackCache;
use PsCheckout\Core\PayPal\ShippingCallback\Cache\ShippingCallbackCacheInterface;
use PsCheckout\Infrastructure\Provider\ShippingCallbackCertProvider;

class ShippingCallbackCertProviderTest extends TestCase
{
    private const CERT_URL = 'https://api.paypal.com/certs/test';

    /** @var HttpClientInterface|MockObject */
    private $httpClient;

    /** @var ShippingCallbackCacheInterface|MockObject */
    private $cache;

    /** @var ShippingCallbackCertProvider */
    private $provider;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->cache = $this->createMock(ShippingCallbackCacheInterface::class);
        $this->provider = new ShippingCallbackCertProvider($this->httpClient, $this->cache);
    }

    public function testReturnsCachedCertWithoutHttpCall(): void
    {
        $certPem = $this->makeValidCertPem();

        $this->cache->method('has')->willReturn(true);
        $this->cache->method('getValue')->willReturn($certPem);
        $this->httpClient->expects($this->never())->method('sendRequest');

        $result = $this->provider->getCert(self::CERT_URL);

        $this->assertSame($certPem, $result);
    }

    public function testFetchesCachesAndReturnsFreshValidCert(): void
    {
        $certPem = $this->makeValidCertPem();

        $this->cache->method('has')->willReturn(false);
        $this->httpClient->method('sendRequest')->willReturn($this->makeHttpResponse($certPem));
        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->anything(), $certPem, $this->greaterThan(0));

        $result = $this->provider->getCert(self::CERT_URL);

        $this->assertSame($certPem, $result);
    }

    public function testThrowsAndCachesWithMinTtlWhenFreshCertIsExpired(): void
    {
        $certPem = $this->makeExpiredCertPem();

        $this->cache->method('has')->willReturn(false);
        $this->httpClient->method('sendRequest')->willReturn($this->makeHttpResponse($certPem));
        $this->cache->expects($this->once())
            ->method('set')
            ->with($this->anything(), $certPem, ShippingCallbackCache::MIN_TTL);

        $this->expectException(\RuntimeException::class);

        $this->provider->getCert(self::CERT_URL);
    }

    public function testThrowsWhenCachedCertHasExpired(): void
    {
        $certPem = $this->makeExpiredCertPem();

        $this->cache->method('has')->willReturn(true);
        $this->cache->method('getValue')->willReturn($certPem);
        $this->httpClient->expects($this->never())->method('sendRequest');

        $this->expectException(\RuntimeException::class);

        $this->provider->getCert(self::CERT_URL);
    }

    private function makeValidCertPem(): string
    {
        return $this->generateSelfSignedCert(365);
    }

    private function makeExpiredCertPem(): string
    {
        return $this->generateSelfSignedCert(-1);
    }

    private function generateSelfSignedCert(int $validityDays): string
    {
        $key = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
        ]);
        $csr = openssl_csr_new(['CN' => 'PayPal Test Cert'], $key, ['digest_alg' => 'sha256']);
        $x509 = openssl_csr_sign($csr, null, $key, $validityDays, ['digest_alg' => 'sha256']);
        openssl_x509_export($x509, $pem);

        return (string) $pem;
    }

    /**
     * @return ResponseInterface|MockObject
     */
    private function makeHttpResponse(string $body): ResponseInterface
    {
        $stream = $this->createMock(StreamInterface::class);
        $stream->method('__toString')->willReturn($body);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getBody')->willReturn($stream);

        return $response;
    }
}
