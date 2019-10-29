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
use PrestaShop\Module\PrestashopCheckout\Environment\PsxEnv;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;

class PsxClient extends GenericClient
{
    public function __construct()
    {
        $client = new Client(array(
            'base_url' => (new PsxEnv())->getPsxApiUrl(),
            'defaults' => array(
                'timeout' => $this->getTimeout(),
                'exceptions' => $this->getExceptionsMode(),
                'headers' => [
                    'Content-Type' => 'application/vnd.psx.v1+json', // api version to use (psl side)
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . (new Token())->getToken(),
                    'Shop-Id' => \Configuration::get('PS_CHECKOUT_SHOP_UUID_V4'),
                    'Module-Version' => \Ps_checkout::VERSION, // version of the module
                    'Prestashop-Version' => _PS_VERSION_, // prestashop version
                ],
            ),
        ));

        $this->setClient($client);
    }
}
