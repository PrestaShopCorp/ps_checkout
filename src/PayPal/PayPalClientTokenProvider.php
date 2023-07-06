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

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

use Configuration;
use Context;
use DateTime;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Repository\PsCheckoutCartRepository;
use PrestaShopDatabaseException;
use PrestaShopException;
use PsCheckoutCart;
use Validate;

class PayPalClientTokenProvider
{
    /**
     * @var PsCheckoutCartRepository
     */
    private $psCheckoutCartRepository;

    public function __construct(PsCheckoutCartRepository $psCheckoutCartRepository)
    {
        $this->psCheckoutCartRepository = $psCheckoutCartRepository;
    }

    /**
     * @return string
     *
     * @throws PsCheckoutException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function getPayPalClientToken()
    {
        $context = Context::getContext();

        if (!Validate::isLoadedObject($context->cart)) {
            return '';
        }

        $psCheckoutCart = $this->psCheckoutCartRepository->findOneByCartId((int) $context->cart->id);

        if ($psCheckoutCart && !$psCheckoutCart->isPaypalClientTokenExpired()) {
            return $psCheckoutCart->getPaypalClientToken();
        }

        $apiOrder = new Order($context->link);
        $merchantId = Configuration::get('PS_CHECKOUT_PAYPAL_ID_MERCHANT', null, null, $context->shop->id);
        $clientToken = $apiOrder->generateClientToken($merchantId);

        if (!$psCheckoutCart) {
            $psCheckoutCart = new PsCheckoutCart();
            $psCheckoutCart->id_cart = (int) $context->cart->id;
        }

        $psCheckoutCart->paypal_token = $clientToken;
        $psCheckoutCart->paypal_token_expire = (new DateTime())->modify('+3550 seconds')->format('Y-m-d H:i:s');
        $this->psCheckoutCartRepository->save($psCheckoutCart);

        return $clientToken;
    }
}
