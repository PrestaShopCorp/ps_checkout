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

namespace PrestaShop\Module\PrestashopCheckout\Api\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PrestaShop\Module\PrestashopCheckout\FirebaseClient;
use PrestaShop\Module\PrestashopCheckout\Environment;

/**
 * Construct the client used to make call to maasland
 */
class MaaslandClient
{
    /**
     * Guzzle Client
     *
     * @var Client
     */
    protected $client;

    /**
     * Class Link in order to generate module link
     *
     * @var \Link
     */
    protected $link;

    /**
     * Enable or disable the catch of Maasland 400 error
     * If set to false, you will not be able to catch the error of maasland
     * guzzle will show a different error message.
     *
     * @var bool
     */
    protected $catchExceptions = true;

    /**
     * Set how long guzzle will wait a response before end it up
     *
     * @var int
     */
    protected $timeout = 10;

    /**
     * Api route
     *
     * @var string
     */
    protected $route;

    public function __construct(\Link $link, Client $client = null)
    {
        $this->setLink($link);

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
                        'Bn-Code' => $this->getBnCode(),
                    ],
                ),
            ));
        }

        $this->setClient($client);
    }

    /**
     * Wrapper of method post from guzzle client
     *
     * @param array $options payload
     *
     * @return array|bool return response or false if no response
     */
    protected function post(array $options = [])
    {
        try {
            $response = $this->client->post($this->route, $options);
        } catch (RequestException $e) {
            \PrestaShopLogger::addLog($e->getMessage(), 3, null, null, null, true);

            if (!$e->hasResponse()) {
                return false;
            }
            $response = $e->getResponse();
        }

        $data = json_decode($response->getBody(), true);

        return isset($data) ? $data : false;
    }

    /**
     * Retrieve the bn code - if on ready send an empty bn code
     * maasland will replace it with the bn code for ready
     *
     * @return string
     */
    private function getBnCode()
    {
        $bnCode = 'PrestaShop_Cart_PSXO_PSDownload';

        if (getenv('PLATEFORM') === 'PSREADY') { // if on ready send an empty bn-code
            $bnCode = '';
        }

        return $bnCode;
    }

    /**
     * Setter for route
     *
     * @param string $route
     */
    protected function setRoute($route)
    {
        $this->route = $route;
    }

    /**
     * Setter for link
     *
     * @param \Link $link
     */
    protected function setLink(\Link $link)
    {
        $this->link = $link;
    }

    /**
     * Setter for client
     *
     * @param Client $client
     */
    protected function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Setter for timeout
     *
     * @param int $timeout
     */
    protected function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    /**
     * Setter for exceptions mode
     *
     * @param bool $bool
     */
    protected function setExceptionsMode($bool)
    {
        $this->catchExceptions = $bool;
    }
}
