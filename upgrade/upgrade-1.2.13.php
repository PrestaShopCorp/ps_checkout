<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Update main function for module Version 1.2.12
 *
 * @param Module $module
 *
 * @return bool
 */
function upgrade_module_1_2_12($module)
{
    foreach (PrestaShop\Module\PrestashopCheckout\OrderStates::ORDER_STATES as $state => $color) {
        $orderStateId = (int) \Configuration::get($state);
        setStateIcons($state, $orderStateId, $module->name);
    }

    return $module->registerHook('displayInvoiceLegalFreeText');
}

function setStateIcons($state, $orderStateId, $moduleName)
{
    $iconExtension = '.gif';
    $iconToPaste = _PS_ORDER_STATE_IMG_DIR_ . $orderStateId . $iconExtension;

    if (true === file_exists($iconToPaste)) {
        if (true !== is_writable($iconToPaste)) {
            \PrestaShopLogger::addLog('[PSPInstall] ' . $iconToPaste . ' is not writable', 2, null, null, null, true);

            return false;
        }
    }

    if ($state === PrestaShop\Module\PrestashopCheckout\Refund::REFUND_STATE) {
        $iconName = 'refund';
    } else {
        $iconName = 'waiting';
    }

    $iconsFolderOrigin = _PS_MODULE_DIR_ . $moduleName . '/views/img/OrderStatesIcons/';
    $iconToCopy = $iconsFolderOrigin . $iconName . $iconExtension;

    if (false === copy($iconToCopy, $iconToPaste)) {
        \PrestaShopLogger::addLog('[PSPInstall] not able to copy ' . $iconName . ' for ID ' . $orderStateId, 2, null, null, null, true);

        return false;
    }

    return true;
}
