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

namespace PrestaShop\Module\PrestashopCheckout\Api\Psl\Client;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Environment\PslEnv;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;

/**
 * Construct the client used to make call to PSL API
 */
class PslClient extends GenericClient
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
     * @var \PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
     */
    protected $context;

    public function __construct(PrestaShopContext $context, Client $client = null)
    {
        $this->context = $context;
        $shopId = (int) $context->getShopId();
        $shopUuidManager = new ShopUuidManager();
        $this->shopUuid = $shopUuidManager->getForShop($shopId);
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        $this->module = $module;
        /** @var PsAccountRepository $psAccountRepository */
        $psAccountRepository = $this->module->getService('ps_checkout.repository.prestashop.account');

        $this->setLink($context->getLink());

        // Client can be provided for tests
        if (null === $client) {
            $client = new Client([
                'base_url' => (new PslEnv())->getPslApiUrl(),
                'defaults' => [
                    'verify' => $this->getVerify(),
                    'timeout' => $this->timeout,
                    'exceptions' => $this->catchExceptions,
                    'headers' => [
                        'Content-Type' => 'application/json', // api version to use (psl side)
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer ' . $psAccountRepository->getIdToken(),
                        'Shop-Id' => $this->shopUuid,
                        'Hook-Url' => $this->link->getModuleLink(
                            'ps_checkout',
                            'DispatchWebHook',
                            [],
                            true,
                            null,
                            $shopId
                        ),
                        'Module-Version' => \Ps_checkout::VERSION, // version of the module
                        'Prestashop-Version' => _PS_VERSION_, // prestashop version
                        'Shop-Url' => $context->getShopUrl(),
                    ],
                ],
            ]);
        }

        $this->setClient($client);
    }
}
