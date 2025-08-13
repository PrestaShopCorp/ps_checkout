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

namespace Unit\Common;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PsCheckout\Utility\Common\DateUtility;

final class DateUtilityTest extends TestCase
{
    public function testValidTimestampWithDefaultFormat()
    {
        // Valid timestamp
        $timestamp = '2025-01-17 15:30:00';
        $expected = '2025-01-17 15:30:00';

        $result = DateUtility::formatDate($timestamp);

        $this->assertEquals($expected, $result);
    }

    public function testValidTimestampWithCustomFormat()
    {
        // Valid timestamp with a custom format
        $timestamp = '2025-01-17 15:30:00';
        $expected = '17-01-2025 15:30';

        $result = DateUtility::formatDate($timestamp, 'd-m-Y H:i');

        $this->assertEquals($expected, $result);
    }

    public function testInvalidTimestamp()
    {
        // Invalid timestamp
        $timestamp = 'invalid-timestamp';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid timestamp provided: $timestamp");

        DateUtility::formatDate($timestamp);
    }

    public function testInvalidTimezone()
    {
        // Valid timestamp but invalid timezone
        $timestamp = '2025-01-17 15:30:00';
        $timezone = 'Invalid/Timezone';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Could not create a DateTime object from timestamp: $timestamp");

        DateUtility::formatDate($timestamp, 'Y-m-d H:i:s', $timezone);
    }

    public function testTimestampWithNoTimeZone()
    {
        // Valid timestamp with no timezone (should default to UTC)
        $timestamp = '2025-01-17 15:30:00';
        $expected = '2025-01-17 15:30:00'; // Assuming the default timezone is UTC

        $result = DateUtility::formatDate($timestamp, 'Y-m-d H:i:s');

        $this->assertEquals($expected, $result);
    }

    public function testTimestampWithDifferentDateFormat()
    {
        // Test with a different date format
        $timestamp = '2025-01-17 15:30:00';
        $expected = '2025/01/17';

        $result = DateUtility::formatDate($timestamp, 'Y/m/d');

        $this->assertEquals($expected, $result);
    }
}
