<?php
/*
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 *  @author PrestaShop SA <contact@prestashop.com>
 *  @copyright  2007-2019 PrestaShop SA
 *  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopPayment\Api\Maasland;

class MaaslandGetClientTokenTest extends TestCase
{
    public function testGetClientTokenWorksAsExpected()
    {
        $accessTokenResult = [
            'scope' => 'https://uri.paypal.com/services/identity/proxyclient [...]',
            'nonce' => '2018-11-16T10:00:15Zqvf4GUEaln6b55p8e1sbdWtTrXkp8i8H0RpxnA_O5s8',
            'access_token' => 'Access-Token',
            'token_type' => 'Bearer',
            'expires_in' => 30546
        ];

        $result = [
            'client_token' => 'eyJicmFpbnRyZWUiOns[...]NlBBIn19',
            'expires_in' => 3600,
        ];
        $client = new Client();

        // Create a mock subscriber and queue two responses.
        $mock = new Mock([
            new Response(200, [], Stream::factory(json_encode($accessTokenResult))),
            new Response(200, [], Stream::factory(json_encode($result))),
        ]);
        // Add the mock subscriber to the client.
        $client->getEmitter()->attach($mock);

        $maasland = new Maasland($client);

        $this->assertSame($result['client_token'], $maasland->getClientToken());
    }

    public function testGetClientTokenWhenAccessTokenIsFalse()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(200, [], Stream::factory('')),
        ]);
        $client->getEmitter()->attach($mock);

        $maasland = new Maasland($client);

        try {
            $this->assertSame(false, $maasland->getClientToken());
        } catch (RequestException $e) {
            // Client tokens depends on Access token. If we cannot get an access token, do not try
            // to get the second one.
            // If this exception is thrown, this means we tried anyway which is wrong.
            throw new \Exception('The second HTTP call should not be done, because access_token was invalid.', 0, $e);
        }
    }
}
