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
use PrestaShop\Module\PrestashopCheckout\Translations\OrderStatesTranslations;

class OrderStateProvider
{
    const ORDER_STATE_ICON_FOLDER = '/views/img/OrderStatesIcons/';

    /**
     * @var string
     */
    private $moduleName;

    /**
     * @param string $moduleName
     */
    public function __construct($moduleName)
    {
        $this->moduleName = $moduleName;
    }

    /**
     * @param string $moduleName
     *
     * @return OrderState[]
     */
    public function createDefaultPayPalOrderStates()
    {
        $orderStates = [];
        $iconFolder = _PS_MODULE_DIR_ . $this->moduleName . self::ORDER_STATE_ICON_FOLDER;
        $class = new \ReflectionClass("\PrestaShop\Module\PrestashopCheckout\Translations\OrderStatesTranslations");

        foreach (OrderStates::ORDER_STATES as $configurationKey => $color) {
            $icon = 'waiting.gif';
            if ($configurationKey === OrderStates::PS_CHECKOUT_STATE_PARTIAL_REFUND) {
                $icon = 'refund.gif';
            }
            $orderStates[] = new OrderState($configurationKey,
                $class->getConstant($configurationKey), $color, $iconFolder . $icon);
        }

        $orderStates[] = $this->createAuthorizeOrderState();

        return $orderStates;
    }

    /**
     * @return MailOrderState
     */
    public function createAuthorizeOrderState()
    {
        return new MailOrderState(
            OrderStates::PS_CHECKOUT_STATE_AUTHORIZED,
            OrderStatesTranslations::PS_CHECKOUT_STATE_AUTHORIZED,
            OrderStates::BLUE_HEXA_COLOR,
            $this->getIconFolder() . 'waiting.gif',
            'authorize'
        );
    }

    /**
     * @return string
     */
    private function getIconFolder()
    {
        return _PS_MODULE_DIR_ . $this->moduleName . self::ORDER_STATE_ICON_FOLDER;
    }
}
