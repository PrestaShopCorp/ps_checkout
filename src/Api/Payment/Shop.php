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

namespace PrestaShop\Module\PrestashopCheckout\Api\Payment;

use GuzzleHttp\Client;
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Api\Firebase\Token;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Client\PaymentClient;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Context\PrestaShopContext;
use PrestaShop\Module\PrestashopCheckout\ExpressCheckout\ExpressCheckoutConfiguration;
use PrestaShop\Module\PrestashopCheckout\Handler\ExceptionHandler;
use PrestaShop\Module\PrestashopCheckout\ShopUuidManager;
use Psr\Log\LoggerInterface;

/**
 * Handle request to maasland regarding the shop/merchant status
 */
class Shop extends PaymentClient
{
    /**
     * @var ExpressCheckoutConfiguration
     */
    protected $expressCheckoutConfiguration;

    public function __construct(
        ExceptionHandler $exceptionHandler,
        LoggerInterface $logger,
        PrestaShopConfiguration $prestaShopConfiguration,
        PrestaShopContext $prestaShopContext,
        ShopUuidManager $shopUuidManager,
        LinkAdapter $linkAdapter,
        Token $firebaseToken,
        Client $client = null,
        ExpressCheckoutConfiguration $expressCheckoutConfiguration
    ) {
        parent::__construct($exceptionHandler, $logger, $prestaShopConfiguration, $prestaShopContext, $shopUuidManager, $linkAdapter, $firebaseToken, $client);
        $this->expressCheckoutConfiguration = $expressCheckoutConfiguration;
    }

    /**
     * Used to notify PSL on settings update
     *
     * @return array
     */
    public function updateSettings()
    {
        $this->setRoute('/payments/shop/update_settings');

        return $this->post([
            'json' => json_encode([
                'settings' => [
                    'cb' => (bool) $this->prestaShopConfiguration->get('PS_CHECKOUT_CARD_PAYMENT_ENABLED'),
                    'express_in_product' => $this->expressCheckoutConfiguration->isProductPageEnabled(),
                    'express_in_cart' => $this->expressCheckoutConfiguration->isOrderPageEnabled(),
                    'express_in_checkout' => $this->expressCheckoutConfiguration->isCheckoutPageEnabled(),
                ],
            ]),
        ]);
    }
}
