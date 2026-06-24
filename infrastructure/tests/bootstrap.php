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

$rootDirectory = __DIR__ . '/../../../../../../';
$projectDir = __DIR__ . '/../../../../';

if (!getenv('IS_CI')) {
    define('_PS_IN_TEST_', true);
}

require_once $projectDir . 'vendor/autoload.php';

if (file_exists($rootDirectory . 'vendor/autoload.php')) {
    require_once $rootDirectory . 'vendor/autoload.php';
}

if (file_exists($rootDirectory . 'autoload.php')) {
    require_once $rootDirectory . 'autoload.php';
}

if (!defined('_PS_VERSION_') && class_exists('AppKernel')) {
    define('_PS_VERSION_', AppKernel::VERSION);
}

if (!function_exists('pSQL')) {
    function pSQL($string, $htmlOK = false)
    {
        return $string;
    }
}

if (!function_exists('bqSQL')) {
    function bqSQL($string)
    {
        return str_replace('`', '', $string);
    }
}

if (!defined('_DB_PREFIX_')) {
    define('_DB_PREFIX_', 'ps_');
}

if (!defined('_DB_SERVER_')) {
    define('_DB_SERVER_', 'localhost');
}

if (!defined('_DB_USER_')) {
    define('_DB_USER_', '');
}

if (!defined('_DB_PASSWD_')) {
    define('_DB_PASSWD_', '');
}

if (!defined('_DB_NAME_')) {
    define('_DB_NAME_', '');
}
