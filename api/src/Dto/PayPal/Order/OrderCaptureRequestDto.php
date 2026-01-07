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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\OrderCaptureRequestPaymentSource;

/**
 * Completes an capture payment for an order.
 */
class OrderCaptureRequestDto
{
    /**
     * @var OrderCaptureRequestPaymentSource|null
     */
    private $paymentSource;

    /**
     * Returns Payment Source.
     * The payment source definition.
     */
    public function getPaymentSource(): ?OrderCaptureRequestPaymentSource
    {
        return $this->paymentSource;
    }

    /**
     * Sets Payment Source.
     * The payment source definition.
     *
     * @maps payment_source
     * @return self
     */
    public function setPaymentSource(?OrderCaptureRequestPaymentSource $paymentSource): self
    {
        $this->paymentSource = $paymentSource;

        return $this;
    }
}
