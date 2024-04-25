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

class PayPalOrderAuthorization
{
    const TABLE = 'pscheckout_authorization';

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
    private $expirationTime;
    /**
     * @var array
     */
    private $sellerProtection;

    public function __construct($id = null, $idOrder = null, $status = null, $expirationTime = null, $sellerProtection = [])
    {
        $this->id = $id;
        $this->idOrder = $idOrder;
        $this->status = $status;
        $this->expirationTime = $expirationTime;
        $this->sellerProtection = $sellerProtection;
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
     * @return PayPalOrderAuthorization
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
     * @return PayPalOrderAuthorization
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
     * @return PayPalOrderAuthorization
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getExpirationTime()
    {
        return $this->expirationTime;
    }

    /**
     * @param string|null $expirationTime
     *
     * @return PayPalOrderAuthorization
     */
    public function setExpirationTime($expirationTime)
    {
        $this->expirationTime = $expirationTime;

        return $this;
    }

    /**
     * @return array
     */
    public function getSellerProtection()
    {
        return $this->sellerProtection;
    }

    /**
     * @param array $sellerProtection
     *
     * @return PayPalOrderAuthorization
     */
    public function setSellerProtection($sellerProtection)
    {
        $this->sellerProtection = $sellerProtection;

        return $this;
    }
}
