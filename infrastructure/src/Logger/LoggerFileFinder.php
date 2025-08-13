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

use PsCheckout\Infrastructure\Adapter\Configuration;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Utility\Common\DateUtility;
use Symfony\Component\Finder\Finder;

class LoggerFileFinder implements LoggerFileFinderInterface
{
    const LOGGER_DIRECTORY_PATH = _PS_ROOT_DIR_ . '/var/logs/';

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ContextInterface
     */
    private $context;

    public function __construct(
        string $moduleName,
        ContextInterface $context,
        Configuration $configuration
    ) {
        $this->moduleName = $moduleName;
        $this->configuration = $configuration;
        $this->context = $context;
    }

    /**
     * Get the list of log files matching the module and shop identifiers.
     *
     * @return array associative array of file names and formatted dates
     */
    public function getFiles(): array
    {
        if (!$this->isLoggerDirectoryReadable()) {
            return [];
        }

        $finder = new Finder();
        $fileNames = [];

        $fileNamePrefix = $this->generateFileNamePrefix();

        // Use Finder to search for files matching the prefix and sort by name
        foreach ($finder->files()->in(self::LOGGER_DIRECTORY_PATH)
            ->name($fileNamePrefix . '*')->sortByName() as $file) {
            $fileNames[$file->getFilename()] = $this->formatFileDate($file);
        }

        return $fileNames;
    }

    /**
     * Check if the logger directory is readable.
     *
     * @return bool
     */
    private function isLoggerDirectoryReadable(): bool
    {
        return is_readable(self::LOGGER_DIRECTORY_PATH);
    }

    /**
     * Generate the file name prefix based on module name and shop ID.
     *
     * @return string
     */
    private function generateFileNamePrefix(): string
    {
        return $this->moduleName . '-' . $this->context->getShop()->id . '-';
    }

    /**
     * Format the date of the log file using DateUtility.
     *
     * @param SplFileInfo $file
     *
     * @return string
     */
    private function formatFileDate($file): string
    {
        // Extract the raw date from the file name (after the prefix)
        $rawDate = str_replace($this->generateFileNamePrefix(), '', $file->getFilename());

        // Return the formatted date using the DateUtility class
        return DateUtility::formatDate(
            $rawDate,
            $this->context->getLanguage()->date_format_lite,
            $this->configuration->get('PS_TIMEZONE')
        );
    }
}
