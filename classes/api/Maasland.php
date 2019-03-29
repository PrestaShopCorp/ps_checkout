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

namespace PrestaShop\Module\PrestashopPayments\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;

class Maasland
{
    public $debugMode = false; // true for false x)
    public $timeout = 3;

    private $maaslandApi = 'http://10.0.75.1:1234';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client = null)
    {
        // Client can be provided for tests
        if (null === $client) {
            $client = new Client();
        }
        $this->client = $client;
    }

    /**
     * Create order to paypal api
     *
     * @param array Cart details
     *
     * @return int|bool data with paypal order id or false if error
     */
    public function createOrder($payload = array())
    {
        $route = '/payments/order/create';

        try {
            $response = $this->client->post($this->maaslandApi . $route, [
                'timeout' => $this->timeout,
                'exceptions' => $this->debugMode,
                'headers' =>
                [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => json_encode($payload)
            ]);
        } catch (RequestException $e) {
            // TODO: Log the error ? Return an error message ?
            return false;
        }

        $data = json_decode($response->getBody(), true);

        return isset($data) ? $data : false;
    }

    /**
     * Capture order funds
     *
     * @param string orderId paypal
     *
     * @return array|bool response from paypal if the payment is accepted or false if error occured
     */
    public function captureOrder($orderId)
    {
        $route = '/payments/order/capture';

        $payload = [
            'mode' => 'paypal',
            'orderId' => (string) $orderId
        ];

        try {
            $response = $this->client->post($this->maaslandApi . $route, [
                'timeout' => $this->timeout,
                'exceptions' => $this->debugMode,
                'headers' =>
                [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => json_encode($payload)
            ]);
        } catch (RequestException $e) {
            // TODO: Log the error ? Return an error message ?
            return false;
        }

        $data = json_decode($response->getBody(), true);

        return isset($data) ? $data : false;
    }

    /**
     * Get paypal order details
     *
     * @param string orderId paypal
     *
     * @return array|bool paypal order
     */
    public function getOrderDetails($orderId)
    {
        // TODO : waiting maasland integration
    }

    /**
     * Authorize an order
     *
     * @param string orderId paypal
     *
     * @return array|bool paypal order
     */
    public function authorizeOrder($orderId)
    {
        // TODO : waiting maasland integration
    }

    /**
     * Refund an order
     *
     * @param string orderId paypal
     *
     * @return array|bool paypal order
     */
    public function refundOrder($payload)
    {
        // TODO : waiting maasland integration
    }

    /**
     * Patch paypal order
     *
     * @param string orderId paypal
     *
     * @return array|bool response from paypal if the payment is accepted or false if error occured
     */
    public function patchOrder($orderId)
    {
        $route = '/payments/order/update';

        $payload = [
            'orderId' => (string) $orderId
        ];

        try {
            $response = $this->client->post($this->maaslandApi . $route, [
                'timeout' => $this->timeout,
                'exceptions' => $this->debugMode,
                'headers' =>
                [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
                'json' => json_encode($payload)
            ]);
        } catch (RequestException $e) {
            // TODO: Log the error ? Return an error message ?
            return false;
        }

        $data = json_decode($response->getBody(), true);

        return isset($data) ? $data : false;
    }
}
