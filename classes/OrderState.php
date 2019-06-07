<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Translations\OrderStatesTranslations;

class OrderStates
{
    const MODULE_NAME = 'ps_checkout';
    const ORDER_STATE_TEMPLATE = 'payment';
    const ORDER_STATE_TABLE = 'order_state';
    const ORDER_STATE_LANG_TABLE = 'order_state_lang';
    const BLUE_HEXA_COLOR = '#4169E1';
    const ORDER_STATES = array(
        'STATE_WAITING_PAYPAL_PAYMENT',
        'STATE_WAITING_CREDIT_CARD_PAYMENT',
        'STATE_WAITING_LOCAL_PAYMENT',
        'STATE_AUTHORIZED',
    );

    /**
     * Insert the new paypal states if it does not exists
     *
     * @return bool
     */
    public function installPaypalStates()
    {
        if (count(self::ORDER_STATES) === $this->paypalStatesExist()) {
            return true;
        }

        $languagesList = \Language::getLanguages();
        $orderStatesTranslations = new OrderStatesTranslations();

        // We create a new order state for each ps_checkout new order states
        foreach (self::ORDER_STATES as $state) {
            $orderStateId = $this->createPaypalStateId();

            // For each languages in the shop, we insert a new order state name
            foreach ($languagesList as $key => $lang) {
                $statesTranslations = $orderStatesTranslations->getTranslations($lang['iso_code']);
                $addTranslations = $this->createPaypalStateLangs($orderStateId, $statesTranslations[$state], $lang['id_lang']);
            }
        }

        return true;
    }

    /**
     * Create the Paypal States id
     *
     * @return int orderStateId
     */
    private function createPaypalStateId()
    {
        $data = array(
            'module_name' => self::MODULE_NAME,
            'color' => self::BLUE_HEXA_COLOR,
            'unremovable' => 1,
        );

        if (true === \Db::getInstance()->insert(self::ORDER_STATE_TABLE, $data)) {
            return (int) \Db::getInstance()->Insert_ID();
        }

        throw new \PrestaShopException('Not able to insert the new order state');
    }

    /**
     * Create the Paypal States Lang
     *
     * @param int $orderStateId
     * @param string $translations
     * @param int $langId
     *
     * @return bool
     */
    private function createPaypalStateLangs(int $orderStateId, string $translations, int $langId)
    {
        $data = array(
            'id_order_state' => $orderStateId,
            'id_lang' => (int) $langId,
            'name' => pSQL($translations),
            'template' => self::ORDER_STATE_TEMPLATE,
        );

        if (false === \Db::getInstance()->insert(self::ORDER_STATE_LANG_TABLE, $data)) {
            throw new \PrestaShopException('Not able to insert the new order state language');
        }

        return true;
    }

    /**
     * Check if the Paypal states already exist
     *
     * @return int
     */
    private function paypalStatesExist()
    {
        $statesAlreadyExist = \Db::getInstance()->getValue(
            'SELECT COUNT(id_order_state)
            FROM `' . _DB_PREFIX_ . 'order_state`
            WHERE module_name = "' . self::MODULE_NAME . '"'
        );

        return (int) $statesAlreadyExist;
    }
}
