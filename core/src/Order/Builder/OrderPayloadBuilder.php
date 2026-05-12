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

namespace PsCheckout\Core\Order\Builder;

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\Order\Builder\Node\AmountBreakdownNodeInterface;
use PsCheckout\Core\Order\Builder\Node\BaseNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\ShippingNodeBuilderInterface;
use PsCheckout\Core\Order\Builder\Node\SupplementaryDataNodeBuilderInterface;

class OrderPayloadBuilder implements OrderPayloadBuilderInterface
{
    /** @var BaseNodeBuilderInterface */
    private $baseNodeBuilder;

    /** @var AmountBreakdownNodeInterface */
    private $amountBreakdownNodeBuilder;

    /** @var ShippingNodeBuilderInterface */
    private $shippingNodeBuilder;

    /** @var SupplementaryDataNodeBuilderInterface */
    private $supplementaryDataNodeBuilder;

    /** @var PaymentSourceNodeBuilderRegistryInterface */
    private $registry;

    public function __construct(
        BaseNodeBuilderInterface $baseNodeBuilder,
        AmountBreakdownNodeInterface $amountBreakdownNodeBuilder,
        ShippingNodeBuilderInterface $shippingNodeBuilder,
        SupplementaryDataNodeBuilderInterface $supplementaryDataNodeBuilder,
        PaymentSourceNodeBuilderRegistryInterface $registry
    ) {
        $this->baseNodeBuilder = $baseNodeBuilder;
        $this->amountBreakdownNodeBuilder = $amountBreakdownNodeBuilder;
        $this->shippingNodeBuilder = $shippingNodeBuilder;
        $this->supplementaryDataNodeBuilder = $supplementaryDataNodeBuilder;
        $this->registry = $registry;
    }

    /** {@inheritDoc} */
    public function build(CheckoutContextInterface $context): array
    {
        if ($context->isUpdate() && $context->getPaypalOrderId() === null) {
            throw new PsCheckoutException('PayPal order ID is required when building payload for updating an order');
        }

        $cart = $context->getCart();

        $payload = $this->baseNodeBuilder
            ->setCart($cart)
            ->setIsVault($context->isVault())
            ->setIsUpdate($context->isUpdate())
            ->setPaypalOrderId($context->getPaypalOrderId())
            ->build();

        $amountBreakdown = $this->amountBreakdownNodeBuilder
            ->setCart($cart)
            ->setFundingSource($context->getFundingSource())
            ->build();
        if (!empty($amountBreakdown)) {
            $payload['purchase_units'][0] = array_replace_recursive($payload['purchase_units'][0], $amountBreakdown);
        }

        if ($context->hasShippingAddress()) {
            $payload['purchase_units'][0] = array_merge(
                $payload['purchase_units'][0],
                $this->shippingNodeBuilder->setCart($cart)->build()
            );
        }

        if (!$context->isUpdate()) {
            if ($context->isCard()) {
                $paymentSource = $this->registry->findBuilder('card')->build($context);
                if (!empty($paymentSource)) {
                    $payload = array_replace_recursive($payload, $paymentSource);
                }
                $payload['purchase_units'][0] = array_merge(
                    $payload['purchase_units'][0],
                    $this->supplementaryDataNodeBuilder->setCart($cart)->setPayload($payload)->build()
                );
            } else {
                try {
                    $paymentSource = $this->registry->findBuilder($context->getFundingSource())->build($context);
                    if (!empty($paymentSource)) {
                        $payload = array_replace_recursive($payload, $paymentSource);
                    }
                } catch (PsCheckoutException $e) {
                    // unknown or unsupported funding source — skip payment_source node
                }
            }

            $shippingOptions = $context->getShippingOptions();
            if (!$context->isCard() && !empty($shippingOptions)) {
                $payload['purchase_units'][0]['shipping_options'] = $shippingOptions;
            }

            if ($context->getFundingSource() === 'pay_upon_invoice') {
                $payload['processing_instruction'] = 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL';
            }
        }

        return $payload;
    }
}
