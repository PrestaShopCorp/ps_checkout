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

use Cart;
use Currency;
use PsCheckout\Core\PayPal\ShippingCallback\Builder\ShippingOptionsBuilderInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Exception\ShippingCallbackException;
use PsCheckout\Core\PayPal\ShippingCallback\Service\ShippingCallbackProcessorInterface;
use PsCheckout\Core\PayPal\ShippingCallback\ValueObject\ShippingCallbackPayload;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use Psr\Log\LoggerInterface;
use Validate;

class ShippingCallbackProcessor implements ShippingCallbackProcessorInterface
{
    /**
     * @var CartInterface
     */
    private $cartAdapter;

    /**
     * @var ShippingOptionsBuilderInterface
     */
    private $shippingOptionsBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(CartInterface $cartAdapter, ShippingOptionsBuilderInterface $shippingOptionsBuilder, LoggerInterface $logger)
    {
        $this->cartAdapter = $cartAdapter;
        $this->shippingOptionsBuilder = $shippingOptionsBuilder;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process(int $cartId, ShippingCallbackPayload $payload): array
    {
        $cart = $this->cartAdapter->getCart($cartId);

        if (!$cart || !Validate::isLoadedObject($cart)) {
            throw new ShippingCallbackException(
                ShippingCallbackException::METHOD_UNAVAILABLE,
                sprintf('Cart %d not found', $cartId)
            );
        }

        $deliveryOptions = $cart->getDeliveryOptionList();

        if (empty($deliveryOptions)) {
            $this->logger->warning('No delivery options available for cart', ['id_cart' => $cartId]);

            throw new ShippingCallbackException(
                ShippingCallbackException::ADDRESS_ERROR,
                sprintf('No shipping options available for cart %d', $cartId)
            );
        }

        $shippingOptions = $this->shippingOptionsBuilder->build($cartId, $payload->getShippingOptionId());
        $selectedPrice = $this->shippingOptionsBuilder->getSelectedShippingPrice($shippingOptions);
        $currencyCode = (new Currency($cart->id_currency))->iso_code;

        $total = $cart->getOrderTotal(true, Cart::BOTH);
        $itemTotal = $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);
        $taxTotal = $total - $itemTotal;
        $total = round($itemTotal + $selectedPrice, 2);

        return [
            'purchase_units' => [
                [
                    'reference_id' => 'default',
                    'amount' => [
                        'currency_code' => $currencyCode,
                        'value' => number_format($total, 2, '.', ''),
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => $currencyCode,
                                'value' => number_format($itemTotal, 2, '.', ''),
                            ],
                            'tax_total' => [
                                'currency_code' => $currencyCode,
                                'value' => number_format($taxTotal, 2, '.', ''),
                            ],
                            'shipping' => [
                                'currency_code' => $currencyCode,
                                'value' => number_format($selectedPrice, 2, '.', ''),
                            ],
                        ],
                    ],
                    'shipping_options' => $shippingOptions,
                ],
            ],
        ];
    }
}
