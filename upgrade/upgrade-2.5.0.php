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
    if (true === (bool) version_compare(_PS_VERSION_, '1.7', '>=')) {
        if (false === \Module::isInstalled($module)) {
            $moduleManagerBuilder = \ModuleManagerBuilder::getInstance();
            $moduleManager = $moduleManagerBuilder->build();

            return $moduleManager->install('ps_accounts');
        } else if (true === \Module::needUpgrade($module)) {
            $moduleManagerBuilder = \ModuleManagerBuilder::getInstance();
            $moduleManager = $moduleManagerBuilder->build();

            return $moduleManager->upgrade('ps_accounts');
        }
    } else {
        file_put_contents(_PS_MODULE_DIR_ . 'ps_account.zip', \Tools::addonsRequest('module', ['id_module' => '49648']));
        \Tools::ZipExtract(_PS_MODULE_DIR_ . 'ps_accounts.zip', _PS_MODULE_DIR_);
        $modulePsAccounts = \Module::getInstanceByName('ps_accounts');

        if (false === \Module::isInstalled($module)) {
            return $modulePsAccounts->install();
        } else if (\Module::initUpgradeModule($modulePsAccounts)) {
            $upgrade = $modulePsAccounts->runUpgradeModule();

            return $upgrade['success'];
        }
    }

    return true;
}
