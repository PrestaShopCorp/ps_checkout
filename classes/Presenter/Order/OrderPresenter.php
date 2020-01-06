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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Order;

use PrestaShop\Module\PrestashopCheckout\OrderStates;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Translations\OrderStatesTranslations;

/**
 * Present the order for the reporting
 */
class OrderPresenter implements PresenterInterface
{
    public function present()
    {
        $orders = $this->getOrders();
        $link = new \Link();
        $context = \Context::getContext();

        $orderStates = [];
        $orderTranslations = new OrderStatesTranslations();
        $orderTranslations = $orderTranslations->getTranslations($context->language->iso_code);
        foreach (OrderStates::ORDER_STATES as $key => $value) {
            $idState = \Configuration::get($key);
            $orderStates[$idState]['color'] = $value;
            $orderStates[$idState]['name'] = $orderTranslations[$key];
        }

        foreach ($orders as &$order) {
            $order['username'] = $this->getUsername($order['id_customer']);
            $order['userProfileLink'] = \Tools::getShopDomainSsl(true) . $link->getAdminLink('AdminCustomers', $order['id_customer']);
            $order['state'] = $orderStates[$order['current_state']];
            $order['total_paid'] = \Tools::displayPrice($order['total_paid']);
        }

        return $orders;
    }

    /**
     * presentPendingOrders
     *
     * @return array
     */
    public function presentPendingOrders()
    {
        $link = new \Link();
        $context = \Context::getContext();

        $orderStates = [];
        $orderTranslations = new OrderStatesTranslations();
        $orderTranslations = $orderTranslations->getTranslations($context->language->iso_code);
        foreach (OrderStates::ORDER_STATES as $key => $value) {
            if ($key == 'PS_CHECKOUT_STATE_WAITING_PAYPAL_PAYMENT' ||
                $key == 'PS_CHECKOUT_STATE_WAITING_CREDIT_CARD_PAYMENT' ||
                $key == 'PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT' ||
                $key == 'PS_CHECKOUT_STATE_WAITING_CAPTURE'
            ) {
                $idState = \Configuration::get($key);
                $orderStates[$idState]['color'] = $value;
                $orderStates[$idState]['name'] = $orderTranslations[$key];
            }
        }

        $orders = $this->getPendingOrders($orderStates);

        foreach ($orders as &$order) {
            $order['username'] = $this->getUsername($order['id_customer']);
            $order['userProfileLink'] = \Tools::getShopDomainSsl(true) . $link->getAdminLink('AdminCustomers', $order['id_customer']);
            $order['state'] = $orderStates[$order['current_state']];
            $order['before_commission'] = \Tools::displayPrice($order['total_paid']);
            // TODO: Waiting for paypal infos (reporting lot 2)
            $order['commission'] = '-';
            $order['total_paid'] = '-';
        }

        return $orders;
    }

    private function getUsername($userID)
    {
        $sql = 'SELECT firstname,lastname FROM `' . _DB_PREFIX_ . 'customer` o WHERE id_customer = ' . $userID;
        $user = \Db::getInstance()->getRow($sql);

        return substr($user['firstname'], 0, 1) . '. ' . $user['lastname'];
    }

    /**
     * get last 500 checkout orders
     *
     * @return mixed
     */
    private function getOrders()
    {
        $sql = 'SELECT id_order, current_state, total_paid, date_add, id_customer
            FROM `' . _DB_PREFIX_ . 'orders` o
            WHERE o.module = "ps_checkout"
            ORDER BY date_add
            LIMIT 500
        ';
        $result = \Db::getInstance()->executeS($sql);

        return $result;
    }

    /**
     * get last 500 checkout orders
     *
     * @param array $idStates
     *
     * @return mixed
     */
    private function getPendingOrders($idStates)
    {
        $sql = 'SELECT id_order, current_state, total_paid, date_add, id_customer
            FROM `' . _DB_PREFIX_ . 'orders` o
            WHERE o.module = "ps_checkout"
            AND current_state IN (' . implode(',', array_keys($idStates)) . ')
            ORDER BY date_add
        ';
        $result = \Db::getInstance()->executeS($sql);

        return $result;
    }
}
