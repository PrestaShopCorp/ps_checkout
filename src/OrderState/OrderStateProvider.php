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

namespace PrestaShop\Module\PrestashopCheckout\OrderState;

use PrestaShop\Module\PrestashopCheckout\OrderStates;

class OrderStateProvider
{
    /**
     * @param string $moduleName
     *
     * @return array
     */
    public function createDefaultPayPalOrderStates($moduleName)
    {
        $orderStates = [];
        $iconFolder = _PS_MODULE_DIR_ . $moduleName . '/views/img/OrderStatesIcons/';
        $class = new \ReflectionClass("\PrestaShop\Module\PrestashopCheckout\Translations\OrderStatesTranslations");

        foreach (OrderStates::ORDER_STATES as $configurationKey => $color) {
            $icon = 'waiting.gif';
            if ($configurationKey === OrderStates::PS_CHECKOUT_STATE_PARTIAL_REFUND) {
                $icon = 'refund.gif';
            }
            $orderStates[] = new OrderState($configurationKey,
                $class->getConstant($configurationKey), $color, $iconFolder . $icon);
        }

        //add a specific Order State
        $authorizeOrderState = new MailOrderState(
            OrderStates::PS_CHECKOUT_STATE_AUTHORIZED,
            $class->getConstant(OrderStates::PS_CHECKOUT_STATE_AUTHORIZED),
            OrderStates::BLUE_HEXA_COLOR,
            $iconFolder . 'waiting.gif',
            'authorize'
        );

        $orderStates[] = $authorizeOrderState;

        return $orderStates;
    }
}
