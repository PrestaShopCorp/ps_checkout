<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment\Client;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Environment\PaymentEnv;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\ShopContext;

/**
 * Construct the client used to make call to maasland
 */
class PaymentClient extends GenericClient
{
    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;

    public function __construct(\Link $link, PsAccountRepository $psAccountRepository, Client $client = null)
    {
        $this->psAccountRepository = $psAccountRepository;

        $this->setLink($link);

        // Client can be provided for tests
        if (null === $client) {
            $client = new Client([
                'base_url' => (new PaymentEnv())->getPaymentApiUrl(),
                'defaults' => [
                    'verify' => $this->getVerify(),
                    'timeout' => $this->timeout,
                    'exceptions' => $this->catchExceptions,
                    'headers' => [
                        'Content-Type' => 'application/vnd.checkout.v1+json', // api version to use (psl side)
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . (new Token())->getToken(),
                        'Shop-Id' => $psAccountRepository->getShopUuid(),
                        'Hook-Url' => $this->link->getModuleLink(
                            'ps_checkout',
                            'DispatchWebHook',
                            [],
                            true,
                            null,
                            (int) \Context::getContext()->shop->id
                        ),
                        'Bn-Code' => (new ShopContext())->getBnCode(),
                        'Module-Version' => \Ps_checkout::VERSION, // version of the module
                        'Prestashop-Version' => _PS_VERSION_, // prestashop version
                    ],
                ],
            ]);
        }

        $this->setClient($client);
    }
}
