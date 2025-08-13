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
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\RequestException;
use Http\Client\Exception\TransferException;
use PsCheckout\Api\Http\Configuration\HttpClientConfigurationBuilderInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CheckoutHttpClient extends PsrHttpClientAdapter implements CheckoutHttpClientInterface
{
    const SUFFIX_IDENTITY = '/v1/identity';

    const SUFFIX_VAULT = '/v1/vault-merchant';

    public function __construct(HttpClientConfigurationBuilderInterface $configurationBuilder)
    {
        parent::__construct($configurationBuilder->build());
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     *
     * @throws HttpException|RequestException|TransferException|NetworkException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            return parent::sendRequest($request);
        } catch (NetworkException $exception) {
            // Thrown when the request cannot be completed because of network issues.
            // No response here
            throw $exception;
        } catch (HttpException $exception) {
            // Thrown when a response was received but the request itself failed.
            // There a response here
            throw $exception;
        } catch (RequestException $exception) {
            // No response here
            throw $exception;
        } catch (TransferException $exception) {
            // others without response
            throw $exception;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getUserIdToken(string $merchantId, $payPalCustomerId = null): ResponseInterface
    {
        $payload = [
            'payer_id' => $merchantId,
        ];

        if ($payPalCustomerId) {
            $payload['customer_id'] = $payPalCustomerId;
        }

        return $this->sendRequest(
            new Request(
                'POST',
                self::SUFFIX_IDENTITY . '/oauth2/token',
                [],
                json_encode($payload)
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function deletePaymentToken(string $merchantId, string $vaultId): ResponseInterface
    {
        return $this->sendRequest(new Request('DELETE', self::SUFFIX_VAULT . "/payment-token/$merchantId/$vaultId"));
    }
}
