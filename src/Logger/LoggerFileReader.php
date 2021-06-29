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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * Class responsible for reading log files.
 */
class LoggerFileReader
{
    /**
     * @param \SplFileObject $logFile
     * @param int $offset
     * @param int $limit
     *
     * @return array
     *
     * @throws PsCheckoutException
     */
    public function read(\SplFileObject $logFile, $offset, $limit)
    {
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
}
