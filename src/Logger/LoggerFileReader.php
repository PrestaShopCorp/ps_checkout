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

namespace PrestaShop\Module\PrestashopCheckout\Logger;

use InvalidArgumentException;
use PrestaShop\Module\PrestashopCheckout\Adapter\ValidateAdapter;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use SplFileObject;

/**
 * Class responsible for reading log files.
 */
class LoggerFileReader
{
    /**
     * @var LoggerDirectory
     */
    private $loggerDirectory;

    /**
     * @var LoggerFileFinder
     */
    private $loggerFileFinder;

    /**
     * @var ValidateAdapter
     */
    private $validateAdapter;

    public function __construct(LoggerDirectory $loggerDirectory, LoggerFileFinder $loggerFileFinder, ValidateAdapter $validateAdapter)
    {
        $this->loggerDirectory = $loggerDirectory;
        $this->loggerFileFinder = $loggerFileFinder;
        $this->validateAdapter = $validateAdapter;
    }

    /**
     * @param string $filename
     * @param int $offset
     * @param int $limit
     *
     * @return array
     *
     * @throws PsCheckoutException
     * @throws InvalidArgumentException
     */
    public function read($filename, $offset, $limit)
    {
        $this->validateParams($filename, $offset, $limit);

        $logFile = new SplFileObject($this->loggerDirectory->getPath() . $filename);

        if (false === $logFile->isFile()) {
            throw new PsCheckoutException('File not found', PsCheckoutException::PSCHECKOUT_LOGGER_FILE_READER_NOT_FOUND);
        }

        $isEndOfFile = true;
        $totalFileNewLines = 0;
        $currentFileLineNumber = 0;
        $fileLines = [];

        if (false === $logFile->isReadable()) {
            throw new PsCheckoutException('File is not readable', PsCheckoutException::PSCHECKOUT_LOGGER_FILE_READER_NOT_READABLE);
        }

        while ($logFile->valid()) {
            $line = $logFile->fgets();

            if ($currentFileLineNumber < $offset) {
                ++$currentFileLineNumber;
                continue;
            }

            if ($totalFileNewLines >= $limit) {
                $isEndOfFile = false;
                break;
            }

            if (false === empty($line)) {
                $fileLines[] = $line;
                ++$totalFileNewLines;
                ++$currentFileLineNumber;
            }
        }

        return [
            'filename' => $logFile->getFilename(),
            'offset' => $offset,
            'limit' => $limit,
            'currentOffset' => $offset + $totalFileNewLines,
            'eof' => $isEndOfFile,
            'lines' => $fileLines,
        ];
    }

    /**
     * @param string $filename
     * @param int $offset
     * @param int $limit
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    private function validateParams($filename, $offset, $limit)
    {
        $this->validateFilename($filename);

        if ($offset < 0) {
            throw new InvalidArgumentException('Offset must be a positive integer or zero');
        }

        if ($limit <= 0) {
            throw new InvalidArgumentException('Limit must be a positive integer');
        }
    }

    /**
     * @param string $filename
     *
     * @return void
     *
     * @throws InvalidArgumentException
     */
    public function validateFilename($filename)
    {
        if (empty($filename)
            || !$this->validateAdapter->isFileName($filename)
            || preg_match('/\.\.(\/|\\\\)/', $filename)
        ) {
            throw new InvalidArgumentException('Filename is invalid');
        }

        $files = $this->loggerFileFinder->getLogFileNames();

        if (!array_key_exists($filename, $files)) {
            throw new InvalidArgumentException('File does not exist');
        }
    }
}
