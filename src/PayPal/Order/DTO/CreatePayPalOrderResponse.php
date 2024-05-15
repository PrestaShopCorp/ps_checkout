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

class CreatePayPalOrderResponse
{
    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $create_time;
    /**
     * @var string
     */
    private $update_time;
    /**
     * @var PaymentSourceResponse
     */
    private $payment_source;
    /**
     * @var string
     */
    private $intent;
    /**
     * @var string
     */
    private $processing_instruction;
    /**
     * @var Payer
     */
    private $payer;
    /**
     * @var PurchaseUnit[]
     */
    private $purchase_units;
    /**
     * @var string
     */
    private $status;
    /**
     * @var LinkDescription[]
     */
    private $links;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * @param string $create_time
     */
    public function setCreateTime($create_time)
    {
        $this->create_time = $create_time;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * @param string $update_time
     */
    public function setUpdateTime($update_time)
    {
        $this->update_time = $update_time;

        return $this;
    }

    /**
     * @return PaymentSourceResponse|null
     */
    public function getPaymentSource()
    {
        return $this->payment_source;
    }

    /**
     * @param PaymentSourceResponse $payment_source
     */
    public function setPaymentSource($payment_source)
    {
        $this->payment_source = $payment_source;

        return $this;
    }

    /**
     * @return string
     */
    public function getIntent()
    {
        return $this->intent;
    }

    /**
     * @param string $intent
     */
    public function setIntent($intent)
    {
        $this->intent = $intent;

        return $this;
    }

    /**
     * @return string
     */
    public function getProcessingInstruction()
    {
        return $this->processing_instruction;
    }

    /**
     * @param string $processing_instruction
     */
    public function setProcessingInstruction($processing_instruction)
    {
        $this->processing_instruction = $processing_instruction;

        return $this;
    }

    /**
     * @return Payer
     */
    public function getPayer()
    {
        return $this->payer;
    }

    /**
     * @param Payer $payer
     */
    public function setPayer($payer)
    {
        $this->payer = $payer;

        return $this;
    }

    /**
     * @return PurchaseUnit[]
     */
    public function getPurchaseUnits()
    {
        return $this->purchase_units;
    }

    /**
     * @param PurchaseUnit[] $purchase_units
     */
    public function setPurchaseUnits($purchase_units)
    {
        $this->purchase_units = $purchase_units;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return LinkDescription[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param LinkDescription[] $links
     */
    public function setLinks($links)
    {
        $this->links = $links;

        return $this;
    }
}
