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
 * Update main function for module version 8.5.1.0
 *
 * @param Ps_checkout $module
 *
 * @return bool
 */
function upgrade_module_8_5_1_0(Ps_checkout $module)
{
    try {
        $db = Db::getInstance();

        $savedShopContext = Shop::getContext();
        $savedShopId = Shop::getContextShopID();
        $savedGroupShopId = Shop::getContextShopGroupID();
        Shop::setContext(Shop::CONTEXT_ALL);
        $shopsList = \Shop::getShops(false, null, true);

        foreach ($shopsList as $shopId) {
            $configuration = json_encode([
                'cart' => [
                    'placement' => 'cart',
                    'status' => (bool) \Configuration::get(
                        'PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BANNER',
                        null,
                        null,
                        $shopId
                    ) ? 'enabled' : 'disabled',
                    'layout' => 'text',
                    'logo-type' => 'inline',
                    'text-color' => 'black',
                    'text-size' => '12',
                ],
                'category' => [
                    'placement' => 'category',
                    'status' => (bool) \Configuration::get(
                        'PS_CHECKOUT_PAY_LATER_CATEGORY_PAGE_BANNER',
                        null,
                        null,
                        $shopId
                    ) ? 'enabled' : 'disabled',
                    'color' => 'white',
                    'layout' => 'flex',
                    'ratio' => '8x1',
                ],
                'checkout' => [
                    'placement' => 'checkout',
                    'status' => (bool) \Configuration::get(
                        'PS_CHECKOUT_PAY_LATER_ORDER_PAGE_BANNER',
                        null,
                        null,
                        $shopId
                    ) ? 'enabled' : 'disabled',
                    'layout' => 'text',
                    'logo-type' => 'inline',
                    'text-color' => 'black',
                    'text-size' => '12',
                ],
                'homepage' => [
                    'placement' => 'homepage',
                    'status' => (bool) \Configuration::get(
                        'PS_CHECKOUT_PAY_LATER_HOME_PAGE_BANNER',
                        null,
                        null,
                        $shopId
                    ) ? 'enabled' : 'disabled',
                    'color' => 'white',
                    'layout' => 'flex',
                    'ratio' => '8x1',
                ],
                'product' => [
                    'placement' => 'product',
                    'status' => (bool) \Configuration::get(
                        'PS_CHECKOUT_PAY_LATER_PRODUCT_PAGE',
                        null,
                        null,
                        $shopId
                    ) ? 'enabled' : 'disabled',
                    'layout' => 'text',
                    'logo-type' => 'inline',
                    'text-color' => 'black',
                    'text-size' => '12',
                ],
            ]);

            Configuration::updateValue('PS_CHECKOUT_PAY_LATER_CONFIG', $configuration, false, null, (int) $shopId);

            foreach (['venmo', 'pay_upon_invoice'] as $paymentOption) {
                $maxPosition = (int) $db->getValue(
                    '
                SELECT MAX(position)
                FROM `' . _DB_PREFIX_ . 'pscheckout_funding_source`
                WHERE `id_shop` = ' . (int) $shopId
                );

                $paymentExists = $db->getValue(
                    '
                SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'pscheckout_funding_source`
                WHERE `name` = "' . pSQL($paymentOption) . '" AND `id_shop` = ' . (int) $shopId
                );

                if (!$paymentExists) {
                    $db->insert('pscheckout_funding_source', [
                        'name' => pSQL($paymentOption),
                        'active' => 0,
                        'position' => $maxPosition + 1,
                        'id_shop' => (int) $shopId,
                    ]);
                }
            }
        }

        // Restore initial PrestaShop shop context
        if (Shop::CONTEXT_SHOP === $savedShopContext) {
            Shop::setContext($savedShopContext, $savedShopId);
        } elseif (Shop::CONTEXT_GROUP === $savedShopContext) {
            Shop::setContext($savedShopContext, $savedGroupShopId);
        } else {
            Shop::setContext($savedShopContext);
        }
    } catch (Exception $exception) {
        PrestaShopLogger::addLog($exception->getMessage(), 4, 1, 'Module', $module->id);

        return false;
    }

    return true;
}
