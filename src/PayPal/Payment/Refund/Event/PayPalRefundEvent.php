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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Event;

use PrestaShop\Module\PrestashopCheckout\Event\Event;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;

abstract class PayPalRefundEvent extends Event
{
    /**
     * @var string
     */
    private $refundId;

    /**
     * @var PayPalOrderId
     */
    private $paypalOrderId;

    /**
     * @var array{id: string, status: string, amount: array, create_time: string, update_time: string, custom_id: string, note_to_payer: string, payer: array, seller_payable_breakdown: array, links: array}
     */
    private $refund;

    /**
     * @param string $refundId
     * @param string $paypalOrderId
     * @param array{id: string, status: string, amount: array, create_time: string, update_time: string, custom_id: string, note_to_payer: string, payer: array, seller_payable_breakdown: array, links: array} $refund
     *
     * @throws PayPalOrderException
     */
    public function __construct($refundId, $paypalOrderId, array $refund)
    {
        $this->refundId = $refundId;
        $this->paypalOrderId = new PayPalOrderId($paypalOrderId);
        $this->refund = $refund;
    }

    /**
     * @return string
     */
    public function getPayPalRefundId()
    {
        return $this->refundId;
    }

    /**
     * @return PayPalOrderId
     */
    public function getPayPalOrderId()
    {
        return $this->paypalOrderId;
    }

    /**
     * @return array{id: string, status: string, amount: array, create_time: string, update_time: string, custom_id: string, note_to_payer: string, payer: array, seller_payable_breakdown: array, links: array}
     */
    public function getRefund()
    {
        return $this->refund;
    }
}
