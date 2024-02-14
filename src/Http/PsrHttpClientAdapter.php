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

namespace PrestaShop\Module\PrestashopCheckout\Http;

use Prestashop\ModuleLibGuzzleAdapter\ClientFactory;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

 abstract class PsrHttpClientAdapter implements HttpClientInterface
 {
     /**
      * @var ClientInterface
      */
     private $client;

     /**
      * @param array $configuration
      */
     public function __construct(array $configuration)
     {
         $this->client = (new ClientFactory())->getClient($configuration);
     }

     /**
      * {@inheritdoc}
      */
     public function sendRequest(RequestInterface $request)
     {
         return $this->client->sendRequest($request);
     }
 }
