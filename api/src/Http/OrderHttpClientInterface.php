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

use Psr\Http\Message\ResponseInterface;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\RequestException;
use Http\Client\Exception\TransferException;
use PsCheckout\Api\Http\Exception\PayPalException;

interface OrderHttpClientInterface
{
    /**
     * @param array $payload
     *
     * @return ResponseInterface
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function createOrder(array $payload): ResponseInterface;

    /**
     * @param string $orderId
     *
     * @return ResponseInterface
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function fetchOrder(string $orderId): ResponseInterface;

    /**
     * @param array $payload
     * @param string $orderId
     *
     * @return ResponseInterface
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function captureOrder(string $orderId, array $payload): ResponseInterface;

    /**
     * @param array $payload
     *
     * @return ResponseInterface
     *
     * @throws NetworkException|HttpException|RequestException|TransferException|PayPalException
     */
    public function updateOrder(array $payload): ResponseInterface;
}
