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

namespace PrestaShop\Module\PrestashopCheckout\Api;

use Exception;
use GuzzleHttp\Psr7\Request;
use Http\Client\Exception\NetworkException;
use Http\Client\Exception\TransferException;
use Link;
use Module;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\Response\ResponseApiHandler;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use Ps_checkout;

/**
 * Construct the client used to make call to maasland
 */
class GenericClient
{
    /**
     * Guzzle Client
     */
    protected $client;

    /**
     * Class Link in order to generate module link
     *
     * @var Link
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
     * @var string
     */
    protected $shopUid;

    /**
     * @var string
     */
    protected $token;

    public function __construct()
    {
        /** @var Ps_checkout $module */
        $module = Module::getInstanceByName('ps_checkout');
        /** @var PsAccountRepository $psAccountRepository */
        $psAccountRepository = $module->getService('ps_checkout.repository.prestashop.account');

        $this->shopUid = $psAccountRepository->getShopUuid();
        $this->token = $psAccountRepository->getIdToken();
    }

    /**
     * Wrapper of method post from guzzle client
     *
     * @param array $options payload
     *
     * @return array return response or false if no response
     */
    protected function post(array $options = [])
    {
        $request = new Request('POST', $this->getRoute(), [], json_encode($options));

        try {
            $response = $this->client->sendRequest($request);
        } catch (\GuzzleHttp\Ring\Exception\ConnectException $exception) {
            return $this->handleException(new NetworkException($exception->getMessage(), $request, $exception));
        } catch (\GuzzleHttp\Ring\Exception\RingException $exception) {
            return $this->handleException(new TransferException($exception->getMessage(), 0, $exception));
        } catch (Exception $exception) {
            return $this->handleException($exception);
        }

        return (new ResponseApiHandler())->handleResponse($response);
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
     * @param object $client Guzzle client
     */
    protected function setClient($client)
    {
        $this->client = $client;
    }

    /**
     * Setter for link
     *
     * @param Link $link
     */
    protected function setLink(Link $link)
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
     * @return object Guzzle client
     */
    protected function getClient()
    {
        return $this->client;
    }

    /**
     * Getter for Link
     *
     * @return Link
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

    private function handleException(Exception $exception)
    {
        $body = '';
        $httpCode = 500;
        $exceptionCode = $exception->getCode();
        $exceptionMessage = $exception->getMessage();

        if ($exception instanceof NetworkException || $exception instanceof TransferException) {
            $exceptionCode = PsCheckoutException::PSCHECKOUT_HTTP_EXCEPTION;
        }

        if (method_exists($exception, 'getResponse')) {
            $response = $exception->getResponse();
            $body = $response ? $response->getBody() : $body;
            $httpCode = $response ? $response->getStatusCode() : $httpCode;
        }

        return [
            'status' => false,
            'httpCode' => $httpCode,
            'body' => $body,
            'exceptionCode' => $exceptionCode,
            'exceptionMessage' => $exceptionMessage,
        ];
    }

    /**
     * @see https://docs.guzzlephp.org/en/5.3/clients.html#verify
     *
     * @return true|string
     */
    protected function getVerify()
    {
        if (defined('_PS_CACHE_CA_CERT_FILE_') && file_exists(constant('_PS_CACHE_CA_CERT_FILE_'))) {
            return constant('_PS_CACHE_CA_CERT_FILE_');
        }

        return true;
    }
}
