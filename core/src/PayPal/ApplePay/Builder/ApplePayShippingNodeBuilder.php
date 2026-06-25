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

namespace PsCheckout\Core\PayPal\ApplePay\Builder;

use PsCheckout\Core\Order\Builder\CheckoutContextInterface;

class ApplePayShippingNodeBuilder implements ApplePayNodeBuilderInterface
{
    /**
     * @var ApplePayShippingTypeResolver
     */
    private $typeResolver;

    public function __construct(ApplePayShippingTypeResolver $typeResolver)
    {
        $this->typeResolver = $typeResolver;
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        if ($context->isVirtualCart()) {
            return [];
        }

        $shippingOptions = $context->getShippingOptions();

        $data = [];

        $shippingType = $this->resolveShippingType($shippingOptions);
        if ($shippingType !== null) {
            $data['shipping_type'] = $shippingType;
        }

        if (empty($shippingOptions)) {
            return $data;
        }

        $selected = [];
        $others = [];
        foreach ($shippingOptions as $option) {
            /** @var string $identifier */
            $identifier = $option['id'] ?? '';
            /** @var string $optionLabel */
            $optionLabel = $option['label'] ?? '';
            /** @var array<string, mixed> $optionAmount */
            $optionAmount = $option['amount'] ?? [];
            /** @var string $amountValue */
            $amountValue = $optionAmount['value'] ?? '0.00';
            $method = [
                'identifier' => $identifier,
                'label' => $optionLabel,
                'detail' => '',
                'amount' => $amountValue,
            ];

            if (!empty($option['selected'])) {
                $selected[] = $method;
            } else {
                $others[] = $method;
            }
        }

        $data['shipping_methods'] = array_merge($selected, $others);

        return $data;
    }

    /**
     * @param array<int, array<string, mixed>> $shippingOptions
     */
    private function resolveShippingType(array $shippingOptions): ?string
    {
        foreach ($shippingOptions as $option) {
            if (!empty($option['selected'])) {
                /** @var string $psType */
                $psType = $option['type'] ?? '';

                return $this->typeResolver->resolve($psType);
            }
        }

        return null;
    }
}
