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

namespace PsCheckout\Infrastructure\Action;

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Request\ValueObject\CreatePayPalOrderRequest;
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class AddProductToCartAction implements AddProductToCartActionInterface
{
    /** @var ContextInterface */
    private $context;

    public function __construct(
        ContextInterface $context
    ) {
        $this->context = $context;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(CreatePayPalOrderRequest $requestData)
    {
        try {
            $cart = $this->createCartInstance();
        } catch (\Exception $exception) {
            throw new PsCheckoutException('Failed to create cart instance');
        }

        /**
         * @var array{quantity: string, deep_quantity: string} $quantityInCart
         */
        $quantityInCart = $cart->getProductQuantity(
            (int) $requestData->getIdProduct(),
            !$requestData->getIdProductAttribute() ? 0 : $requestData->getIdProductAttribute(),
            !$requestData->getIdCustomization() ? 0 : $requestData->getIdCustomization()
        );

        $quantityWanted = (int) $requestData->getQuantityWanted();

        $quantityToAdd = $quantityWanted - (int) $quantityInCart['quantity'];

        if ($quantityToAdd !== 0) {
            if (!$cart->updateQty(
                $quantityToAdd < 0 ? -1 * $quantityToAdd : $quantityToAdd,
                $requestData->getIdProduct(),
                !$requestData->getIdProductAttribute() ? null : $requestData->getIdProductAttribute(),
                !$requestData->getIdCustomization() ? false : $requestData->getIdCustomization(),
                $quantityToAdd < 0 ? 'down' : 'up'
            )) {
                $cart->delete();

                throw new PsCheckoutException('Failed to add product to cart');
            }
        }

        try {
            $this->context->setCurrentCart($cart);
        } catch (\Exception $exception) {
            $cart->delete();

            throw new PsCheckoutException('Failed to update cart context');
        }
    }

    /**
     * @throws \Throwable
     */
    private function createCartInstance(): \Cart
    {
        $existingCart = $this->context->getCart();

        if ($existingCart && (int) $existingCart->id) {
            return $existingCart;
        }

        $cart = new \Cart();

        $cart->id_currency = $this->context->getCurrency()->id;
        $cart->id_lang = $this->context->getLanguage()->id;

        $cart->add();

        return $cart;
    }
}
