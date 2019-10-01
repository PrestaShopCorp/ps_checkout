<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
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
     * @return array|bool data with paypal order id or false if error
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
     *
     * @return array|bool response from paypal if the payment is accepted or false if error occured
     */
    public function capture($orderId, $merchantId)
    {
        $this->setRoute('/payments/order/capture');

        $response = $this->post([
            'json' => json_encode([
                'mode' => 'paypal',
                'orderId' => (string) $orderId,
                'payee' => [
                    'merchant_id' => $merchantId,
                ],
            ]),
        ]);

        if (isset($response['checkoutError']) && $response['checkoutError'] === true) {
            return $response;
        }

        if (false === isset($response['purchase_units'][0]['payments']['captures'][0])) {
            return false;
        }

        return $response['purchase_units'][0]['payments']['captures'][0];
    }

    /**
     * Get paypal order details
     *
     * @param string $orderId paypal
     *
     * @return array|bool paypal order
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
     * @return array|bool paypal order
     */
    public function authorize($orderId, $merchantId)
    {
        // TODO : waiting maasland integration
    }

    /**
     * Refund an order
     *
     * @param array $payload
     *
     * @return array|bool paypal order
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
     * @param string $orderId paypal
     *
     * @return array|bool response from paypal if the payment is accepted or false if error occured
     */
    public function patch($orderId)
    {
        $this->setRoute('/payments/order/update');

        return $this->post([
            'json' => json_encode([
                'orderId' => (string) $orderId,
            ]),
        ]);
    }
}
