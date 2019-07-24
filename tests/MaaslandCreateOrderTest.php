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
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Api\Order;

class Link
{
    public function getAdminLink()
    {
        return 'adminLink';
    }

    public function getModuleLink()
    {
        return 'moduleLink';
    }
}

class PrestaShopLogger
{
    public static function addLog()
    {
    }
}

class MaaslandCreateOrderTest extends TestCase
{
    public function testCreateOrderDefaultScenario()
    {
        // TODO: Display a dummy but valid payload
        $result = [
            'data-key' => 'data-value',
        ];
        $client = new Client();

        // Create a mock subscriber and queue two responses.
        $mock = new Mock([
            new Response(200, [], Stream::factory(json_encode($result))),
        ]);
        // Add the mock subscriber to the client.
        $client->getEmitter()->attach($mock);

        $order = new Order(new Link(), $client);

        $this->assertSame($result, $order->create([]));
    }

    public function testCreateOrderUnauthorized()
    {
        $client = new Client();

        $mock = new Mock([
            new Response(401, [], Stream::factory('')),
        ]);
        $client->getEmitter()->attach($mock);

        $order = new Order(new Link(), $client);

        $this->assertSame(false, $order->create([]));
    }

    public function testCreateOrderInvalidResponse()
    {
        $result = 'I\'m not valid json';
        $client = new Client();

        $mock = new Mock([
            new Response(200, [], Stream::factory($result)),
        ]);
        $client->getEmitter()->attach($mock);

        $order = new Order(new Link(), $client);

        $this->assertSame(false, $order->create([]));
    }
}
