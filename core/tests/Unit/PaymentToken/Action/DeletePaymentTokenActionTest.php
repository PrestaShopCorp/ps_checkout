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

namespace PsCheckout\Tests\Unit\PaymentToken\Action;

use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PsCheckout\Api\Http\CheckoutHttpClientInterface;
use PsCheckout\Core\PaymentToken\Action\DeletePaymentTokenAction;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PaymentToken\ValueObject\PaymentToken;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use Psr\Http\Message\ResponseInterface;

class DeletePaymentTokenActionTest extends TestCase
{
    /** @var DeletePaymentTokenAction */
    private $action;

    /** @var PaymentTokenRepositoryInterface|MockObject */
    private $paymentTokenRepository;

    /** @var CheckoutHttpClientInterface|MockObject */
    private $checkoutHttpClient;

    /** @var ConfigurationInterface|MockObject */
    private $configuration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->paymentTokenRepository = $this->createMock(PaymentTokenRepositoryInterface::class);
        $this->checkoutHttpClient = $this->createMock(CheckoutHttpClientInterface::class);
        $this->configuration = $this->createMock(ConfigurationInterface::class);

        $this->action = new DeletePaymentTokenAction(
            $this->paymentTokenRepository,
            $this->checkoutHttpClient,
            $this->configuration
        );
    }

    /**
     * @dataProvider provideSuccessfulDeletionScenarios
     */
    public function testItSuccessfullyDeletesToken(string $vaultId, int $customerId, string $merchantId): void
    {
        // Arrange
        $token = $this->createMock(PaymentToken::class);
        $token->method('getId')->willReturn($vaultId);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(204);

        $this->paymentTokenRepository->expects($this->once())
            ->method('getAllByCustomerId')
            ->with($customerId)
            ->willReturn([$token]);

        $this->configuration->expects($this->once())
            ->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
            ->willReturn($merchantId);

        $this->checkoutHttpClient->expects($this->once())
            ->method('deletePaymentToken')
            ->with($merchantId, $vaultId)
            ->willReturn($response);

        $this->paymentTokenRepository->expects($this->once())
            ->method('delete')
            ->with($vaultId);

        // Act
        $this->action->execute($vaultId, $customerId);
    }

    public function provideSuccessfulDeletionScenarios(): array
    {
        return [
            'standard_token' => [
                'vaultId' => 'token-123',
                'customerId' => 42,
                'merchantId' => 'merchant-456',
            ],
            'long_token_id' => [
                'vaultId' => 'token-' . str_repeat('a', 50),
                'customerId' => 99,
                'merchantId' => 'merchant-789',
            ],
            'numeric_customer_id' => [
                'vaultId' => 'token-xyz',
                'customerId' => 123456,
                'merchantId' => 'merchant-abc',
            ],
        ];
    }

    /**
     * @dataProvider provideTokenOwnershipScenarios
     */
    public function testItValidatesTokenOwnership(
        string $requestedVaultId,
        array $existingTokens,
        string $expectedExceptionMessage
    ): void {
        // Arrange
        $customerId = 42;
        $tokens = array_map(function ($tokenId) {
            $token = $this->createMock(PaymentToken::class);
            $token->method('getId')->willReturn($tokenId);

            return $token;
        }, $existingTokens);

        $this->paymentTokenRepository->expects($this->once())
            ->method('getAllByCustomerId')
            ->with($customerId)
            ->willReturn($tokens);

        $this->paymentTokenRepository->expects($this->never())
            ->method('delete');

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        // Act
        $this->action->execute($requestedVaultId, $customerId);
    }

    public function provideTokenOwnershipScenarios(): array
    {
        return [
            'no_tokens_exist' => [
                'requestedVaultId' => 'token-123',
                'existingTokens' => [],
                'expectedExceptionMessage' => 'Failed to remove saved payment token',
            ],
            'token_not_in_list' => [
                'requestedVaultId' => 'token-123',
                'existingTokens' => ['other-token-1', 'other-token-2'],
                'expectedExceptionMessage' => 'Failed to remove saved payment token',
            ],
            'similar_but_different_token' => [
                'requestedVaultId' => 'token-123',
                'existingTokens' => ['token-1234', 'token-12'],
                'expectedExceptionMessage' => 'Failed to remove saved payment token',
            ],
        ];
    }

    /**
     * @dataProvider providePayPalErrorScenarios
     */
    public function testItHandlesPayPalErrors(
        int $statusCode,
        ?string $clientException,
        string $expectedMessage
    ): void {
        // Arrange
        $vaultId = 'token-123';
        $customerId = 42;
        $merchantId = 'merchant-456';

        $token = $this->createMock(PaymentToken::class);
        $token->method('getId')->willReturn($vaultId);

        $this->paymentTokenRepository->expects($this->once())
            ->method('getAllByCustomerId')
            ->with($customerId)
            ->willReturn([$token]);

        $this->configuration->expects($this->once())
            ->method('get')
            ->with(PayPalConfiguration::PS_CHECKOUT_PAYPAL_ID_MERCHANT)
            ->willReturn($merchantId);

        if ($clientException) {
            $this->checkoutHttpClient->expects($this->once())
                ->method('deletePaymentToken')
                ->willThrowException(new Exception($clientException));
        } else {
            $response = $this->createMock(ResponseInterface::class);
            $response->method('getStatusCode')->willReturn($statusCode);

            $this->checkoutHttpClient->expects($this->once())
                ->method('deletePaymentToken')
                ->willReturn($response);
        }

        // Assert
        $this->expectException(Exception::class);
        $this->expectExceptionMessage($expectedMessage);

        // Act
        $this->action->execute($vaultId, $customerId);
    }

    public function providePayPalErrorScenarios(): array
    {
        return [
            'unauthorized_error' => [
                'statusCode' => 401,
                'clientException' => null,
                'expectedMessage' => 'Failed to delete payment token',
            ],
            'server_error' => [
                'statusCode' => 500,
                'clientException' => null,
                'expectedMessage' => 'Failed to delete payment token',
            ],
            'network_error' => [
                'statusCode' => 0,
                'clientException' => 'Network connection error',
                'expectedMessage' => 'Failed to delete payment token',
            ],
            'timeout_error' => [
                'statusCode' => 0,
                'clientException' => 'Request timed out',
                'expectedMessage' => 'Failed to delete payment token',
            ],
        ];
    }
}
