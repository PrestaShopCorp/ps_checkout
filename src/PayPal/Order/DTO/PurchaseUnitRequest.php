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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class PurchaseUnitRequest
{
    /**
     * @var string
     */
    private $reference_id;
    /**
     * @var AmountWithBreakdown
     */
    private $amount;
    /**
     * @var PayeeRequest
     */
    private $payee;
    /**
     * @var string
     */
    private $description;
    /**
     * @var string
     */
    private $custom_id;
    /**
     * @var string
     */
    private $invoice_id;
    /**
     * @var string
     */
    private $soft_descriptor;
    /**
     * @var ItemRequest[]
     */
    private $items;
    /**
     * @var ShippingRequest
     */
    private $shipping;
    /**
     * @var SupplementaryDataRequest
     */
    private $supplementary_data;

    /**
     * @return string
     */
    public function getReferenceId()
    {
        return $this->reference_id;
    }

    /**
     * @param string $reference_id
     *
     * @return void
     */
    public function setReferenceId($reference_id)
    {
        $this->reference_id = $reference_id;
    }

    /**
     * @return AmountWithBreakdown
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param AmountWithBreakdown $amount
     *
     * @return void
     */
    public function setAmount(AmountWithBreakdown $amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return PayeeRequest
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * @param PayeeRequest $payee
     *
     * @return void
     */
    public function setPayee(PayeeRequest $payee)
    {
        $this->payee = $payee;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return void
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getCustomId()
    {
        return $this->custom_id;
    }

    /**
     * @param string $custom_id
     *
     * @return void
     */
    public function setCustomId($custom_id)
    {
        $this->custom_id = $custom_id;
    }

    /**
     * @return string
     */
    public function getInvoiceId()
    {
        return $this->invoice_id;
    }

    /**
     * @param string $invoice_id
     *
     * @return void
     */
    public function setInvoiceId($invoice_id)
    {
        $this->invoice_id = $invoice_id;
    }

    /**
     * @return string
     */
    public function getSoftDescriptor()
    {
        return $this->soft_descriptor;
    }

    /**
     * @param string $soft_descriptor
     *
     * @return void
     */
    public function setSoftDescriptor($soft_descriptor)
    {
        $this->soft_descriptor = $soft_descriptor;
    }

    /**
     * @return ItemRequest[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param ItemRequest[] $items
     *
     * @return void
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /**
     * @return ShippingRequest
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param ShippingRequest $shipping
     *
     * @return void
     */
    public function setShipping(ShippingRequest $shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return SupplementaryDataRequest
     */
    public function getSupplementaryData()
    {
        return $this->supplementary_data;
    }

    /**
     * @param SupplementaryDataRequest $supplementary_data
     *
     * @return void
     */
    public function setSupplementaryData(SupplementaryDataRequest $supplementary_data)
    {
        $this->supplementary_data = $supplementary_data;
    }
}
