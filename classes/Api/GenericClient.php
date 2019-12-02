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

namespace PrestaShop\Module\PrestashopCheckout\Api;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Handler\Response\ResponseApiHandler;

/**
 * Construct the client used to make call to maasland
 */
class GenericClient
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
    protected $catchExceptions = false;

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

    /**
     * Wrapper of method post from guzzle client
     *
     * @param array $options payload
     *
     * @return array return response or false if no response
     */
    protected function post(array $options = [])
    {
        $response = $this->getClient()->post($this->getRoute(), $options);

        $responseHandler = new ResponseApiHandler();

        return $responseHandler->handleResponse($response);
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
     * Setter for client
     *
     * @param Client $client
     */
    protected function setClient(Client $client)
    {
        $this->client = $client;
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

    /**
     * Getter for route
     *
     * @return string
     */
    protected function getRoute()
    {
        return $this->route;
    }

    /**
     * Getter for client
     *
     * @return Client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * Getter for Link
     *
     * @return \Link
     */
    protected function getLink()
    {
        return $this->link;
    }

    /**
     * Getter for timeout
     *
     * @return int
     */
    protected function getTimeout()
    {
        return $this->timeout;
    }

    /**
     * Getter for exceptions mode
     *
     * @return bool
     */
    protected function getExceptionsMode()
    {
        return $this->catchExceptions;
    }
}
