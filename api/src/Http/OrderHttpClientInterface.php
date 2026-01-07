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

use PsCheckout\Api\Dto\PayPal\Order\CreateOrderRequestDto;
use PsCheckout\Api\Dto\PayPal\Order\CreateOrderResponseDto;
use PsCheckout\Api\Dto\PayPal\Order\GetOrderResponseDto;
use PsCheckout\Api\Dto\PayPal\Order\OrderAuthorizeRequestDto;
use PsCheckout\Api\Dto\PayPal\Order\OrderAuthorizeResponseDto;
use PsCheckout\Api\Dto\PayPal\Order\OrderCaptureRequestDto;
use PsCheckout\Api\Dto\PayPal\Order\OrderCaptureResponseDto;
use PsCheckout\Api\Dto\PayPal\Order\UpdateOrderResponseDto;
use Psr\Http\Message\ResponseInterface;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\RequestException;
use Http\Client\Exception\TransferException;
use PsCheckout\Api\Http\Exception\PayPalException;

interface OrderHttpClientInterface
{
    /**
     * @param CreateOrderRequestDto $payload
     * @param string|null $requestId
     * @param string|null $clientMetadataId
     *
     * @return CreateOrderResponseDto
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function createOrder(CreateOrderRequestDto $payload, ?string $requestId, ?string $clientMetadataId): CreateOrderResponseDto;

    /**
     * @param string $orderId
     *
     * @return GetOrderResponseDto
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function fetchOrder(string $orderId): GetOrderResponseDto;

    /**
     * @param OrderCaptureRequestDto $payload
     * @param string $orderId
     * @param string|null $requestId
     * @param string|null $clientMetadataId

     *
     * @return OrderCaptureResponseDto
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function captureOrder(string $orderId, ?OrderCaptureRequestDto $payload = null, ?string $requestId = null, ?string $clientMetadataId = null): OrderCaptureResponseDto;

    /**
     * @param array $payload
     * @param string $orderId
     *
     * @return UpdateOrderResponseDto
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function updateOrder(string $orderId, array $payload): UpdateOrderResponseDto;

    /**
     * @param OrderAuthorizeRequestDto $payload
     * @param string $orderId
     *
     * @return OrderAuthorizeResponseDto
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function authorizeOrder(string $orderId, OrderAuthorizeRequestDto $payload): OrderAuthorizeResponseDto;
}
