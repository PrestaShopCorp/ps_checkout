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
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use PrestaShop\Module\PrestashopCheckout\FirebaseClient;
use PrestaShop\Module\PrestashopCheckout\Environment;

/**
 * Handle all call make to PSL (maasland)
 */
class Maasland
{
    public $catchExceptions = true;
    public $timeout = 10;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var \Link
     */
    private $link;

    public function __construct(\Link $link, Client $client = null)
    {
        $this->link = $link;

        $bnCode = 'PrestaShop_Cart_PrestaShopCheckout_PSDownload';
        if (getenv('PLATEFORM') === 'PSREADY') { // if on ready send an empty bn-code
            $bnCode = '';
        }

        // Client can be provided for tests
        if (null === $client) {
            $client = new Client(array(
                'base_url' => (new Environment())->getMaaslandUrl(),
                'defaults' => array(
                    'timeout' => $this->timeout,
                    'exceptions' => $this->catchExceptions,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . (new FirebaseClient())->getToken(),
                        'Shop-Id' => \Configuration::get('PS_CHECKOUT_SHOP_UUID_V4'),
                        'Hook-Url' => $this->link->getModuleLink('ps_checkout', 'DispatchWebHook', array(), true),
                        'Bn-Code' => $bnCode,
                    ],
                ),
            ));
        }
        $this->client = $client;
    }

    /**
     * Generate the paypal link to onboard merchant
     *
     * @return string|bool onboarding link
     */
    public function getMerchantIntegration()
    {
        $route = '/payments/shop/get_merchant_integrations';

        $payload = array();

        try {
            $response = $this->client->post($route, [
                'json' => json_encode($payload),
            ]);
        } catch (ServerException $e) {
            \PrestaShopLogger::addLog($e->getMessage());

            return false;
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());

            return $response;
        }

        $data = json_decode($response->getBody(), true);

        return isset($data) ? $data : false;
    }

    /**
     * Generate the paypal link to onboard merchant
     *
     * @return string|bool onboarding link
     */
    public function getPaypalOnboardingLink($email, $locale)
    {
        $route = '/payments/onboarding/onboard';

        $callBackUrl = $this->link->getAdminLink('AdminPaypalOnboardingPrestashopCheckout');

        $currency = \Currency::getCurrency(\Configuration::get('PS_CURRENCY_DEFAULT'));
        $isoCode = $currency['iso_code'];

        $payload = [
            'url' => $callBackUrl,
            'person_details' => [
                'email_address' => $email,
            ],
            'preferred_language_code' => str_replace('-', '_', $locale),
            'primary_currency_code' => $isoCode,
        ];

        try {
            $response = $this->client->post($route, [
                'json' => json_encode($payload),
            ]);
        } catch (ServerException $e) {
            \PrestaShopLogger::addLog($e->getMessage());

            return false;
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());

            return $response;
        }

        $data = json_decode($response->getBody(), true);

        if (false === isset($data['links']['1']['href'])) {
            return false;
        }

        return $data['links']['1']['href'];
    }

    /**
     * Create order to paypal api
     *
     * @param array Cart details
     *
     * @return array|bool data with paypal order id or false if error
     */
    public function createOrder($payload = array())
    {
        $route = '/payments/order/create';

        try {
            $response = $this->client->post($route, [
                'json' => $payload,
            ]);
        } catch (ServerException $e) {
            \PrestaShopLogger::addLog($e->getMessage());

            return false;
        } catch (ClientException $e) {
            $response = $e->getResponse();
        }

        $data = json_decode($response->getBody()->getContents(), true);

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
            'orderId' => (string) $orderId,
        ];

        try {
            $response = $this->client->post($route, [
                'json' => json_encode($payload),
            ]);
        } catch (ServerException $e) {
            \PrestaShopLogger::addLog($e->getMessage());

            return false;
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents(), true);

            return $response;
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
            'orderId' => $orderId,
        ];

        try {
            $response = $this->client->post($route, [
                'json' => json_encode($payload),
            ]);
        } catch (ServerException $e) {
            \PrestaShopLogger::addLog($e->getMessage());

            return false;
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());

            return $response;
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
                'json' => json_encode($payload),
            ]);
        } catch (ServerException $e) {
            \PrestaShopLogger::addLog($e->getMessage());

            return false;
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());

            return $response;
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
            'orderId' => (string) $orderId,
        ];

        try {
            $response = $this->client->post($route, [
                'json' => json_encode($payload),
            ]);
        } catch (ServerException $e) {
            \PrestaShopLogger::addLog($e->getMessage());

            return false;
        } catch (ClientException $e) {
            $response = json_decode($e->getResponse()->getBody()->getContents());

            return $response;
        }

        $data = json_decode($response->getBody(), true);

        return isset($data) ? $data : false;
    }
}
