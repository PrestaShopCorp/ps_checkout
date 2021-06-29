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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment;

use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PaymentClient;

/**
 * Handle order requests
 */
class Order extends PaymentClient
{
    /**
     * Create order to paypal api
     *
     * @param string $payload Cart details (json)
     *
     * @return array data with paypal order id or false if error
     */
    public function create($payload)
    {
        $this->setRoute('/payments/order/create');

        return $this->post([
            'json' => $payload,
        ]);
    }

    /**
     * Capture order funds
     *
     * @param string $orderId paypal
     * @param string $merchantId
     * @param string $fundingSource
     *
     * @return array response from paypal if the payment is accepted or false if error occured
     */
    public function capture($orderId, $merchantId, $fundingSource)
    {
        $this->setRoute('/payments/order/capture');

        return $this->post([
            'json' => json_encode([
                'mode' => $fundingSource,
                'orderId' => (string) $orderId,
                'payee' => [
                    'merchant_id' => $merchantId,
                ],
            ]),
        ]);
    }

    /**
     * Get paypal order details
     *
     * @param string $orderId paypal
     *
     * @return array paypal order
     */
    public function fetch($orderId)
    {
        $this->setRoute('/payments/order/fetch');

        return $this->post([
            'json' => json_encode([
                'orderId' => $orderId,
            ]),
        ]);
    }

    /**
     * Authorize an order
     *
     * @param string $orderId paypal
     * @param string $merchantId
     *
     * @return array paypal order
     */
    public function authorize($orderId, $merchantId)
    {
        // TODO : waiting maasland integration
        return [];
    }

    /**
     * Refund an order
     *
     * @param array $payload
     *
     * @return array paypal order
     */
    public function refund($payload)
    {
        $this->setRoute('/payments/order/refund');

        return $this->post([
            'json' => json_encode($payload),
        ]);
    }

    /**
     * Patch paypal order
     *
     * @param string $payload
     *
     * @return array response from paypal if the payment is accepted or false if error occured
     */
    public function patch($payload)
    {
        $this->setRoute('/payments/order/update');

        return $this->post([
            'json' => $payload,
        ]);
    }

    /**
     * @param string $merchantId
     *
     * @return array
     */
    public function generateClientToken($merchantId)
    {
        $this->setRoute('/payments/order/generate_client_token');

        return $this->post([
            'json' => [
                'return_payload' => true,
                'payee' => [
                    'merchant_id' => $merchantId,
                ],
            ],
        ]);
    }
}
