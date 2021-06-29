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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Removes files or directories.
 *
 * @param array $files An array of files to remove
 *
 * @return true|string True if everything goes fine, error details otherwise
 */
function removePsCheckoutPhpUnitFromFsDuringUpgrade(array $files)
{
    $files = array_reverse($files);
    foreach ($files as $file) {
        if (is_dir($file)) {
            $iterator = new FilesystemIterator($file, FilesystemIterator::CURRENT_AS_PATHNAME | FilesystemIterator::SKIP_DOTS);
            removePsCheckoutPhpUnitFromFsDuringUpgrade(iterator_to_array($iterator));
            if (!rmdir($file) && file_exists($file)) {
                return 'Deletion of directory ' . $file . 'failed';
            }
        } elseif (!unlink($file) && file_exists($file)) {
            return 'Deletion of file ' . $file . 'failed';
        }
    }

    return true;
}

/**
 * Update main function for module Version 1.2.9
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_1_2_9($module)
{
    /*
     * PHPUNIT REMOVAL
     * This step removes the folder vendor/phpunit, when added from a previous release installed on the shop.
     */
    $path = __DIR__ . '/../vendor/phpunit';
    if (file_exists($path)) {
        $result = removePsCheckoutPhpUnitFromFsDuringUpgrade([$path]);
        if ($result !== true) {
            $module->getLogger()->error('Could not delete PHPUnit from module.');

            return false;
        }
    }

    return true;
}
