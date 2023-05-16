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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Event;

use PrestaShop\Module\PrestashopCheckout\Event\Event;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Exception\PayPalOrderException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\Exception\PayPalCaptureException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Capture\ValueObject\PayPalCaptureId;

abstract class PayPalCaptureEvent extends Event
{
    /**
     * @var PayPalCaptureId
     */
    private $captureId;

    /**
     * @var PayPalOrderId
     */
    private $paypalOrderId;

    /**
     * @var array{id: string, status: string, amount: array, final_capture: bool, disbursement_mode: string, create_time: string, update_time: string, seller_protection: array, seller_receivable_breakdown: array, links: array}
     */
    private $capture;

    /**
     * @param string $captureId
     * @param string $paypalOrderId
     * @param array{id: string, status: string, amount: array, final_capture: bool, disbursement_mode: string, create_time: string, update_time: string, seller_protection: array, seller_receivable_breakdown: array, links: array} $capture
     *
     * @throws PayPalCaptureException
     * @throws PayPalOrderException
     */
    public function __construct($captureId, $paypalOrderId, array $capture)
    {
        $this->captureId = new PayPalCaptureId($captureId);
        $this->paypalOrderId = new PayPalOrderId($paypalOrderId);
        $this->capture = $capture;
    }

    /**
     * @return PayPalCaptureId
     */
    public function getPayPalCaptureId()
    {
        return $this->captureId;
    }

    /**
     * @return PayPalOrderId
     */
    public function getPayPalOrderId()
    {
        return $this->paypalOrderId;
    }

    /**
     * @return array{id: string, status: string, amount: array, final_capture: bool, disbursement_mode: string, create_time: string, update_time: string, seller_protection: array, seller_receivable_breakdown: array, links: array}
     */
    public function getCapture()
    {
        return $this->capture;
    }
}
