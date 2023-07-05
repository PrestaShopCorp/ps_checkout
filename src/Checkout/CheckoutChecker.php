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

namespace PrestaShop\Module\PrestashopCheckout\Checkout;

use Cart;
use Configuration;
use Customer;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Card3DSecure;
use Psr\Log\LoggerInterface;
use Validate;

class CheckoutChecker
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param int $cartId
     * @param array{id: string, status: string, intent: string, payment_source: array, purchase_units: array} $orderPayPal
     *
     * @return void
     *
     * @throws PsCheckoutException
     */
    public function continueWithAuthorization($cartId, $orderPayPal)
    {
        if ($orderPayPal['status'] === 'COMPLETED') {
            throw new PsCheckoutException(sprintf('PayPal Order %s is already captured', $orderPayPal['id']));
        }

        if (isset($orderPayPal['payment_source']['card'])) {
            $card3DSecure = (new Card3DSecure())->continueWithAuthorization($orderPayPal);

            $this->logger->info(
                '3D Secure authentication result',
                [
                    'authentication_result' => isset($orderPayPal['payment_source']['card']['authentication_result']) ? $orderPayPal['payment_source']['card']['authentication_result'] : null,
                    'decision' => str_replace(
                        [
                            (string) Card3DSecure::NO_DECISION,
                            (string) Card3DSecure::PROCEED,
                            (string) Card3DSecure::REJECT,
                            (string) Card3DSecure::RETRY,
                        ],
                        [
                            Configuration::get('PS_CHECKOUT_LIABILITY_SHIFT_REQ') ? 'Rejected, no liability shift' : 'Proceed, without liability shift',
                            'Proceed, liability shift is possible',
                            'Rejected',
                            'Retry, ask customer to retry',
                        ],
                        (string) $card3DSecure
                    ),
                ]
            );

            switch ($card3DSecure) {
                case Card3DSecure::REJECT:
                    throw new PsCheckoutException('Card Strong Customer Authentication failure', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_FAILURE);
                case Card3DSecure::RETRY:
                    throw new PsCheckoutException('Card Strong Customer Authentication must be retried.', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN);
                case Card3DSecure::NO_DECISION:
                    if (Configuration::get('PS_CHECKOUT_LIABILITY_SHIFT_REQ')) {
                        throw new PsCheckoutException('No liability shift to card issuer', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN);
                    }
                    break;
            }
        }

        $cart = new Cart($cartId);

        if (!Validate::isLoadedObject($cart)) {
            throw new PsCheckoutException(sprintf('Cart with id %s not found.', var_export($cartId, true)), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        $products = $cart->getProducts(true);

        if (empty($products)) {
            throw new PsCheckoutException(sprintf('Cart with id %s has no product. Cannot capture the order.', var_export($cart->id, true)), PsCheckoutException::CART_PRODUCT_MISSING);
        }

        if ($cart->isAllProductsInStock() !== true ||
            (method_exists($cart, 'checkAllProductsAreStillAvailableInThisState') && $cart->checkAllProductsAreStillAvailableInThisState() !== true) ||
            (method_exists($cart, 'checkAllProductsHaveMinimalQuantities') && $cart->checkAllProductsHaveMinimalQuantities() !== true)
        ) {
            throw new PsCheckoutException(sprintf('Cart with id %s contains products unavailable. Cannot capture the order.', var_export($cart->id, true)), PsCheckoutException::CART_PRODUCT_UNAVAILABLE);
        }

        if (!Customer::customerHasAddress($cart->id_customer, $cart->id_address_invoice)) {
            throw new PsCheckoutException(sprintf('Invoice address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_invoice, true)), PsCheckoutException::CART_ADDRESS_INVOICE_INVALID);
        }

        if (!$cart->isVirtualCart() && !Customer::customerHasAddress($cart->id_customer, $cart->id_address_delivery)) {
            throw new PsCheckoutException(sprintf('Delivery address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_delivery, true)), PsCheckoutException::CART_ADDRESS_DELIVERY_INVALID);
        }

        if (!$cart->isVirtualCart() && !array_key_exists((int) $cart->id_address_delivery, $cart->getDeliveryOptionList())) {
            throw new PsCheckoutException(sprintf('No delivery option selected for address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_delivery, true)), PsCheckoutException::CART_DELIVERY_OPTION_INVALID);
        }

        // Check if PayPal order amount is the same than the cart amount : we tolerate a difference of more or less 0.05
        $paypalOrderAmount = (float) sprintf('%01.2f', $orderPayPal['purchase_units'][0]['amount']['value']);
        $cartAmount = (float) sprintf('%01.2f', $cart->getOrderTotal(true, Cart::BOTH));

        if ($paypalOrderAmount + 0.05 < $cartAmount || $paypalOrderAmount - 0.05 > $cartAmount) {
            throw new PsCheckoutException('The transaction amount does not match with the cart amount.', PsCheckoutException::DIFFERENCE_BETWEEN_TRANSACTION_AND_CART);
        }
    }
}
