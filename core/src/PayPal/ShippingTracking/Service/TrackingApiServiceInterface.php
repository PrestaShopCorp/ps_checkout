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

namespace PsCheckout\Core\PayPal\ShippingTracking\Service;

use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingApiRequest;
use PsCheckout\Core\PayPal\ShippingTracking\ValueObject\TrackingRecord;

interface TrackingApiServiceInterface
{
    /**
     * Process tracking API call (ADD or UPDATE)
     *
     * @param TrackingRecord|null $existingTracking
     * @param array $payload
     * @param string $payPalOrderId
     * @param int $orderId
     * @param bool $throwOnError
     * @return TrackingApiResult
     * @throws \Exception
     */
    public function processTracking(TrackingApiRequest $request): TrackingApiResult;
}
