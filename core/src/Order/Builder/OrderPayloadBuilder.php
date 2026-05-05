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
    /** @var array<string, mixed> */
    private $cart;

    /** @var string */
    private $fundingSource;

    /** @var string */
    private $paypalOrderId;

    /** @var string|null */
    private $paypalCustomerId;

    /** @var string|null */
    private $paypalVaultId;

    /** @var bool */
    private $savePaymentMethod = false;

    /** @var bool */
    private $isUpdate = false;

    /** @var bool */
    private $expressCheckout = false;

    /** @var bool */
    private $isVault = false;

    /** @var bool */
    private $isCard = false;

    /** @var array */
    private $payload = [];

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

    /** @var string|null */
    private $birthDate;

    /** @var string|null */
    private $phone;

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
    public function build(bool $isFullPayload = true): array
    {
        $this->checkPaypalOrderIdWhenUpdate();

        $this->payload = $this->buildBasePayload();

        $optionalPayload = $this->buildOptionalPayload($isFullPayload);

        $this->mergePayload($optionalPayload);

        return $this->payload;
    }

    private function buildBasePayload(): array
    {
        return $this->baseNodeBuilder
            ->setCart($this->cart)
            ->setIsVault($this->isVault)
            ->setIsUpdate($this->isUpdate)
            ->setPaypalOrderId($this->paypalOrderId)
            ->build();
    }

    private function buildOptionalPayload(bool $isFullPayload): array
    {
        $optionalPayload = [];

        if ($isFullPayload) {
            $amountBreakdown = $this->amountBreakdownNodeBuilder
                ->setCart($this->cart)
                ->setFundingSource($this->fundingSource)
                ->build();
            if (!empty($amountBreakdown)) {
                $this->payload['purchase_units'][0] = array_replace_recursive($this->payload['purchase_units'][0], $amountBreakdown);
            }
        }

        if ($this->shippingAddressExists()) {
            $this->payload['purchase_units'][0] = array_merge($this->payload['purchase_units'][0], $this->shippingNodeBuilder->setCart($this->cart)->build());
        }

        if ($this->isCard) {
            $context = $this->buildCheckoutContext('card');
            $optionalPayload[] = $this->registry->findBuilder('card')->build($context);
            $this->payload['purchase_units'][0] = array_merge(
                $this->payload['purchase_units'][0],
                $this->supplementaryDataNodeBuilder->setCart($this->cart)->setPayload($this->payload)->build()
            );
        }

        if ($isFullPayload && !$this->isCard) {
            try {
                $context = $this->buildCheckoutContext($this->fundingSource);
                $paymentSource = $this->registry->findBuilder($this->fundingSource)->build($context);
                if (!empty($paymentSource)) {
                    $optionalPayload[] = $paymentSource;
                }
            } catch (PsCheckoutException $e) {
                // unknown or unsupported funding source — skip payment_source node
            }
        }

        if ($this->fundingSource === 'pay_upon_invoice' && !$this->isUpdate) {
            $optionalPayload[] = ['processing_instruction' => 'ORDER_COMPLETE_ON_PAYMENT_APPROVAL'];
        }

        return $optionalPayload;
    }

    private function buildCheckoutContext(string $fundingSource): CheckoutContextInterface
    {
        return new CheckoutContext(
            $this->cart,
            $fundingSource,
            $this->savePaymentMethod,
            $this->paypalCustomerId,
            $this->paypalVaultId,
            $this->expressCheckout,
            $this->isUpdate,
            $this->birthDate,
            $this->phone
        );
    }

    private function mergePayload(array $optionalPayload): void
    {
        foreach ($optionalPayload as $node) {
            if (!empty($node)) {
                $this->payload = array_replace_recursive($this->payload, $node);
            }
        }
    }

    /**
     * @throws PsCheckoutException
     */
    private function checkPaypalOrderIdWhenUpdate(): void
    {
        if ($this->isUpdate && empty($this->paypalOrderId)) {
            throw new PsCheckoutException('PayPal order ID is required when building payload for updating an order');
        }
    }

    /** {@inheritDoc} */
    public function setCart(array $cart): self
    {
        $this->cart = $cart;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsUpdate(bool $isUpdate): self
    {
        $this->isUpdate = $isUpdate;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsExpressCheckout(bool $isExpressCheckout): self
    {
        $this->expressCheckout = $isExpressCheckout;

        return $this;
    }

    /** {@inheritDoc} */
    public function setSavePaymentMethod(bool $savePaymentMethod): self
    {
        $this->savePaymentMethod = $savePaymentMethod;

        return $this;
    }

    /** {@inheritDoc} */
    public function setFundingSource(string $fundingSource): self
    {
        $this->fundingSource = $fundingSource;

        return $this;
    }

    /** {@inheritDoc} */
    public function setPaypalCustomerId(string $paypalCustomerId): self
    {
        $this->paypalCustomerId = $paypalCustomerId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setPaypalVaultId(string $paypalVaultId): self
    {
        $this->paypalVaultId = $paypalVaultId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setPaypalOrderId(string $paypalOrderId): self
    {
        $this->paypalOrderId = $paypalOrderId;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsVault(bool $isVault): self
    {
        $this->isVault = $isVault;

        return $this;
    }

    /** {@inheritDoc} */
    public function setIsCard(bool $isCard): self
    {
        $this->isCard = $isCard;

        return $this;
    }

    private function shippingAddressExists(): bool
    {
        if (isset($this->cart['addresses']['shipping'])) {
            return $this->cart['addresses']['shipping']->id !== null;
        }

        return false;
    }

    /** {@inheritDoc} */
    public function setCustomerBirthDay($birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    /** {@inheritDoc} */
    public function setCustomerPhone($phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
