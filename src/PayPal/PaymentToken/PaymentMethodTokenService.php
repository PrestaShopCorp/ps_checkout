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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken;

use Exception;
use GuzzleHttp\Psr7\Request;
use PrestaShop\Module\PrestashopCheckout\Http\CheckoutHttpClient;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;

class PaymentMethodTokenService
{
    private $httpClient;
    /**
     * @var CheckoutHttpClient
     */
    private $checkoutHttpClient;
    /**
     * @var PayPalConfiguration
     */
    private $payPalConfiguration;

    public function __construct($httpClient, CheckoutHttpClient $checkoutHttpClient, PayPalConfiguration $payPalConfiguration)
    {
        $this->httpClient = $httpClient;
        $this->checkoutHttpClient = $checkoutHttpClient;
        $this->payPalConfiguration = $payPalConfiguration;
    }

    /**
     * @param PayPalCustomerId $customerId
     *
     * @return array
     *
     * @throws Exception
     */
    public function fetchPaymentMethodTokens(PayPalCustomerId $customerId)
    {
        try {
            $request = new Request('GET', 'https://api.paypal.com/v3/vault/payment-tokens&customer_id=' . $customerId->getValue(), []);
            $response = $this->httpClient->sendRequest($request);

            $data = json_decode($response->getBody(), true);

            if (empty($data['payment_tokens'])) {
                throw new Exception('Failed to fetch PayPal Payment Method tokens from response.');
            }

            return $data['payment_tokens'];
        } catch (Exception $exception) {
            throw new Exception('Failed to fetch PayPal Payment Method tokens.', 0, $exception);
        }
    }

    public function deletePaymentToken(PaymentTokenId $paymentTokenId)
    {
        try {
            $response = $this->checkoutHttpClient->deletePaymentToken($this->payPalConfiguration->getMerchantId(), $paymentTokenId);

            if ($response->getStatusCode() !== 204) {
                throw new Exception('Failed to delete payment token', $response->getStatusCode());
            }
        } catch (Exception $exception) {
            throw new Exception('Failed to delete payment token', 0, $exception);
        }
    }
}
