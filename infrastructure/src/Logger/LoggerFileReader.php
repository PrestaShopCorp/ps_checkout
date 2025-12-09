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

namespace PsCheckout\Infrastructure\Logger;

use InvalidArgumentException;
use PsCheckout\Infrastructure\Adapter\ValidateInterface;
use RuntimeException;
use SplFileObject;

class LoggerFileReader implements LoggerFileReaderInterface
{
    /**
     * @var ValidateInterface
     */
    private $validate;

    /**
     * @var LoggerFileFinderInterface
     */
    private $loggerFileFinder;

    public function __construct(ValidateInterface $validate, LoggerFileFinderInterface $loggerFileFinder)
    {
        $this->validate = $validate;
        $this->loggerFileFinder = $loggerFileFinder;
    }

    /**
     * {@inheritdoc}
     */
    public function read(string $filename, int $offset, int $limit): array
    {
        $this->validateParams($filename, $offset, $limit);

        $logFile = new SplFileObject(LoggerFileFinder::LOGGER_DIRECTORY_PATH . $filename);

        if (false === $logFile->isFile()) {
            throw new RuntimeException('File not found');
        }

        $isEndOfFile = true;
        $totalFileNewLines = 0;
        $currentFileLineNumber = 0;
        $fileLines = [];

        if (false === $logFile->isReadable()) {
            throw new RuntimeException('File is not readable');
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
     * {@inheritdoc}
     */
    public function validateFilename(string $filename): void
    {
        if (empty($filename)
            || !$this->validate->isFileName($filename)
            || preg_match('/\.\.(\/|\\\\)/', $filename)
        ) {
            throw new InvalidArgumentException('Filename is invalid');
        }

        $files = $this->loggerFileFinder->getFiles();

        if (!array_key_exists($filename, $files)) {
            throw new InvalidArgumentException('File does not exist');
        }
    }

    private function validateParams(string $filename, int $offset, int $limit): void
    {
        $this->validateFilename($filename);

        if ($offset < 0) {
            throw new InvalidArgumentException('Offset must be a positive integer or zero');
        }

        if ($limit <= 0) {
            throw new InvalidArgumentException('Limit must be a positive integer');
        }
    }
}
