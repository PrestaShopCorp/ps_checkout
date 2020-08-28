<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace Tests\Unit\Logger;

use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerDirectory;

class LoggerDirectoryTest extends TestCase
{
    public function testItIsReturnValidPathForVersionLessThan17()
    {
        $cacheDirectory = new LoggerDirectory('1.6.1.0', __DIR__);

        $this->assertSame(__DIR__ . '/log/', $cacheDirectory->getPath());
    }

    public function testItIsReturnValidPathForVersionLessThan174()
    {
        $cacheDirectory = new LoggerDirectory('1.7.0.0', __DIR__);

        $this->assertSame(__DIR__ . '/app/logs/', $cacheDirectory->getPath());
    }

    public function testItIsReturnValidPathForVersionGreaterThanEq174()
    {
        $cacheDirectory = new LoggerDirectory('1.7.4.0', __DIR__);

        $this->assertSame(__DIR__ . '/var/logs/', $cacheDirectory->getPath());
    }
}
