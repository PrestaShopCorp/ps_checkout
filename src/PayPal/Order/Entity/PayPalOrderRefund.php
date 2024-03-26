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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity;

class PayPalOrderRefund
{
    const TABLE = 'pscheckout_refund';

    /**
     * @var string|null
     */
    private $id;
    /**
     * @var string|null
     */
    private $idOrder;
    /**
     * @var string|null
     */
    private $status;
    /**
     * @var string|null
     */
    private $invoiceId;
    /**
     * @var string|null
     */
    private $customId;
    /**
     * @var string|null
     */
    private $acquirerReferenceNumber;
    /**
     * @var array
     */
    private $sellerPayableBreakdown;
    /**
     * @var int
     */
    private $idOrderSlip;

    public function __construct($id = null, $idOrder = null, $status = null, $invoiceId = null, $customId = null, $acquirerReferenceNumber = null, $sellerPayableBreakdown = [], $idOrderSlip = null)
    {
        $this->id = $id;
        $this->idOrder = $idOrder;
        $this->status = $status;
        $this->invoiceId = $invoiceId;
        $this->customId = $customId;
        $this->acquirerReferenceNumber = $acquirerReferenceNumber;
        $this->sellerPayableBreakdown = $sellerPayableBreakdown;
        $this->idOrderSlip = $idOrderSlip;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     *
     * @return PayPalOrderRefund
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getIdOrder()
    {
        return $this->idOrder;
    }

    /**
     * @param string|null $idOrder
     *
     * @return PayPalOrderRefund
     */
    public function setIdOrder($idOrder)
    {
        $this->idOrder = $idOrder;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     *
     * @return PayPalOrderRefund
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getInvoiceId()
    {
        return $this->invoiceId;
    }

    /**
     * @param string|null $invoiceId
     *
     * @return PayPalOrderRefund
     */
    public function setInvoiceId($invoiceId)
    {
        $this->invoiceId = $invoiceId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCustomId()
    {
        return $this->customId;
    }

    /**
     * @param string|null $customId
     *
     * @return PayPalOrderRefund
     */
    public function setCustomId($customId)
    {
        $this->customId = $customId;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAcquirerReferenceNumber()
    {
        return $this->acquirerReferenceNumber;
    }

    /**
     * @param string|null $acquirerReferenceNumber
     *
     * @return PayPalOrderRefund
     */
    public function setAcquirerReferenceNumber($acquirerReferenceNumber)
    {
        $this->acquirerReferenceNumber = $acquirerReferenceNumber;

        return $this;
    }

    /**
     * @return array
     */
    public function getSellerPayableBreakdown()
    {
        return $this->sellerPayableBreakdown;
    }

    /**
     * @param array $sellerPayableBreakdown
     *
     * @return PayPalOrderRefund
     */
    public function setSellerPayableBreakdown($sellerPayableBreakdown)
    {
        $this->sellerPayableBreakdown = $sellerPayableBreakdown;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getIdOrderSlip()
    {
        return $this->idOrderSlip;
    }

    /**
     * @param int $idOrderSlip
     *
     * @return PayPalOrderRefund
     */
    public function setIdOrderSlip($idOrderSlip)
    {
        $this->idOrderSlip = (int) $idOrderSlip;

        return $this;
    }
}
