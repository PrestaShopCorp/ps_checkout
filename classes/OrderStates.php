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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Translations\OrderStatesTranslations;

class OrderStates
{
    const MODULE_NAME = 'ps_checkout';
    const ORDER_STATE_TEMPLATE = 'payment';
    const ORDER_TABLE = 'orders';
    const ORDER_HISTORY_TABLE = 'order_history';
    const ORDER_STATE_TABLE = 'order_state';
    const ORDER_STATE_LANG_TABLE = 'order_state_lang';
    const DARK_BLUE_HEXA_COLOR = '#34209E';
    const BLUE_HEXA_COLOR = '#3498D8';
    const GREEN_HEXA_COLOR = '#01B887';
    const ORDER_STATES = [
        'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' => self::DARK_BLUE_HEXA_COLOR,
        'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' => self::DARK_BLUE_HEXA_COLOR,
        'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' => self::DARK_BLUE_HEXA_COLOR,
        'PS_CHECKOUT_STATE_AUTHORIZED' => self::BLUE_HEXA_COLOR,
        'PS_CHECKOUT_STATE_PARTIAL_REFUND' => self::GREEN_HEXA_COLOR,
        'PS_CHECKOUT_STATE_WAITING_CAPTURE' => self::BLUE_HEXA_COLOR,
    ];

    /**
     * Insert the new paypal states if it does not exists
     * Create a new order state for each ps_checkout new order states
     *
     * FYI: this method is also used in the upgrade-1.2.14.php file
     *
     * @return bool
     */
    public function installPaypalStates()
    {
        foreach (self::ORDER_STATES as $state => $color) {
            $orderStateId = $this->getPaypalStateId($state, $color);
            $this->createPaypalStateLangs($state, $orderStateId);
            $this->setStateIcons($state, $orderStateId);
        }

        return true;
    }

    /**
     * Get the paypal state id if it already exist.
     * Get the paypal state id if it doesn't exist by creating it
     *
     * @param string $state
     *
     * @return int
     */
    private function getPaypalStateId($state, $color)
    {
        $stateId = (int) \Configuration::getGlobalValue($state);

        // Is state ID already existing in the Configuration table ?
        if (0 === $stateId || false === \OrderState::existsInDatabase($stateId, self::ORDER_STATE_TABLE)) {
            return $this->createPaypalStateId($state, $color);
        }

        return (int) $stateId;
    }

    /**
     * Create the Paypal State id
     *
     * @param string $state
     *
     * @return int orderStateId
     */
    private function createPaypalStateId($state, $color)
    {
        $data = [
            'module_name' => self::MODULE_NAME,
            'color' => $color,
            'unremovable' => 1,
        ];

        if (true === \Db::getInstance()->insert(self::ORDER_STATE_TABLE, $data)) {
            $insertedId = (int) \Db::getInstance()->Insert_ID();
            \Configuration::updateGlobalValue($state, $insertedId);

            return $insertedId;
        }

        throw new PsCheckoutException('Not able to insert the new order state');
    }

    /**
     * Create the Paypal States Lang
     *
     * @param string $state
     * @param int $orderStateId
     */
    private function createPaypalStateLangs($state, $orderStateId)
    {
        $languagesList = \Language::getLanguages();
        $orderStatesTranslations = new OrderStatesTranslations();

        // For each languages in the shop, we insert a new order state name
        foreach ($languagesList as $key => $lang) {
            if (true === $this->stateLangAlreadyExists($orderStateId, (int) $lang['id_lang'])) {
                continue;
            }

            $statesTranslations = $orderStatesTranslations->getTranslations($lang['iso_code']);
            $this->insertNewStateLang($orderStateId, $statesTranslations[$state], (int) $lang['id_lang']);
        }
    }

    /**
     * Check if Paypal State language already exists in the table ORDEr_STATE_LANG_TABLE
     *
     * @param int $orderStateId
     * @param int $langId
     *
     * @return bool
     */
    private function stateLangAlreadyExists($orderStateId, $langId)
    {
        return (bool) \Db::getInstance()->getValue(
            'SELECT id_order_state
            FROM  `' . _DB_PREFIX_ . self::ORDER_STATE_LANG_TABLE . '`
            WHERE
                id_order_state = ' . $orderStateId . '
                AND id_lang = ' . $langId
        );
    }

    /**
     * Create the Paypal States Lang
     *
     * @param int $orderStateId
     * @param string $translations
     * @param int $langId
     */
    private function insertNewStateLang($orderStateId, $translations, $langId)
    {
        $data = [
            'id_order_state' => $orderStateId,
            'id_lang' => (int) $langId,
            'name' => pSQL($translations),
            'template' => self::ORDER_STATE_TEMPLATE,
        ];

        if (false === \Db::getInstance()->insert(self::ORDER_STATE_LANG_TABLE, $data)) {
            throw new PsCheckoutException('Not able to insert the new order state language');
        }
    }

    /**
     * Set an icon for the current State Id
     *
     * @param string $state
     * @param int $orderStateId
     *
     * @return false|void
     */
    private function setStateIcons($state, $orderStateId)
    {
        $iconExtension = '.gif';
        $iconToPaste = _PS_ORDER_STATE_IMG_DIR_ . $orderStateId . $iconExtension;

        if (true === file_exists($iconToPaste)) {
            if (true !== is_writable($iconToPaste)) {
                \PrestaShopLogger::addLog('[PSPInstall] ' . $iconToPaste . ' is not writable', 2, null, null, null, true);

                return false;
            }
        }

        if ($state === Refund::REFUND_STATE) {
            $iconName = 'refund';
        } else {
            $iconName = 'waiting';
        }

        $iconsFolderOrigin = _PS_MODULE_DIR_ . self::MODULE_NAME . '/views/img/OrderStatesIcons/';
        $iconToCopy = $iconsFolderOrigin . $iconName . $iconExtension;

        if (false === copy($iconToCopy, $iconToPaste)) {
            \PrestaShopLogger::addLog('[PSPInstall] not able to copy ' . $iconName . ' for ID ' . $orderStateId, 2, null, null, null, true);
        }
    }
}
