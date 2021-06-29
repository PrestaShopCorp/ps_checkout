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
 * Update main function for module version 2.11.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_2_11_0($module)
{
    if (true === (bool) version_compare(_PS_VERSION_, '1.7.1.0', '>=')) {
        // Register hooks only for PrestaShop 1.7.1, used for payment methods logo block
        $module->registerHook('displayProductAdditionalInfo');
        $module->updatePosition(Hook::getIdByName('displayProductAdditionalInfo'), false, 1);
    }

    return true;
}
