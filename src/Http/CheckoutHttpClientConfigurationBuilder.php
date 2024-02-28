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

use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Environment\PaymentEnv;
use PrestaShop\Module\PrestashopCheckout\Repository\PsAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Routing\Router;
use PrestaShop\Module\PrestashopCheckout\ShopContext;
use Ps_checkout;

class CheckoutHttpClientConfigurationBuilder implements HttpClientConfigurationBuilderInterface
{
    const TIMEOUT = 10;

    /**
     * @var PaymentEnv
     */
    private $paymentEnv;

    /**
     * @var Router
     */
    private $router;

    /**
     * @var ShopContext
     */
    private $shopContext;

    /**
     * @var PsAccountRepository
     */
    private $psAccountRepository;

    /**
     * @var PrestaShopContext
     */
    private $prestaShopContext;

    public function __construct(
        PaymentEnv $paymentEnv,
        Router $router,
        ShopContext $shopContext,
        PsAccountRepository $psAccountRepository,
        PrestaShopContext $prestaShopContext
    ) {
        $this->paymentEnv = $paymentEnv;
        $this->router = $router;
        $this->shopContext = $shopContext;
        $this->psAccountRepository = $psAccountRepository;
        $this->prestaShopContext = $prestaShopContext;
    }

    /**
     * @return array
     */
    public function build()
    {
        return [
            'base_url' => $this->paymentEnv->getPaymentApiUrl(),
            'verify' => $this->getVerify(),
            'timeout' => static::TIMEOUT,
            'headers' => [
                'Content-Type' => 'application/vnd.checkout.v1+json', // api version to use (psl side)
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->psAccountRepository->getIdToken(),  // Token we get from PsAccounts
                'Shop-Id' => $this->psAccountRepository->getShopUuid(),  // Shop UUID we get from PsAccounts
                'Hook-Url' => $this->router->getDispatchWebhookLink($this->prestaShopContext->getShopId()),
                'Bn-Code' => $this->shopContext->getBnCode(),
                'Module-Version' => Ps_checkout::VERSION, // version of the module
                'Prestashop-Version' => _PS_VERSION_, // prestashop version
            ],
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
