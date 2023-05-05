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

namespace PrestaShop\Module\PrestashopCheckout\Temp\Entities;

class PurchaseUnit
{
    /** @var Amount */
    private $amount;

    /** @var string */
    private $customId;

    /** @var string */
    private $description;

    /** @var string */
    private $invoiceId;

    /** @var Item[] */
    private $items;

    /** @var Payee */
    private $payee;

    /** @var string */
    private $referenceId;

    /** @var Shipping */
    private $shipping;

    /** @var string */
    private $softDescriptor;

    /**
     * @link https://developer.paypal.com/docs/api/orders/v2/#definition-purchase_unit_request
     *
     * @param Amount $amount
     */
    public function __construct($amount) {
        $this->setAmount($amount);
    }

    /**
     * @return Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param Amount $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getCustomId()
    {
        return $this->customId;
    }

    /**
     * @param string $customId
     */
    public function setCustomId($customId)
    {
        $this->customId = $customId;
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
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param string $invoiceId
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;
    }

    /**
     * @return Item[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param Item[] $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return Payee
     */
    public function getPayee()
    {
        return $this->payee;
    }

    /**
     * @param Payee $payee
     */
    public function setPayee($payee)
    {
        $this->payee = $payee;
    }

    /**
     * @return string
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param string $referenceId
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;
    }

    /**
     * @return Shipping
     */
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * @param Shipping $shipping
     */
    public function setShipping($shipping)
    {
        $this->shipping = $shipping;
    }

    /**
     * @return string
     */
    public function getSoftDescriptor()
    {
        return $this->softDescriptor;
    }

    /**
     * @param string $softDescriptor
     */
    public function setSoftDescriptor($softDescriptor)
    {
        $this->softDescriptor = $softDescriptor;
    }

    public function toArray()
    {
        $data = [
            'amount' => $this->getAmount()->toArray()
        ];

        if (!empty($this->getCustomId())) {
            $data['custom_id'] = $this->getCustomId();
        }

        if (!empty($this->getDescription())) {
            $data['description'] = $this->getDescription();
        }

        if (!empty($this->getInvoiceId())) {
            $data['invoice_id'] = $this->getInvoiceId();
        }

        if (!empty($this->getItems())) {
            foreach ($this->getItems() as $item) {
                $data['items'][] = $item->toArray();
            }
        }

        if (!empty($this->getPayee())) {
            $data['payee'] = $this->getPayee()->toArray();
        }

        if (!empty($this->getReferenceId())) {
            $data['reference_id'] = $this->getReferenceId();
        }

        if (!empty($this->getShipping())) {
            $data['shipping'] = $this->getShipping()->toArray();
        }

        if (!empty($this->getSoftDescriptor())) {
            $data['soft_descriptor'] = $this->getSoftDescriptor();
        }

        return array_filter($data);
    }
}
