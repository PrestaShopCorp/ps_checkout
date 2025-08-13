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

namespace PsCheckout\Core\Order\Validator;

use PsCheckout\Api\ValueObject\PayPalOrderResponse;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureConfiguration;
use PsCheckout\Core\PayPal\Card3DSecure\Card3DSecureValidatorInterface;
use PsCheckout\Core\Settings\Configuration\PayPalConfiguration;
use PsCheckout\Infrastructure\Adapter\Cart;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\CustomerInterface;
use Psr\Log\LoggerInterface;
use Cart as PrestaShopCart;

class OrderAuthorizationValidator implements OrderAuthorizationValidatorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CartInterface
     */
    private $cart;

    /**
     * @var CustomerInterface
     */
    private $customer;

    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var Card3DSecureValidatorInterface
     */
    private $card3DSecureValidator;

    public function __construct(
        LoggerInterface $logger,
        CustomerInterface $customer,
        CartInterface $cart,
        ConfigurationInterface $configuration,
        Card3DSecureValidatorInterface $card3DSecureValidator
    ) {
        $this->logger = $logger;
        $this->cart = $cart;
        $this->customer = $customer;
        $this->configuration = $configuration;
        $this->card3DSecureValidator = $card3DSecureValidator;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(int $cartId, PayPalOrderResponse $payPalOrder)
    {
        if ($payPalOrder->getStatus() === 'COMPLETED') {
            throw new PsCheckoutException(sprintf('PayPal Order %s is already captured', $payPalOrder->getId()), PsCheckoutException::PAYPAL_ORDER_ALREADY_CAPTURED);
        }

        $contingencies = $this->configuration->get(PayPalConfiguration::PS_CHECKOUT_HOSTED_FIELDS_CONTINGENCIES);
        $paymentSource = key($payPalOrder->getPaymentSource());

        if (in_array($paymentSource, ['apple_pay', 'google_pay', 'card'], true)) {
            $card3DSecure = $this->card3DSecureValidator->getAuthorizationDecision($payPalOrder);

            $this->logger->info(
                '3D Secure authentication result',
                [
                    'authentication_result' => $payPalOrder->getAuthenticationResult(),
                    'decision' => str_replace(
                        [
                            (string) Card3DSecureConfiguration::DECISION_NO_DECISION,
                            (string) Card3DSecureConfiguration::DECISION_PROCEED,
                            (string) Card3DSecureConfiguration::DECISION_REJECT,
                            (string) Card3DSecureConfiguration::DECISION_RETRY,
                        ],
                        [
                            $contingencies === 'SCA_ALWAYS' ? 'Rejected, no liability shift' : 'Proceed, without liability shift',
                            'Proceed, liability shift is possible',
                            'Rejected',
                            'Retry, ask customer to retry',
                        ],
                        (string) $card3DSecure
                    ),
                ]
            );

            switch ($card3DSecure) {
                case Card3DSecureConfiguration::DECISION_REJECT:
                    throw new PsCheckoutException('Card Strong Customer Authentication failure', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_FAILURE);
                case Card3DSecureConfiguration::DECISION_RETRY:
                    throw new PsCheckoutException('Card Strong Customer Authentication must be retried.', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN);
                case Card3DSecureConfiguration::DECISION_NO_DECISION:
                    if ($contingencies === 'SCA_ALWAYS') {
                        throw new PsCheckoutException('No liability shift to card issuer', PsCheckoutException::PAYPAL_PAYMENT_CARD_SCA_UNKNOWN);
                    }

                    break;
            }
        }

        $cart = $this->cart->getCart($cartId);

        if (!$cart) {
            throw new PsCheckoutException(sprintf('Cart with id %s not found.', var_export($cartId, true)), PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        if (empty($cart->getProducts(true))) {
            throw new PsCheckoutException(sprintf('Cart with id %s has no product. Cannot capture the order.', var_export($cart->id, true)), PsCheckoutException::CART_PRODUCT_MISSING);
        }

        if (
            !$this->isAllProductsInStock($cart) ||
            !$this->checkAllProductsAreStillAvailableInThisState($cart) ||
            !$this->checkAllProductsHaveMinimalQuantities($cart)
        ) {
            throw new PsCheckoutException(sprintf('Cart with id %s contains products unavailable. Cannot capture the order.', var_export($cart->id, true)), PsCheckoutException::CART_PRODUCT_UNAVAILABLE);
        }

        if (!$this->customer->customerHasAddress($cart->id_customer, $cart->id_address_invoice)) {
            throw new PsCheckoutException(sprintf('Invoice address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_invoice, true)), PsCheckoutException::CART_ADDRESS_INVOICE_INVALID);
        }

        if (!$cart->isVirtualCart() && !$this->customer->customerHasAddress($cart->id_customer, $cart->id_address_delivery)) {
            throw new PsCheckoutException(sprintf('Delivery address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_delivery, true)), PsCheckoutException::CART_ADDRESS_DELIVERY_INVALID);
        }

        if (!$cart->isVirtualCart() && !array_key_exists((int) $cart->id_address_delivery, $cart->getDeliveryOptionList())) {
            throw new PsCheckoutException(sprintf('No delivery option selected for address with id %s is incorrect. Cannot capture the order.', var_export($cart->id_address_delivery, true)), PsCheckoutException::CART_DELIVERY_OPTION_INVALID);
        }

        // Check if PayPal order amount is the same than the cart amount : we tolerate a difference of more or less 0.05
        $paypalOrderAmount = (float) sprintf('%01.2f', $payPalOrder->getOrderAmountValue());
        $cartAmount = (float) sprintf('%01.2f', $cart->getOrderTotal(true, Cart::BOTH));

        if ($paypalOrderAmount + 0.05 < $cartAmount || $paypalOrderAmount - 0.05 > $cartAmount) {
            throw new PsCheckoutException('The transaction amount does not match with the cart amount.', PsCheckoutException::DIFFERENCE_BETWEEN_TRANSACTION_AND_CART);
        }
    }

    /**
     * @param PrestaShopCart $cart
     *
     * @return bool
     */
    private function isAllProductsInStock(PrestaShopCart $cart): bool
    {
        if (!$this->configuration->get('PS_STOCK_MANAGEMENT')) {
            return true;
        }

        return $cart->isAllProductsInStock();
    }

    /**
     * @param PrestaShopCart $cart
     *
     * @return bool
     */
    private function checkAllProductsAreStillAvailableInThisState(PrestaShopCart $cart): bool
    {
        if (method_exists($cart, 'checkAllProductsAreStillAvailableInThisState')) {
            return $cart->checkAllProductsAreStillAvailableInThisState();
        }

        return true;
    }

    /**
     * @param PrestaShopCart $cart
     *
     * @return bool
     */
    private function checkAllProductsHaveMinimalQuantities(PrestaShopCart $cart): bool
    {
        if (method_exists($cart, 'checkAllProductsHaveMinimalQuantities')) {
            return $cart->checkAllProductsHaveMinimalQuantities();
        }

        return true;
    }
}
