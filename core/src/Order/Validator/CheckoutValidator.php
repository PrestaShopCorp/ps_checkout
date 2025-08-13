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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Repository\CartRepositoryInterface;
use PsCheckout\Infrastructure\Repository\OrderRepositoryInterface;
use PsCheckout\Infrastructure\Repository\PayPalOrderRepository;

class CheckoutValidator implements CheckoutValidatorInterface
{
    /**
     * @var PayPalOrderRepository
     */
    private $payPalOrderRepository;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    public function __construct(
        PayPalOrderRepository $payPalOrderRepository,
        OrderRepositoryInterface $orderRepository,
        CartRepositoryInterface $cartRepository
    ) {
        $this->payPalOrderRepository = $payPalOrderRepository;
        $this->orderRepository = $orderRepository;
        $this->cartRepository = $cartRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function validate(string $id, int $cartId)
    {
        $psCheckoutOrder = $this->payPalOrderRepository->getOneBy(['id' => $id]);

        if (!$psCheckoutOrder) {
            throw new PsCheckoutException('PayPal Order not found', PsCheckoutException::PAYPAL_ORDER_NOT_FOUND);
        }

        /** @var \Cart|null $cart */
        $cart = $this->cartRepository->getOneBy([
            'id_cart' => $cartId,
        ]);

        if (!$cart) {
            throw new PsCheckoutException('Cart does not exist', PsCheckoutException::PRESTASHOP_CART_NOT_FOUND);
        }

        if (!$cart->getProducts(true)) {
            throw new PsCheckoutException(sprintf('Cart with id %s has no product. Cannot create the order.', var_export($cart->id, true)), PsCheckoutException::CART_PRODUCT_MISSING);
        }

        $orders = $this->orderRepository->getAllBy(['id_cart' => $cart->id]);

        if (!empty($orders)) {
            throw new PsCheckoutException('Order already exist', PsCheckoutException::PRESTASHOP_ORDER_ALREADY_EXISTS);
        }
    }
}
