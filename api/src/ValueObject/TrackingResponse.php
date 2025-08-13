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

namespace PsCheckout\Api\ValueObject;

class TrackingResponse
{
    /**
     * @var string
     */
    private $trackerId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var array
     */
    private $links;

    /**
     * @var string
     */
    private $createTime;

    /**
     * @var string
     */
    private $updateTime;

    /**
     * Constructor to initialize TrackingResponse properties
     */
    public function __construct(
        string $trackerId,
        string $status,
        array $links,
        string $createTime = '',
        string $updateTime = ''
    ) {
        $this->trackerId = $trackerId;
        $this->status = $status;
        $this->links = $links;
        $this->createTime = $createTime;
        $this->updateTime = $updateTime;
    }

    /**
     * @return string
     */
    public function getTrackerId(): string
    {
        return $this->trackerId;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * @return string
     */
    public function getCreateTime(): string
    {
        return $this->createTime;
    }

    /**
     * @return string
     */
    public function getUpdateTime(): string
    {
        return $this->updateTime;
    }

    /**
     * Create TrackingResponse from API response data
     *
     * @param array $responseData
     *
     * @return TrackingResponse
     */
    public static function createFromResponse(array $responseData): TrackingResponse
    {
        // Handle middleware response format where tracker info is nested
        $trackerId = '';
        $trackerStatus = 'UNKNOWN';
        $trackerLinks = [];
        
        // Check if this is a middleware response with nested tracker data
        if (isset($responseData['purchase_units'][0]['shipping']['trackers'][0])) {
            $tracker = $responseData['purchase_units'][0]['shipping']['trackers'][0];
            $trackerId = $tracker['id'] ?? '';
            $trackerStatus = $tracker['status'] ?? 'UNKNOWN';
            $trackerLinks = $tracker['links'] ?? [];
        } else {
            // Fallback to direct response format (if used)
            $trackerId = $responseData['tracker_id'] ?? '';
            $trackerStatus = $responseData['status'] ?? 'UNKNOWN';
            $trackerLinks = $responseData['links'] ?? [];
        }

        return new self(
            $trackerId,
            $trackerStatus,
            $trackerLinks,
            $responseData['create_time'] ?? '',
            $responseData['update_time'] ?? ''
        );
    }

    /**
     * Get self link for this tracker
     *
     * @return string
     */
    public function getSelfLink(): string
    {
        foreach ($this->getLinks() as $link) {
            if ('self' === $link['rel']) {
                return $link['href'];
            }
        }

        return '';
    }

    /**
     * Check if tracking was successfully created
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        // Must have a tracker ID
        if (empty($this->trackerId)) {
            return false;
        }
        
        // Accept various successful status values from middleware
        $successfulStatuses = [
            'SHIPPED',      // PayPal standard
            'ON_HOLD',      // PayPal standard
            'DELIVERED',    // PayPal standard
            'COMPLETED',    // Middleware status
            'SUCCESS',      // Generic success
            'CREATED'       // Tracking created
        ];
        
        return in_array($this->status, $successfulStatuses);
    }
}
