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

use PrestaShop\Module\PrestashopCheckout\Presenter\Date\DatePresenter;
use Symfony\Component\Finder\Finder;

/**
 * Class responsible for finding log files.
 */
class LoggerFileFinder
{
    /**
     * @var LoggerDirectory
     */
    private $loggerDirectory;

    /**
     * @var LoggerFilename
     */
    private $loggerFilename;

    /**
     * @param LoggerDirectory $loggerDirectory
     * @param LoggerFilename $loggerFilename
     */
    public function __construct($loggerDirectory, LoggerFilename $loggerFilename)
    {
        $this->loggerDirectory = $loggerDirectory;
        $this->loggerFilename = $loggerFilename;
    }

    /**
     * @return array|string[]
     */
    public function getLogFileNames()
    {
        if (!$this->loggerDirectory->isReadable()) {
            return [];
        }

        $finder = new Finder();
        $fileNames = [];
        $fileNamePrefix = $this->loggerFilename->get() . '-';

        foreach ($finder->files()->in($this->loggerDirectory->getPath())->name($fileNamePrefix . '*')->sortByName() as $file) {
            $fileNames[$file->getFilename()] = (new DatePresenter(str_replace($fileNamePrefix, '', $file->getFilename()), \Context::getContext()->language->date_format_lite))->present();
        }

        return $fileNames;
    }
}
