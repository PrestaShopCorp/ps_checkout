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

namespace Tests\Unit\PsCheckout\Infrastructure\Logger;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use PsCheckout\Infrastructure\Logger\LoggerFileFinderInterface;
use PsCheckout\Infrastructure\Logger\LoggerFileReader;
use PsCheckout\Infrastructure\Logger\LoggerFileReaderInterface;

class LoggerFileReaderTest extends TestCase
{
    /**
     * @var LoggerFileReaderInterface
     */
    private $loggerFileReader;

    /**
     * @dataProvider badFilenameProvider
     */
    public function testValidateFilenameWithBadFilename($filename): void
    {
        $validate = $this->createMock(ValidateInterface::class);
        $validate->expects($this->once())
            ->method('isFileName')
            ->with($filename)
            ->willReturn(true);
        $loggerFileFinder = $this->createMock(LoggerFileFinderInterface::class);
        $this->loggerFileReader = new LoggerFileReader($validate, $loggerFileFinder);
        $this->expectException(InvalidArgumentException::class);
        $this->loggerFileReader->validateFilename($filename);
    }

    /**
     * @dataProvider rightFilenameProvider
     */
    public function testValidateFilenameWithFilenameNotFound($filename): void
    {
        $validate = $this->createMock(ValidateInterface::class);
        $validate->expects($this->once())
            ->method('isFileName')
            ->with($filename)
            ->willReturn(true);
        $loggerFileFinder = $this->createMock(LoggerFileFinderInterface::class);
        $loggerFileFinder->expects($this->once())
            ->method('getFiles')
            ->willReturn([]);
        $this->loggerFileReader = new LoggerFileReader($validate, $loggerFileFinder);
        $this->expectException(InvalidArgumentException::class);
        $this->loggerFileReader->validateFilename($filename);
    }

    /**
     * @dataProvider rightFilenameProvider
     */
    public function testValidateFilenameWithRightFilename($filename): void
    {
        $validate = $this->createMock(ValidateInterface::class);
        $validate->expects($this->once())
            ->method('isFileName')
            ->with($filename)
            ->willReturn(true);
        $loggerFileFinder = $this->createMock(LoggerFileFinderInterface::class);
        $loggerFileFinder->expects($this->once())
            ->method('getFiles')
            ->willReturn(array_fill_keys([$filename], 'test'));
        $this->loggerFileReader = new LoggerFileReader($validate, $loggerFileFinder);
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
