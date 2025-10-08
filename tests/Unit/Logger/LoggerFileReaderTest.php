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

namespace Tests\Unit\Logger;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PrestaShop\Module\PrestashopCheckout\Adapter\ValidateAdapter;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerDirectory;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileFinder;
use PrestaShop\Module\PrestashopCheckout\Logger\LoggerFileReader;

class LoggerFileReaderTest extends TestCase
{
    /**
     * @var LoggerFileReader
     */
    private $loggerFileReader;

    /**
     * @dataProvider badFilenameProvider
     */
    public function testValidateFilenameWithBadFilename($filename): void
    {
        $validate = $this->createMock(ValidateAdapter::class);
        $validate->expects($this->once())
            ->method('isFileName')
            ->with($filename)
            ->willReturn(true);
        $loggerDirectory = $this->createMock(LoggerDirectory::class);
        $loggerDirectory->expects($this->once())->method('getPath')->willReturn('/var/logs');
        $loggerFileFinder = $this->createMock(LoggerFileFinder::class);
        $this->loggerFileReader = new LoggerFileReader($loggerDirectory, $loggerFileFinder, $validate);
        $this->expectException(InvalidArgumentException::class);
        $this->loggerFileReader->validateFilename($filename);
    }

    /**
     * @dataProvider rightFilenameProvider
     */
    public function testValidateFilenameWithFilenameNotFound($filename): void
    {
        $validate = $this->createMock(ValidateAdapter::class);
        $validate->expects($this->once())
            ->method('isFileName')
            ->with($filename)
            ->willReturn(true);
        $loggerDirectory = $this->createMock(LoggerDirectory::class);
        $loggerDirectory->expects($this->once())->method('getPath')->willReturn('/var/logs');
        $loggerFileFinder = $this->createMock(LoggerFileFinder::class);
        $loggerFileFinder->expects($this->once())
            ->method('getLogFileNames')
            ->willReturn([]);
        $this->loggerFileReader = new LoggerFileReader($loggerDirectory, $loggerFileFinder, $validate);
        $this->expectException(InvalidArgumentException::class);
        $this->loggerFileReader->validateFilename($filename);
    }

    /**
     * @dataProvider rightFilenameProvider
     */
    public function testValidateFilenameWithRightFilename($filename): void
    {
        $validate = $this->createMock(ValidateAdapter::class);
        $validate->expects($this->once())
            ->method('isFileName')
            ->with($filename)
            ->willReturn(true);
        $loggerFileFinder = $this->createMock(LoggerFileFinder::class);
        $loggerFileFinder->expects($this->once())
            ->method('getLogFileNames')
            ->willReturn(array_fill_keys([$filename], 'test'));
        $loggerDirectory = $this->createMock(LoggerDirectory::class);
        $loggerDirectory->expects($this->once())->method('getPath')->willReturn('/var/logs');
        $this->loggerFileReader = new LoggerFileReader($loggerDirectory, $loggerFileFinder, $validate);
        $this->loggerFileReader->validateFilename($filename);
    }

    public function badFilenameProvider(): array
    {
        return [
            ['../../app/config/parameters.php'],
        ];
    }

    public function rightFilenameProvider(): array
    {
        return [
            ['log_2024-06-01'],
            ['error'],
            ['access_01'],
        ];
    }
}
