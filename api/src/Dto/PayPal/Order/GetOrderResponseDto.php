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

use PsCheckout\Api\Dto\PayPal\LinkDescription;
use PsCheckout\Api\Dto\PayPal\OrderIntent;
use PsCheckout\Api\Dto\PayPal\OrderStatus;
use PsCheckout\Api\Dto\PayPal\Payer;
use PsCheckout\Api\Dto\PayPal\PaymentSourceResponse;
use PsCheckout\Api\Dto\PayPal\PurchaseUnit;

/**
 * The order details.
 */
class GetOrderResponseDto
{
    /**
     * @var string|null
     */
    private $createTime;

    /**
     * @var string|null
     */
    private $updateTime;

    /**
     * @var string|null
     */
    private $id;

    /**
     * @var PaymentSourceResponse|null
     */
    private $paymentSource;

    /**
     * @var value-of<OrderIntent::INTENTS>|null
     */
    private $intent;

    /**
     * @var Payer|null
     */
    private $payer;

    /**
     * @var PurchaseUnit[]|null
     */
    private $purchaseUnits;

    /**
     * @var value-of<OrderStatus::STATUSES>|null
     */
    private $status;

    /**
     * @var LinkDescription[]|null
     */
    private $links;

    /**
     * Returns Create Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     */
    public function getCreateTime(): ?string
    {
        return $this->createTime;
    }

    /**
     * Sets Create Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     *
     * @maps create_time
     * @return self
     */
    public function setCreateTime(?string $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Returns Update Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     */
    public function getUpdateTime(): ?string
    {
        return $this->updateTime;
    }

    /**
     * Sets Update Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     *
     * @maps update_time
     * @return self
     */
    public function setUpdateTime(?string $updateTime): self
    {
        $this->updateTime = $updateTime;

        return $this;
    }

    /**
     * Returns Id.
     * The ID of the order.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The ID of the order.
     *
     * @maps id
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Returns Payment Source.
     * The payment source used to fund the payment.
     */
    public function getPaymentSource(): ?PaymentSourceResponse
    {
        return $this->paymentSource;
    }

    /**
     * Sets Payment Source.
     * The payment source used to fund the payment.
     *
     * @maps payment_source
     * @return self
     */
    public function setPaymentSource(?PaymentSourceResponse $paymentSource): self
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }

    /**
     * Returns Intent.
     * The intent to either capture payment immediately or authorize a payment for an order after order
     * creation.
     *
     * @return value-of<OrderIntent::INTENTS>|null
     */
    public function getIntent(): ?string
    {
        return $this->intent;
    }

    /**
     * Sets Intent.
     * The intent to either capture payment immediately or authorize a payment for an order after order
     * creation.
     *
     * @param value-of<OrderIntent::INTENTS>|null $intent
     *
     * @maps intent
     * @return self
     */
    public function setIntent(?string $intent): self
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
     * An array of purchase units. Each purchase unit establishes a contract between a customer and
     * merchant. Each purchase unit represents either a full or partial order that the customer intends to
     * purchase from the merchant.
     *
     * @return PurchaseUnit[]|null
     */
    public function getPurchaseUnits(): ?array
    {
        return $this->purchaseUnits;
    }

    /**
     * Sets Purchase Units.
     * An array of purchase units. Each purchase unit establishes a contract between a customer and
     * merchant. Each purchase unit represents either a full or partial order that the customer intends to
     * purchase from the merchant.
     *
     * @maps purchase_units
     *
     * @param PurchaseUnit[]|null $purchaseUnits
     * @return self
     */
    public function setPurchaseUnits(?array $purchaseUnits): self
    {
        $this->purchaseUnits = $purchaseUnits;

        return $this;
    }

    /**
     * Returns Status.
     * The order status.
     *
     * @return value-of<OrderStatus::STATUSES>|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The order status.
     *
     * @param value-of<OrderStatus::STATUSES>|null $status
     *
     * @maps status
     * @return self
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Returns Links.
     * An array of request-related HATEOAS links. To complete payer approval, use the `approve` link to
     * redirect the payer. The API caller has 6 hours (default setting, this which can be changed by your
     * account manager to 24/48/72 hours to accommodate your use case) from the time the order is created,
     * to redirect your payer. Once redirected, the API caller has 6 hours for the payer to approve the
     * order and either authorize or capture the order. If you are not using the PayPal JavaScript SDK to
     * initiate PayPal Checkout (in context) ensure that you include `application_context.return_url` is
     * specified or you will get "We're sorry, Things don't appear to be working at the moment" after the
     * payer approves the payment.
     *
     * @return LinkDescription[]|null
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * Sets Links.
     * An array of request-related HATEOAS links. To complete payer approval, use the `approve` link to
     * redirect the payer. The API caller has 6 hours (default setting, this which can be changed by your
     * account manager to 24/48/72 hours to accommodate your use case) from the time the order is created,
     * to redirect your payer. Once redirected, the API caller has 6 hours for the payer to approve the
     * order and either authorize or capture the order. If you are not using the PayPal JavaScript SDK to
     * initiate PayPal Checkout (in context) ensure that you include `application_context.return_url` is
     * specified or you will get "We're sorry, Things don't appear to be working at the moment" after the
     * payer approves the payment.
     *
     * @maps links
     *
     * @param LinkDescription[]|null $links
     * @return self
     */
    public function setLinks(?array $links): self
    {
        $this->links = $links;

        return $this;
    }
}
