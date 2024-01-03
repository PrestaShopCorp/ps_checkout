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

use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Environment\Env;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Routing\Router;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use Ps_checkout;

class PaymentClientConfigurationBuilder
{
    const TIMEOUT = 10;

    /** @var Env */
    private $env;

    /** @var Router */
    private $router;

    /** @var ShopContext */
    private $shopContext;

    /** @var PsAccountRepository */
    private $psAccountRepository;

    /** @var PrestaShopConfiguration */
    private $prestaShopConfiguration;

    /** @var CertFileProvider */
    private $certFileProvider;

    public function __construct(
        Env $env,
        Router $router,
        ShopContext $shopContext,
        PsAccountRepository $psAccountRepository,
        PrestaShopConfiguration $prestaShopConfiguration,
        CertFileProvider $certFileProvider
    ) {
        $this->env = $env;
        $this->router = $router;
        $this->shopContext = $shopContext;
        $this->psAccountRepository = $psAccountRepository;
        $this->prestaShopConfiguration = $prestaShopConfiguration;
        $this->certFileProvider = $certFileProvider;
    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'base_url' => $this->env->getPaymentApiUrl(),
            'verify' => $this->certFileProvider->getPath(),
            'timeout' => static::TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/vnd.checkout.v1+json', // api version to use (psl side)
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->psAccountRepository->getIdToken(),  // Token we get from PsAccounts
                'Shop-Id' => $this->psAccountRepository->getShopUuid(),  // Shop UUID we get from PsAccounts
                'Hook-Url' => $this->router->getDispatchWebhookLink((int) Context::getContext()->shop->id),
                'Bn-Code' => $this->shopContext->getBnCode(),
                'Module-Version' => Ps_checkout::VERSION, // version of the module
                'Prestashop-Version' => _PS_VERSION_, // prestashop version
            ],
        ];
    }
}
