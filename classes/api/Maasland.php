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

namespace PrestaShop\Module\PrestashopPayment\Api;

use GuzzleHttp\Client;

class Maasland
{
    private $paypalApi = 'https://api.sandbox.paypal.com';

    public function getAccessToken()
    {
        $route = '/v1/oauth2/token';

        $client = new Client();
        $response = $client->post($this->paypalApi . $route, [
            'headers' =>
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'body' => 'grant_type=client_credentials',
            'auth' => ['<username>', '<password>', 'basic']
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['access_token'];
    }

    public function getClientToken()
    {
        $route = '/v1/identity/generate-token';

        $client = new Client();
        $response = $client->post($this->paypalApi . $route, [
            'headers' =>
            [
                'Authorization' => 'Bearer ' . $this->getAccessToken(),
                'Content-Type' => 'application/json'
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return $data['client_token'];
    }

    public function createOrder($payload = array())
    {
        // $route = '/v2/checkout/orders/';

        $client = new Client();
        $response = $client->post('http://127.0.0.1:8000/payments/order/create', [
            'headers' =>
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        return $data;
    }
}
