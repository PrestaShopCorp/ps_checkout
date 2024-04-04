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

use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\HttpException;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\RequestException;
use Http\Client\Exception\TransferException;
use PrestaShop\Module\PrestashopCheckout\Builder\Configuration\CheckoutClientConfigurationBuilder;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class CheckoutHttpClient extends PsrHttpClientAdapter implements CheckoutHttpClientInterface
{
    const SUFFIX_IDENTITY = '/v1/identity';
    const SUFFIX_ORDER = '/v1/order';
    const SUFFIX_VAULT = '/v1/vault-merchant';

    public function __construct(CheckoutClientConfigurationBuilder $configurationBuilder)
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
    public function sendRequest(RequestInterface $request)
    {
        try {
            return parent::sendRequest($request);
        } catch (NetworkException $exception) {
            throw $exception;
            // Thrown when the request cannot be completed because of network issues.
            // No response here
        } catch (HttpException $exception) {
            // Thrown when a response was received but the request itself failed.
            // There a response here
            // So this one contains why response failed with Maasland error response
            if ($exception->getResponse()->getStatusCode() === 500) {
                // Internal Server Error: retry then stop using Maasland for XXX times after X failed retries, requires a circuit breaker
            }
            if ($exception->getResponse()->getStatusCode() === 503) {
                // Service Unavailable: we should stop using Maasland, requires a circuit breaker
            }
            // response status code 4XX throw exception to be catched on specific method
            throw $exception; // Avoid this to be catched next
        } catch (RequestException $exception) {
            throw $exception;
            // No response here
        } catch (TransferException $exception) {
            throw $exception;
            // others without response
        }
    }

    /**
     * @param string $payload //Payload JSON
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function createOrder($payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/create', $options, $payload));
    }

    /**
     * @param string $payload
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function updateOrder($payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/update', $options, $payload));
    }

    /**
     * @param string $payload
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function fetchOrder($payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/fetch', $options, $payload));
    }

    /**
     * @param string $payload
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function captureOrder($payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/capture', $options, $payload));
    }

    /**
     * @param string $payload
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function refundOrder($payload, array $options = [])
    {
        return $this->sendRequest(new Request('POST', '/payments/order/refund', $options, $payload));
    }

    /**
     * @param string $merchantId
     * @param PaymentTokenId $paymentTokenId
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function getPaymentTokenStatus($merchantId, PaymentTokenId $paymentTokenId, array $options = [])
    {
        $tokenId = $paymentTokenId->getValue();

        return $this->sendRequest(new Request('GET', self::SUFFIX_VAULT . "/payment-token/$merchantId/$tokenId/status", $options));
    }

    /**
     * @param string $merchantId
     * @param PaymentTokenId $paymentTokenId
     * @param array $options
     *
     * @return ResponseInterface
     */
    public function deletePaymentToken($merchantId, PaymentTokenId $paymentTokenId, array $options = [])
    {
        $tokenId = $paymentTokenId->getValue();

        return $this->sendRequest(new Request('DELETE', self::SUFFIX_VAULT . "/payment-token/$merchantId/$tokenId", $options));
    }

    public function getUserIdToken($merchantId, PayPalCustomerId $payPalCustomerId = null, $options = [])
    {
        $payload = [
            'payer_id' => $merchantId,
        ];

        if ($payPalCustomerId) {
            $payload['customer_id'] = $payPalCustomerId->getValue();
        }

        return $this->sendRequest(
            new Request(
                'POST',
                self::SUFFIX_IDENTITY . '/oauth2/token',
                $options,
                json_encode($payload)
            )
        );
    }
}
