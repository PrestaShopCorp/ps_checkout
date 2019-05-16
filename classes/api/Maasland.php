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

namespace PrestaShop\Module\PrestashopCheckout\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Stream\Stream;
use PrestaShop\Module\PrestashopCheckout\FirebaseClient;

class Maasland
{
    public $debugMode = false; // true for false x)
    public $timeout = 5;

    private $maaslandLive = '';
    private $maaslandSandbox = '';

    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client = null)
    {
        // temporary
        $conf = json_decode(file_get_contents(_PS_MODULE_DIR_.'/ps_checkout/maaslandConf.json'));
        $this->maaslandLive = $conf->integration->live;
        $this->maaslandSandbox = $conf->integration->sandbox;

        // Client can be provided for tests
        if (null === $client) {
            $client = new Client(array(
                'base_url' => $this->maaslandSandbox,
                'defaults' => array(
                    'timeout' => $this->timeout,
                    'exceptions' => $this->debugMode,
                    'headers' =>
                    [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer '.(new FirebaseClient())->getToken(),
                        'Shop-Id' => '', // TODO: make a valid uuid v4
                        'Hook-Url' => '' // TODO: Create front controller to manage hook callback
                    ],
                ),
            ));
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
            $response = $this->client->post($route, [
                'json' => $payload
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
            $response = $this->client->post($route, [
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
    public function fetchOrder($orderId)
    {
        $route = '/payments/order/fetch';

        $payload = [
            'orderId' => $orderId
        ];

        try {
            $response = $this->client->post($route, [
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
        $route = '/payments/order/refund';

        try {
            $response = $this->client->post($route, [
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
            $response = $this->client->post($route, [
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
