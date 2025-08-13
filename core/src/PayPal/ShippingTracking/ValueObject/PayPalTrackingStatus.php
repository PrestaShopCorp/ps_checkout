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

/**
 * PayPalTrackingStatus value object for PayPal tracking API
 */
class PayPalTrackingStatus
{
    /**
     * Valid tracking statuses for PayPal API
     */
    const SHIPPED = 'SHIPPED';

    const ON_HOLD = 'ON_HOLD';

    const CANCELLED = 'CANCELLED';

    const DELIVERED = 'DELIVERED';

    /**
     * Default status when none is provided
     */
    const DEFAULT_STATUS = self::SHIPPED;

    /**
     * @var string
     */
    private $status;

    /**
     * @param string $status
     * @throws \InvalidArgumentException
     */
    public function __construct(string $status)
    {
        if (!$this->isValid($status)) {
            throw new \InvalidArgumentException(
                sprintf('Invalid tracking status "%s". Valid statuses are: %s', $status, implode(', ', self::getValidStatuses()))
            );
        }
        
        $this->status = $status;
    }

    /**
     * Get the status value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->status;
    }

    /**
     * Create from string with fallback to default
     *
     * @param string|null $status
     * @return PayPalTrackingStatus
     */
    public static function createWithFallback($status): PayPalTrackingStatus
    {
        if (empty($status) || !self::isValid($status)) {
            return new self(self::DEFAULT_STATUS);
        }
        
        return new self($status);
    }

    /**
     * Check if status is valid
     *
     * @param string $status
     * @return bool
     */
    public static function isValid(string $status): bool
    {
        return in_array($status, self::getValidStatuses(), true);
    }

    /**
     * Get all valid statuses
     *
     * @return array
     */
    public static function getValidStatuses(): array
    {
        return [
            self::SHIPPED,
            self::ON_HOLD,
            self::CANCELLED,
            self::DELIVERED,
        ];
    }

    /**
     * Get default status
     *
     * @return string
     */
    public static function getDefaultStatus(): string
    {
        return self::DEFAULT_STATUS;
    }

    /**
     * String representation
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->status;
    }
}
