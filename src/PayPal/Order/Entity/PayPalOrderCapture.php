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

class PayPalOrderCapture
{
    const TABLE = 'pscheckout_capture';

    /**
     * @var string
     */
    private $id;
    /**
     * @var string
     */
    private $idOrder;
    /**
     * @var string
     */
    private $status;
    /**
     * @var bool
     */
    private $finalCapture;
    /**
     * @var string
     */
    private $createdAt;
    /**
     * @var string
     */
    private $updatedAt;
    /**
     * @var array
     */
    private $sellerProtection;
    /**
     * @var array
     */
    private $sellerReceivableBreakdown;

    public function __construct($id, $idOrder, $status, $createdAt, $updatedAt, $sellerProtection, $sellerReceivableBreakdown, $finalCapture = false)
    {
        $this->id = $id;
        $this->idOrder = $idOrder;
        $this->status = $status;
        $this->finalCapture = (bool) $finalCapture;
        $this->createdAt = $createdAt;
        $this->updatedAt = $updatedAt;
        $this->sellerProtection = $sellerProtection;
        $this->sellerReceivableBreakdown = $sellerReceivableBreakdown;
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
     * @return PayPalOrderCapture
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
     * @return PayPalOrderCapture
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
     * @return PayPalOrderCapture
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return bool|mixed
     */
    public function getFinalCapture()
    {
        return $this->finalCapture;
    }

    /**
     * @param bool $finalCapture
     *
     * @return PayPalOrderCapture
     */
    public function setFinalCapture($finalCapture)
    {
        $this->finalCapture = (bool) $finalCapture;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string|null $createdAt
     *
     * @return PayPalOrderCapture
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param string|null $updatedAt
     *
     * @return PayPalOrderCapture
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return array|null
     */
    public function getSellerProtection()
    {
        return $this->sellerProtection;
    }

    /**
     * @param array $sellerProtection
     *
     * @return PayPalOrderCapture
     */
    public function setSellerProtection($sellerProtection)
    {
        $this->sellerProtection = $sellerProtection;

        return $this;
    }

    /**
     * @return array
     */
    public function getSellerReceivableBreakdown()
    {
        return $this->sellerReceivableBreakdown;
    }

    /**
     * @param array $sellerReceivableBreakdown
     *
     * @return PayPalOrderCapture
     */
    public function setSellerReceivableBreakdown($sellerReceivableBreakdown)
    {
        $this->sellerReceivableBreakdown = $sellerReceivableBreakdown;

        return $this;
    }
}
