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

namespace PsCheckout\Core\PayPal\ShippingTracking\ValueObject;

class TrackingStatus
{
    // Database tracking statuses
    public const PENDING = 'PENDING';

    public const SENT = 'SENT';

    public const FAILED = 'FAILED';
    
    public const UPDATE_FAILED = 'UPDATE_FAILED'; // Update operation failed
    
    public const UPDATE_SENT = 'UPDATE_SENT'; // Update operation succeeded

    /**
     * @var string
     */
    private $status;

    /**
     * Constructor
     *
     * @param string $status
     */
    public function __construct($status)
    {
        if (!$this->isValidStatus($status)) {
            throw new \InvalidArgumentException("Invalid tracking status: {$status}");
        }

        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->status;
    }

    /**
     * Check if the status is valid
     *
     * @param string $status
     *
     * @return bool
     */
    private function isValidStatus($status)
    {
        return in_array($status, $this->getAllStatuses(), true);
    }

    /**
     * Get all valid statuses
     *
     * @return array
     */
    public function getAllStatuses()
    {
        return [
            self::PENDING,
            self::SENT,
            self::FAILED,
            self::UPDATE_FAILED,
            self::UPDATE_SENT,
        ];
    }

    /**
     * Get database-specific statuses
     *
     * @return array
     */
    public static function getDatabaseStatuses()
    {
        return [
            self::PENDING,
            self::SENT,
            self::FAILED,
            self::UPDATE_FAILED,
            self::UPDATE_SENT,
        ];
    }

    /**
     * Check if status indicates success
     *
     * @return bool
     */
    public function isSuccessful()
    {
        return in_array($this->status, [self::SENT, self::UPDATE_SENT], true);
    }

    /**
     * Check if status indicates failure
     *
     * @return bool
     */
    public function isFailed()
    {
        return in_array($this->status, [self::FAILED, self::UPDATE_FAILED], true);
    }

    /**
     * Check if status is pending
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status === self::PENDING;
    }

    /**
     * Factory method for creating PENDING status
     *
     * @return TrackingStatus
     */
    public static function pending()
    {
        return new self(self::PENDING);
    }

    /**
     * Factory method for creating SENT status
     *
     * @return TrackingStatus
     */
    public static function sent()
    {
        return new self(self::SENT);
    }

    /**
     * Factory method for creating FAILED status
     *
     * @return TrackingStatus
     */
    public static function failed()
    {
        return new self(self::FAILED);
    }

    /**
     * Factory method for creating UPDATE_FAILED status
     *
     * @return TrackingStatus
     */
    public static function updateFailed()
    {
        return new self(self::UPDATE_FAILED);
    }

    /**
     * Factory method for creating UPDATE_SENT status
     *
     * @return TrackingStatus
     */
    public static function updateSent()
    {
        return new self(self::UPDATE_SENT);
    }
}
