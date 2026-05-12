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

namespace PsCheckout\Infrastructure\Builder;

use Cart;
use Currency;
use PsCheckout\Core\PayPal\ShippingCallback\Builder\ShippingOptionsBuilderInterface;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Repository\PsCheckoutCarrierRepository;
use Validate;

class ShippingOptionsBuilder implements ShippingOptionsBuilderInterface
{
    /** @var CartInterface */
    private $cartAdapter;

    /** @var PsCheckoutCarrierRepository */
    private $carrierRepository;

    public function __construct(CartInterface $cartAdapter, PsCheckoutCarrierRepository $carrierRepository)
    {
        $this->cartAdapter = $cartAdapter;
        $this->carrierRepository = $carrierRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function build(int $cartId, ?string $selectedOptionId): array
    {
        $cart = $this->cartAdapter->getCart($cartId);

        if (!$cart || !Validate::isLoadedObject($cart)) {
            return [];
        }

        $deliveryOptions = $cart->getDeliveryOptionList();

        if (empty($deliveryOptions)) {
            return [];
        }

        $selectedCarrierId = (int) trim((string) current($cart->getDeliveryOption(null, false, false)), ',');

        return $this->buildShippingOptions($cart, $deliveryOptions, $selectedCarrierId, $selectedOptionId);
    }

    /**
     * {@inheritDoc}
     */
    public function getSelectedShippingPrice(array $shippingOptions): float
    {
        foreach ($shippingOptions as $option) {
            if ($option['selected']) {
                return (float) $option['amount']['value'];
            }
        }

        return 0.0;
    }

    public function formatShippingOptionIdFromCarrierId(string $carrierId): string
    {
        return sprintf('delivery-option-%s', $carrierId);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildShippingOptions(Cart $cart, array $deliveryOptions, int $selectedCarrierId, ?string $selectedOptionId): array
    {
        $shippingOptions = [];
        $currencyCode = (new Currency($cart->id_currency))->iso_code;

        foreach ($deliveryOptions as $options) {
            foreach ($options as $optionId => $option) {
                if (!isset($option['carrier_list'])) {
                    continue;
                }
                foreach ($option['carrier_list'] as $carrierId => $carrierData) {
                    $carrierType = $this->carrierRepository->getTypeByCarrierId((int) $carrierId);

                    if ($carrierType === null) {
                        continue; // carrier disabled for PayPal shipping options
                    }

                    $price = isset($carrierData['price_with_tax'])
                        ? (float) $carrierData['price_with_tax']
                        : (float) ($carrierData['price'] ?? 0);

                    $label = isset($carrierData['instance']) ? (string) $carrierData['instance']->name : (string) $carrierId;

                    $shippingOptions[] = [
                        'id' => $this->formatShippingOptionIdFromCarrierId((string) $carrierId),
                        'label' => $label,
                        'type' => $carrierType,
                        'amount' => [
                            'currency_code' => $currencyCode,
                            'value' => number_format($price, 2, '.', ''),
                        ],
                        'selected' => false,
                    ];
                }
            }

            break; // Only process the first address's options
        }

        if (empty($shippingOptions)) {
            return [];
        }

        $marked = false;

        foreach ($shippingOptions as &$option) {
            if ($selectedOptionId !== null && $option['id'] === $selectedOptionId) {
                $option['selected'] = true;
                $marked = true;
            } elseif ($selectedCarrierId && $option['id'] === $this->formatShippingOptionIdFromCarrierId((string) $selectedCarrierId)) {
                $option['selected'] = true;
                $marked = true;
            }
        }
        unset($option);

        if (!$marked) {
            $shippingOptions[0]['selected'] = true;
        }

        return $shippingOptions;
    }
}
