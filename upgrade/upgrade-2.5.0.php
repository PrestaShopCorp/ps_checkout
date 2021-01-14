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
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module version 2.5.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_2_5_0($module)
{
    $is17 = (bool) version_compare(_PS_VERSION_, '1.7', '>=');

    if (false === \Module::isInstalled('ps_accounts')) {
        return $is17 ? installPsAccountIfIsShop1_7() : installPsAccountIfIsShop1_6();
    }

    return $is17 ? upgradePsAccountIfIsShop1_7() : upgradePsAccountIfIsShop1_6();
}

/**
 * Install module if shop version is 1.7
 *
 * @return bool
 */
function installPsAccountIfIsShop1_7()
{
    $moduleManagerBuilder = \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
    $moduleManager = $moduleManagerBuilder->build();

    return $moduleManager->install('ps_accounts');
}

/**
 * Install module if shop version is 1.6
 *
 * @return bool
 */
function installPsAccountIfIsShop1_6()
{
    if (!downloadModuleIfIsShop1_6()) {
        return false;
    }

    $modulePsAccounts = \Module::getInstanceByName('ps_accounts');

    if (!$modulePsAccounts) {
        \PrestaShopLogger::addLog('Unable to get ps_accounts instance to proceed install');

        return false;
    }

    return $modulePsAccounts->install();
}

/**
 * Update module if shop version is 1.7
 *
 * @return bool
 */
function upgradePsAccountIfIsShop1_7()
{
    $modulePsAccounts = \Module::getInstanceByName('ps_accounts');

    if (true === \Module::needUpgrade($modulePsAccounts)) {
        $moduleManagerBuilder = \PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder::getInstance();
        $moduleManager = $moduleManagerBuilder->build();

        return $moduleManager->upgrade('ps_accounts');
    }

    return true;
}

/**
 * Update module if shop version is 1.6
 *
 * @return bool
 */
function upgradePsAccountIfIsShop1_6()
{
    if (!downloadModuleIfIsShop1_6()) {
        return false;
    }

    $modulePsAccounts = \Module::getInstanceByName('ps_accounts');

    if (!$modulePsAccounts) {
        \PrestaShopLogger::addLog('Unable to get ps_accounts instance to proceed upgrade');

        return false;
    }

    if (\Module::initUpgradeModule($modulePsAccounts)) {
        $upgrade = $modulePsAccounts->runUpgradeModule();

        if (!$upgrade['success']) {
            \PrestaShopLogger::addLog('Unable to upgrade ps_accounts');
        }

        return (bool) $upgrade['success'];
    }

    return true;
}

/**
 * Download Module ps_accounts if shop version is 1.6
 *
 * @return bool
 */
function downloadModuleIfIsShop1_6()
{
    $content = \Tools::addonsRequest('module', ['id_module' => '49648']);

    if (!$content) {
        \PrestaShopLogger::addLog('Unable to download ps_accounts ZIP');

        return false;
    }

    if (!file_put_contents(_PS_MODULE_DIR_ . 'ps_account.zip', $content)) {
        \PrestaShopLogger::addLog('Unable to write ps_accounts ZIP into modules folder');

        return false;
    }

    if (!\Tools::ZipExtract(_PS_MODULE_DIR_ . 'ps_accounts.zip', _PS_MODULE_DIR_)) {
        \PrestaShopLogger::addLog('Unable to extract ps_accounts ZIP into modules folder');

        return false;
    }

    return true;
}
