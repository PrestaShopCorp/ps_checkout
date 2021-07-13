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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment\Client;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Environment\PaymentEnv;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;

/**
 * Construct the client used to make call to maasland
 */
class PaymentClient extends GenericClient
{
    /**
     * @var string
     */
    protected $shopUuid;

    /**
     * @var \Ps_checkout
     */
    protected $module;
    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;

    public function __construct(\Link $link, Client $client = null)
    {
        $context = \Context::getContext();
        $shopUuidManager = new ShopUuidManager();
        $this->shopUuid = $shopUuidManager->getForShop((int) $context->shop->id);
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        $this->module = $module;

        $this->setLink($link);

        $this->psAccountRepository = $this->module->getService('ps_checkout.repository.prestashop.account');
        $token = $this->psAccountRepository->getIdToken();

        // Client can be provided for tests
        if (null === $client) {
            $client = new Client([
                'base_url' => (new PaymentEnv())->getPaymentApiUrl(),
                'defaults' => [
                    'verify' => $this->getVerify(),
                    'timeout' => $this->timeout,
                    'exceptions' => $this->catchExceptions,
                    'headers' => [
                        'Content-Type' => 'application/json', // api version to use (psl side)
                        'Accept' => 'application/json',
                        'Authorization' => "Bearer $token",
                        'Shop-Id' => $this->shopUuid,
                        'Hook-Url' => $this->link->getModuleLink(
                            'ps_checkout',
                            'DispatchWebHook',
                            [],
                            true,
                            null,
                            (int) $context->shop->id
                        ),
                        'Module-Version' => \Ps_checkout::VERSION, // version of the module
                        'Prestashop-Version' => _PS_VERSION_, // prestashop version
                        'Shop-Url' => $context->shop->getBaseURL(),
                    ],
                ],
            ]);
        }

        $this->setClient($client);
    }
}
