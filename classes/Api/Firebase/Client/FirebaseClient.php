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

namespace PrestaShop\Module\PrestashopCheckout\Api\Firebase\Client;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Environment\FirebaseEnv;

/**
 * Handle firebase signIn/signUp
 */
class FirebaseClient extends GenericClient
{
    /**
     * Firebase api key
     *
     * @var string
     */
    protected $apiKey;

    public function __construct(array $params = [])
    {
        if (isset($params['api_key'])) {
            $this->apiKey = $params['api_key'];
        } else {
            $this->apiKey = (new FirebaseEnv())->getFirebaseApiKey();
        }

        $client = new Client([
            'defaults' => [
                'timeout' => $this->timeout,
                'allow_redirects' => false,
                'query' => [
                    'key' => $this->apiKey,
                ],
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
            ],
        ]);

        $this->setClient($client);
    }
}
