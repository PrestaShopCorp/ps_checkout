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

class TrackingApiResult
{
    private $isUpdate;

    private $status;

    private $responseData;

    private $trackerId;

    private $existingTrackerId;

    public function __construct(
        bool $isUpdate,
        string $status,
        array $responseData,
        $trackerId,
        $existingTrackerId = null
    ) {
        $this->isUpdate = $isUpdate;
        $this->status = $status;
        $this->responseData = $responseData;
        $this->trackerId = $trackerId;
        $this->existingTrackerId = $existingTrackerId;
    }

    public function isUpdate(): bool
    {
        return $this->isUpdate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }

    public function getTrackerId()
    {
        return $this->trackerId;
    }

    public function getExistingTrackerId()
    {
        return $this->existingTrackerId;
    }

    public function isSuccess(): bool
    {
        return $this->status === 'success';
    }

    public function isApiError(): bool
    {
        return $this->status === 'api_error';
    }
}
