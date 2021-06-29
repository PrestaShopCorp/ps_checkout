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
                'verify' => $this->getVerify(),
                'timeout' => $this->timeout,
                'exceptions' => $this->catchExceptions,
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
