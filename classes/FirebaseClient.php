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

namespace PrestaShop\Module\PrestashopCheckout;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use PrestaShop\Module\PrestashopCheckout\Environment\FirebaseEnv;

/**
 * Handle firebase signIn/signUp
 */
class FirebaseClient
{
    /**
     * Client used to request Firebase API
     *
     * @var Client
     */
    protected $client;

    /**
     * API key used for calls to Firebase
     *
     * @var string
     */
    protected $apiKey;

    /**
     * API url used for calls to Firebase in order to get refresh the token
     *
     * @var string
     */
    protected $baseUrlSecureToken = 'https://securetoken.googleapis.com/v1/';

    /**
     * Number of seconds to wait before timeout
     *
     * @var int
     */
    protected $timeOut = 10;

    public function __construct(array $params = array())
    {
        if (isset($params['api_key'])) {
            $this->apiKey = $params['api_key'];
        } else {
            $this->apiKey = (new FirebaseEnv())->getFirebaseApiKey();
        }

        $this->client = new Client(
            array(
                'base_url' => $this->baseUrlSecureToken,
                'defaults' => array(
                    'timeout' => $this->timeOut,
                    'allow_redirects' => false,
                    'query' => array(
                        'key' => $this->apiKey,
                    ),
                    'headers' => array(
                        'Content-Type' => 'application/json',
                    ),
                ),
            )
        );
    }

    /**
     * Get the user firebase token
     *
     * @return string
     */
    public function getToken()
    {
        if (false === $this->checkIfTokenIsValid()) {
            $this->refreshToken();
        }

        return \Configuration::get('PS_PSX_FIREBASE_ID_TOKEN');
    }

    /**
     * Refresh the token
     *
     * @see https://firebase.google.com/docs/reference/rest/auth#section-refresh-token Firebase documentation
     *
     * @param string $token
     *
     * @return array
     */
    public function refreshToken()
    {
        $response = $this->post('token', array(
            'json' => array(
                'grant_type' => 'refresh_token',
                'refresh_token' => \Configuration::get('PS_PSX_FIREBASE_REFRESH_TOKEN'),
            ),
        ));

        if (isset($response['id_token'])) {
            \Configuration::updateValue('PS_PSX_FIREBASE_ID_TOKEN', $response['id_token']);
        }

        return $response;
    }

    /**
     * Check the token validity. The token expire time is set to 3600 seconds.
     *
     * @return bool
     */
    public function checkIfTokenIsValid()
    {
        $query = 'SELECT date_upd
                FROM ' . _DB_PREFIX_ . 'configuration
                WHERE name="PS_PSX_FIREBASE_ID_TOKEN"
                AND date_upd > NOW() + INTERVAL 1 HOUR';

        $dateUpd = \Db::getInstance()->getValue($query);

        if (false === $dateUpd) {
            return false;
        }

        return true;
    }

    protected function post($url = null, array $options = [])
    {
        try {
            $response = $this->client->post($url, $options);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
            } else {
                throw $e;
            }
        }

        $body = json_decode((string) $response->getBody(), true);

        return $body;
    }

    public function setTimeOut($timeOut)
    {
        return $this->timeOut = $timeOut;
    }
}
