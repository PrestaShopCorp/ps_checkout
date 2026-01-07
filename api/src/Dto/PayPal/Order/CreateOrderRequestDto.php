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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\OrderIntent;
use PsCheckout\Api\Dto\PayPal\OrderApplicationContext;
use PsCheckout\Api\Dto\PayPal\Payer;
use PsCheckout\Api\Dto\PayPal\PaymentSource;
use PsCheckout\Api\Dto\PayPal\PurchaseUnitRequest;

class CreateOrderRequestDto
{
    /**
     * @var value-of<OrderIntent::INTENTS>
     */
    private $intent;

    /**
     * @var Payer|null
     */
    private $payer;

    /**
     * @var PurchaseUnitRequest[]
     */
    private $purchaseUnits;

    /**
     * @var PaymentSource|null
     */
    private $paymentSource;

    /**
     * @deprecated
     *
     * @var OrderApplicationContext|null
     */
    private $applicationContext;

    /**
     * @param value-of<OrderIntent::INTENTS> $intent
     * @param PurchaseUnitRequest[] $purchaseUnits
     */
    public function __construct(
        string $intent,
        array $purchaseUnits
    ) {
        $this->intent = $intent;
        $this->purchaseUnits = $purchaseUnits;
    }

    /**
     * Returns Intent.
     * The intent to either capture payment immediately or authorize a payment for an order after order
     * creation.
     *
     * @return value-of<OrderIntent::INTENTS>
     */
    public function getIntent(): string
    {
        return $this->intent;
    }

    /**
     * Sets Intent.
     * The intent to either capture payment immediately or authorize a payment for an order after order
     * creation.
     *
     * @param value-of<OrderIntent::INTENTS> $intent
     *
     * @required
     * @maps intent
     * @return self
     */
    public function setIntent(string $intent): self
    {
        $this->intent = $intent;

        return $this;
    }

    /**
     * Returns Payer.
     * DEPRECATED. The customer is also known as the payer. The Payer object was intended to only be used
     * with the `payment_source.paypal` object. In order to make this design more clear, the details in the
     * `payer` object are now available under `payment_source.paypal`. Please use `payment_source.paypal`.
     *
     * @deprecated
     */
    public function getPayer(): ?Payer
    {
        return $this->payer;
    }

    /**
     * Sets Payer.
     * DEPRECATED. The customer is also known as the payer. The Payer object was intended to only be used
     * with the `payment_source.paypal` object. In order to make this design more clear, the details in the
     * `payer` object are now available under `payment_source.paypal`. Please use `payment_source.paypal`.
     *
     * @deprecated
     *
     * @maps payer
     * @return self
     */
    public function setPayer(?Payer $payer): self
    {
        $this->payer = $payer;

        return $this;
    }

    /**
     * Returns Purchase Units.
     * An array of purchase units. Each purchase unit establishes a contract between a payer and the payee.
     * Each purchase unit represents either a full or partial order that the payer intends to purchase from
     * the payee.
     *
     * @return PurchaseUnitRequest[]
     */
    public function getPurchaseUnits(): array
    {
        return $this->purchaseUnits;
    }

    /**
     * Sets Purchase Units.
     * An array of purchase units. Each purchase unit establishes a contract between a payer and the payee.
     * Each purchase unit represents either a full or partial order that the payer intends to purchase from
     * the payee.
     *
     * @required
     * @maps purchase_units
     *
     * @param PurchaseUnitRequest[] $purchaseUnits
     * @return self
     */
    public function setPurchaseUnits(array $purchaseUnits): self
    {
        $this->purchaseUnits = $purchaseUnits;

        return $this;
    }

    /**
     * Adds a Purchase Unit to the request.
     *
     * @param PurchaseUnitRequest $purchaseUnit
     * @return self
     */
    public function addPurchaseUnit(PurchaseUnitRequest $purchaseUnit): self
    {
        $this->purchaseUnits[] = $purchaseUnit;

        return $this;
    }

    /**
     * Returns Payment Source.
     * The payment source definition.
     */
    public function getPaymentSource(): ?PaymentSource
    {
        return $this->paymentSource;
    }

    /**
     * Sets Payment Source.
     * The payment source definition.
     *
     * @maps payment_source
     * @return self
     */
    public function setPaymentSource(?PaymentSource $paymentSource): self
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }

    /**
     * Returns Application Context.
     * Customizes the payer experience during the approval process for the payment with PayPal. Note:
     * Partners and Marketplaces might configure brand_name and shipping_preference during partner account
     * setup, which overrides the request values.
     *
     * @deprecated
     */
    public function getApplicationContext(): ?OrderApplicationContext
    {
        return $this->applicationContext;
    }

    /**
     * Sets Application Context.
     * Customizes the payer experience during the approval process for the payment with PayPal. Note:
     * Partners and Marketplaces might configure brand_name and shipping_preference during partner account
     * setup, which overrides the request values.
     *
     * @deprecated
     *
     * @maps application_context
     * @return self
     */
    public function setApplicationContext(?OrderApplicationContext $applicationContext): self
    {
        $this->applicationContext = $applicationContext;

        return $this;
    }
}
