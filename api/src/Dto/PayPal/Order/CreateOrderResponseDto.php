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
use PsCheckout\Api\Dto\PayPal\Payer;

/**
 * The order details.
 */
class CreateOrderResponseDto
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
     * @var string|null
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
     * @var string|null
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
     */
    public function setCreateTime(?string $createTime): void
    {
        $this->createTime = $createTime;
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
     */
    public function setUpdateTime(?string $updateTime): void
    {
        $this->updateTime = $updateTime;
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
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
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
     */
    public function setPaymentSource(?PaymentSourceResponse $paymentSource): void
    {
        $this->paymentSource = $paymentSource;
    }

    /**
     * Returns Intent.
     * The intent to either capture payment immediately or authorize a payment for an order after order
     * creation.
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
     * @maps intent
     */
    public function setIntent(?string $intent): void
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
     */
    public function setPurchaseUnits(?array $purchaseUnits): void
    {
        $this->purchaseUnits = $purchaseUnits;
    }

    /**
     * Returns Status.
     * The order status.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The order status.
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
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
     */
    public function setLinks(?array $links): void
    {
        $this->links = $links;
    }
}
