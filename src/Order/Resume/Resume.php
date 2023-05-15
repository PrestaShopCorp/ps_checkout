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

namespace PrestaShop\Module\PrestashopCheckout\Order\Resume;

class Resume
{
    /**
     * @var ResumeCart
     */
    private $cart;

    /**
     * @var ResumeOrder
     */
    private $order;

    /**
     * @var ResumePayPalOrder
     */
    private $paypalOrder;

    /**
     * @var ResumePayPalCapture
     */
    private $paypalCapture;

    /**
     * @var ResumePayPalRefund
     */
    private $paypalRefund;

    /**
     * @var ResumePayPalAuthorization
     */
    private $paypalAuthorization;

    /**
     * @param ResumeCart $cart
     * @param ResumeOrder $order
     * @param ResumePayPalOrder $paypalOrder
     * @param ResumePayPalCapture $paypalCapture
     * @param ResumePayPalRefund $paypalRefund
     * @param ResumePayPalAuthorization $paypalAuthorization
     */
    public function __construct(ResumeCart $cart, ResumeOrder $order, ResumePayPalOrder $paypalOrder)
    {
        $this->cart = $cart;
        $this->order = $order;
        $this->paypalOrder = $paypalOrder;
    }

    /**
     * @return ResumeCart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * @return ResumeOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @return ResumePayPalOrder
     */
    public function getPaypalOrder()
    {
        return $this->paypalOrder;
    }

    /**
     * @return ResumePayPalCapture
     */
    public function getPaypalCapture()
    {
        return $this->paypalCapture;
    }

    /**
     * @return ResumePayPalRefund
     */
    public function getPaypalRefund()
    {
        return $this->paypalRefund;
    }

    /**
     * @return ResumePayPalAuthorization
     */
    public function getPaypalAuthorization()
    {
        return $this->paypalAuthorization;
    }

    /**
     * @param ResumePayPalCapture $paypalCapture
     */
    public function setPaypalCapture($paypalCapture)
    {
        $this->paypalCapture = $paypalCapture;
    }

    /**
     * @param ResumePayPalRefund $paypalRefund
     */
    public function setPaypalRefund($paypalRefund)
    {
        $this->paypalRefund = $paypalRefund;
    }

    /**
     * @param ResumePayPalAuthorization $paypalAuthorization
     */
    public function setPaypalAuthorization($paypalAuthorization)
    {
        $this->paypalAuthorization = $paypalAuthorization;
    }
}
