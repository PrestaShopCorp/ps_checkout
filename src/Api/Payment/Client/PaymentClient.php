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
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\GenericClient;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\Environment\PaymentEnv;
use PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;
use Psr\Log\LoggerInterface;

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
     * @var Token
     */
    private $firebaseToken;

    public function __construct(
        ExceptionHandler $exceptionHandler,
        LoggerInterface $logger,
        PrestaShopConfiguration $prestaShopConfiguration,
        PrestaShopContext $prestaShopContext,
        ShopUuidManager $shopUuidManager,
        LinkAdapter $linkAdapter,
        Token $firebaseToken,
        Client $client = null
    ) {
        parent::__construct($exceptionHandler, $logger, $prestaShopConfiguration, $prestaShopContext, $shopUuidManager, $linkAdapter);

        $this->prestaShopContext = $prestaShopContext;
        $this->shopUuidManager = $shopUuidManager;
        $this->firebaseToken = $firebaseToken;

        $this->shopUuid = $this->shopUuidManager->getForShop($this->prestaShopContext->getShopId());

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
                        'Authorization' => 'Bearer ' . $this->firebaseToken->getToken(),
                        'Shop-Id' => $this->shopUuid,
                        'Hook-Url' => $this->linkAdapter->getModuleLink(
                            'ps_checkout',
                            'DispatchWebHook',
                            [],
                            true,
                            null,
                            (int) $this->prestaShopContext->getShopId()
                        ),
                        'Module-Version' => \Ps_checkout::VERSION, // version of the module
                        'Prestashop-Version' => _PS_VERSION_, // prestashop version
                        'Shop-Url' => $this->prestaShopContext->getShopUrl(),
                    ],
                ],
            ]);
        }

        $this->setClient($client);
    }
}
