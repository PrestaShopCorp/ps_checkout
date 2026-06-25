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

use PsCheckout\Core\PayPal\ShippingCallback\Builder\ShippingOptionsBuilderInterface;
use PsCheckout\Infrastructure\Adapter\CartDataInterface;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Adapter\HookInterface;
use PsCheckout\Infrastructure\Adapter\ModuleInterface;
use PsCheckout\Infrastructure\Repository\PsCheckoutCarrierRepository;

class ShippingOptionsBuilder implements ShippingOptionsBuilderInterface
{
    /** @var CartInterface */
    private $cartAdapter;

    /** @var PsCheckoutCarrierRepository */
    private $carrierRepository;

    /** @var HookInterface */
    private $hook;

    /** @var ModuleInterface */
    private $module;

    public function __construct(CartInterface $cartAdapter, PsCheckoutCarrierRepository $carrierRepository, HookInterface $hook, ModuleInterface $module)
    {
        $this->cartAdapter = $cartAdapter;
        $this->carrierRepository = $carrierRepository;
        $this->hook = $hook;
        $this->module = $module;
    }

    /**
     * {@inheritDoc}
     */
    public function build(int $cartId, ?string $selectedOptionId, array $preComputedDeliveryOptions = []): array
    {
        $cart = $this->cartAdapter->getCart($cartId);

        if (!$cart) {
            return [];
        }

        $deliveryOptions = !empty($preComputedDeliveryOptions)
            ? $preComputedDeliveryOptions
            : $cart->getDeliveryOptionList();

        if (empty($deliveryOptions)) {
            return [];
        }

        $selectedCarrierId = (int) trim((string) current($cart->getDeliveryOption()), ',');

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
    private function buildShippingOptions(CartDataInterface $cart, array $deliveryOptions, int $selectedCarrierId, ?string $selectedOptionId): array
    {
        $shippingOptions = [];
        $currencyCode = $cart->getCurrencyIsoCode();

        foreach ($deliveryOptions as $options) {
            foreach ($options as $optionId => $option) {
                if (!isset($option['carrier_list'])) {
                    continue;
                }
                foreach ($option['carrier_list'] as $carrierId => $carrierData) {
                    $data = $this->carrierRepository->getCarrierData((int) $carrierId);

                    $carrierType = $data ? $data['type'] : PsCheckoutCarrierRepository::TYPE_SHIPPING;
                    $disabled = $data ? $data['disabled'] : false;

                    $externalModuleName = $data ? $data['external_module_name'] : '';
                    if ($externalModuleName !== '') {
                        $idModule = $this->module->getModuleIdByName($externalModuleName);
                        $hookParams = [
                            'id_carrier' => (int) $carrierId,
                            'id_reference' => $data ? $data['id_reference'] : 0,
                            'type' => &$carrierType,
                            'disabled' => &$disabled,
                        ];
                        $this->hook->exec('actionGetPsCheckoutCarrierType', $hookParams, $idModule);
                    }

                    if ($disabled) {
                        continue;
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
            } elseif ($selectedOptionId === null && $selectedCarrierId && $option['id'] === $this->formatShippingOptionIdFromCarrierId((string) $selectedCarrierId)) {
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
