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

namespace PsCheckout\Tests\Unit\PayPal\OAuth;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\CheckoutHttpClientInterface;
use PsCheckout\Core\PayPal\OAuth\OAuthService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class OAuthServiceTest extends TestCase
{
    /** @var CheckoutHttpClientInterface|MockObject */
    private $httpClient;

    /** @var ResponseInterface|MockObject */
    private $response;

    /** @var StreamInterface|MockObject */
    private $stream;

    private OAuthService $oauthService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->httpClient = $this->createMock(CheckoutHttpClientInterface::class);
        $this->response = $this->createMock(ResponseInterface::class);
        $this->stream = $this->createMock(StreamInterface::class);

        $this->oauthService = new OAuthService($this->httpClient);
    }

    public function testItSuccessfullyGetsUserIdToken(): void
    {
        // Arrange
        $merchantId = 'test-merchant-id';
        $payPalCustomerId = 'test-customer-id';
        $expectedToken = 'test-id-token';

        $this->stream->method('__toString')
            ->willReturn(json_encode(['id_token' => $expectedToken]));

        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClient->expects($this->once())
            ->method('getUserIdToken')
            ->with($merchantId, $payPalCustomerId)
            ->willReturn($this->response);

        // Act
        $result = $this->oauthService->getUserIdToken($merchantId, $payPalCustomerId);

        // Assert
        $this->assertEquals($expectedToken, $result);
    }

    public function testItThrowsExceptionWhenIdTokenIsMissing(): void
    {
        // Arrange
        $merchantId = 'test-merchant-id';

        $this->stream->method('__toString')
            ->willReturn(json_encode(['other_field' => 'value']));

        $this->response->method('getBody')
            ->willReturn($this->stream);

        $this->httpClient->expects($this->once())
            ->method('getUserIdToken')
            ->with($merchantId)
            ->willReturn($this->response);

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to get PayPal User ID token.');

        // Act
        $this->oauthService->getUserIdToken($merchantId);
    }

    public function testItThrowsExceptionWhenHttpClientFails(): void
    {
        // Arrange
        $merchantId = 'test-merchant-id';
        $originalException = new Exception('HTTP Client Error');

        $this->httpClient->expects($this->once())
            ->method('getUserIdToken')
            ->with($merchantId)
            ->willThrowException($originalException);

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Failed to get PayPal User ID token.');

        // Act
        $this->oauthService->getUserIdToken($merchantId);
    }
}
