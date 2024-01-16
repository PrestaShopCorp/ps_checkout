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

namespace PrestaShop\Module\PrestashopCheckout\DTO\Orders;

class PatchPayPalOrderRequest
{
    /**
     * @var string
     */
    private $intent;
    /**
     * @var PurchaseUnitRequest[]
     */
    private $purchase_units;
    /**
     * @var PaymentSourceRequest
     */
    private $payment_source;

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
    }

    /**
     * @return PurchaseUnitRequest[]
     */
    public function getPurchaseUnits()
    {
        return $this->purchase_units;
    }

    /**
     * @param PurchaseUnitRequest[] $purchase_units
     */
    public function setPurchaseUnits(array $purchase_units)
    {
        $this->purchase_units = $purchase_units;
    }

    /**
     * @return PaymentSourceRequest
     */
    public function getPaymentSource()
    {
        return $this->payment_source;
    }

    /**
     * @param PaymentSourceRequest $payment_source
     */
    public function setPaymentSource(PaymentSourceRequest $payment_source)
    {
        $this->payment_source = $payment_source;
    }
}
