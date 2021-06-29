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

namespace PrestaShop\Module\PrestashopCheckout\Api\Psx\Client;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Environment\PsxEnv;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;

class PsxClient extends GenericClient
{
    public function __construct()
    {
        $client = new Client([
            'base_url' => (new PsxEnv())->getPsxApiUrl(),
            'defaults' => [
                'verify' => $this->getVerify(),
                'timeout' => $this->getTimeout(),
                'exceptions' => $this->getExceptionsMode(),
                'headers' => [
                    'Content-Type' => 'application/vnd.psx.v1+json', // api version to use (psl side)
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . (new Token())->getToken(),
                    'Shop-Id' => (new ShopUuidManager())->getForShop((int) \Context::getContext()->shop->id),
                    'Module-Version' => \Ps_checkout::VERSION, // version of the module
                    'Prestashop-Version' => _PS_VERSION_, // prestashop version
                ],
            ],
        ]);

        $this->setClient($client);
    }
}
