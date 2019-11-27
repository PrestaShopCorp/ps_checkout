<?php
/**
 * 2007-2019 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2019 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
use GuzzleHttp\Client;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;

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

class Module
{
    private $logger;

    public function __construct()
    {
        $this->logger = new Logger();
    }

    public static function getInstanceByName($name)
    {
        return new self();
    }

    public function getLogger()
    {
        return $this->logger;
    }
}

class Logger
{
    public function debug($msg)
    {
    }
}

class PaymentCreateOrderTest extends TestCase
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

        $response = $order->create([]);

        $this->assertSame($result, $response['body']);
    }

    public function testCreateOrderUnauthorized()
    {
        $client = new Client([
            'defaults' => [
                'exceptions' => false,
            ],
        ]);

        $mock = new Mock([
            new Response(401, [], Stream::factory('')),
        ]);
        $client->getEmitter()->attach($mock);

        $order = new Order(new Link(), $client);

        $response = $order->create([]);

        $this->assertSame(false, $response['status']);
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

        $response = $order->create([]);

        $this->assertSame(false, $response['status']);
    }
}
