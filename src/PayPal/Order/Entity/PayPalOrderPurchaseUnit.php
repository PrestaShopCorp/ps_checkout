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

class PayPalOrderPurchaseUnit
{
    const TABLE = 'pscheckout_purchase_unit';

    /**
     * @var string|null
     */
    private $idOrder;
    /**
     * @var string|null
     */
    private $checksum;
    /**
     * @var string|null
     */
    private $referenceId;
    /**
     * @var array
     */
    private $items;

    public function __construct($idOrder = null, $checksum = null, $referenceId = null, $items = [])
    {
        $this->idOrder = $idOrder;
        $this->checksum = $checksum;
        $this->referenceId = $referenceId;
        $this->items = $items;
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
     * @return PayPalOrderPurchaseUnit
     */
    public function setIdOrder($idOrder)
    {
        $this->idOrder = $idOrder;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getChecksum()
    {
        return $this->checksum;
    }

    /**
     * @param string|null $checksum
     *
     * @return PayPalOrderPurchaseUnit
     */
    public function setChecksum($checksum)
    {
        $this->checksum = $checksum;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * @param string|null $referenceId
     *
     * @return PayPalOrderPurchaseUnit
     */
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param array $items
     *
     * @return PayPalOrderPurchaseUnit
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }
}
