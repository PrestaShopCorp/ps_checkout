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
use PsCheckout\Core\Order\Builder\OrderPayloadBuilderInterface;
use PsCheckout\Presentation\TranslatorInterface;

class ApplePayAmountNodeBuilder implements ApplePayNodeBuilderInterface
{
    /**
     * @var OrderPayloadBuilderInterface
     */
    private $orderPayloadBuilder;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        OrderPayloadBuilderInterface $orderPayloadBuilder,
        TranslatorInterface $translator
    ) {
        $this->orderPayloadBuilder = $orderPayloadBuilder;
        $this->translator = $translator;
    }

    /**
     * {@inheritDoc}
     */
    public function build(CheckoutContextInterface $context): array
    {
        $payload = $this->orderPayloadBuilder->build($context);
        /** @var array<int, array<string, mixed>> $purchaseUnits */
        $purchaseUnits = $payload['purchase_units'];
        /** @var array<string, mixed> $purchaseUnit */
        $purchaseUnit = $purchaseUnits[0];
        /** @var array<string, mixed> $amount */
        $amount = $purchaseUnit['amount'];
        /** @var array<string, mixed> $breakdown */
        $breakdown = isset($amount['breakdown']) ? $amount['breakdown'] : [];

        /** @var string $currencyCode */
        $currencyCode = $amount['currency_code'] ?? '';
        /** @var string $totalValue */
        $totalValue = $amount['value'] ?? '0.00';

        $data = [
            'currency_code' => $currencyCode,
            'total' => [
                'type' => 'final',
                'label' => $this->translator->trans('Total'),
                'amount' => $totalValue,
            ],
        ];

        $lineItems = $this->buildLineItems($breakdown, $context->isVirtualCart());
        if (!empty($lineItems)) {
            $data['line_items'] = $lineItems;
        }

        return $data;
    }

    /**
     * @param array<string, mixed> $breakdown
     *
     * @return array<int, array<string, string>>
     */
    private function buildLineItems(array $breakdown, bool $isVirtual): array
    {
        $items = [];

        /** @var array<string, mixed> $itemTotal */
        $itemTotal = $breakdown['item_total'] ?? [];
        /** @var string $subtotalValue */
        $subtotalValue = $itemTotal['value'] ?? '0.00';
        if ((float) $subtotalValue > 0) {
            $items[] = ['type' => 'final', 'label' => $this->translator->trans('Subtotal'), 'amount' => $subtotalValue];
        }

        /** @var array<string, mixed> $taxTotal */
        $taxTotal = $breakdown['tax_total'] ?? [];
        /** @var string $taxValue */
        $taxValue = $taxTotal['value'] ?? '0.00';
        if ((float) $taxValue > 0) {
            $items[] = ['type' => 'final', 'label' => $this->translator->trans('Tax'), 'amount' => $taxValue];
        }

        if (!$isVirtual && isset($breakdown['shipping'])) {
            /** @var array<string, mixed> $shippingEntry */
            $shippingEntry = $breakdown['shipping'];
            /** @var string $shippingValue */
            $shippingValue = $shippingEntry['value'] ?? '0.00';
            $items[] = ['type' => 'final', 'label' => $this->translator->trans('Shipping'), 'amount' => $shippingValue];
        }

        /** @var array<string, mixed> $handlingEntry */
        $handlingEntry = $breakdown['handling'] ?? [];
        /** @var string $handlingValue */
        $handlingValue = $handlingEntry['value'] ?? '0.00';
        if ((float) $handlingValue > 0) {
            $items[] = ['type' => 'final', 'label' => $this->translator->trans('Handling'), 'amount' => $handlingValue];
        }

        /** @var array<string, mixed> $discountEntry */
        $discountEntry = $breakdown['discount'] ?? [];
        /** @var string $discountValue */
        $discountValue = $discountEntry['value'] ?? '0.00';
        if ((float) $discountValue > 0) {
            $items[] = ['type' => 'final', 'label' => $this->translator->trans('Discount'), 'amount' => '-' . $discountValue];
        }

        return $items;
    }
}
