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

use PsCheckout\Api\Dto\PayPal\Payer;
use PsCheckout\Api\Dto\PayPal\PaymentSource;
use PsCheckout\Api\Dto\PayPal\PurchaseUnitRequest;

class CreateOrderRequestDto
{
    /**
     * @var string
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
     * @param string $intent
     * @param PurchaseUnitRequest[] $purchaseUnits
     */
    public function __construct(string $intent, array $purchaseUnits)
    {
        $this->intent = $intent;
        $this->purchaseUnits = $purchaseUnits;
    }

    /**
     * Returns Intent.
     * The intent to either capture payment immediately or authorize a payment for an order after order
     * creation.
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
     * @required
     * @maps intent
     */
    public function setIntent(string $intent): void
    {
        $this->intent = $intent;
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
     */
    public function setPayer(?Payer $payer): void
    {
        $this->payer = $payer;
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
     */
    public function setPurchaseUnits(array $purchaseUnits): void
    {
        $this->purchaseUnits = $purchaseUnits;
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
     */
    public function setPaymentSource(?PaymentSource $paymentSource): void
    {
        $this->paymentSource = $paymentSource;
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
     */
    public function setApplicationContext(?OrderApplicationContext $applicationContext): void
    {
        $this->applicationContext = $applicationContext;
    }
}
