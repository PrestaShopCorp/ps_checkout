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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Order;

use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\OrderStates;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Translations\OrderStatesTranslations;

/**
 * Present the pending orders for the reporting
 */
class OrderPendingPresenter implements PresenterInterface
{
    /**
     * present pending orders
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    public function present()
    {
        $link = new LinkAdapter();
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
                $idState = (int) \Configuration::getGlobalValue($key);
                $orderStates[$idState]['color'] = $value;
                $orderStates[$idState]['name'] = $orderTranslations[$key];
            }
        }

        $orders = $this->getPendingOrders($orderStates);

        foreach ($orders as &$order) {
            $order['username'] = substr($order['firstname'], 0, 1) . '. ' . $order['lastname'];
            $order['userProfileLink'] = $link->getAdminLink('AdminCustomers', true, [], ['id_customer' => $order['id_customer'], 'viewcustomer' => 1]);
            $order['orderLink'] = $link->getAdminLink('AdminOrders', true, [], ['id_order' => $order['id_order'], 'vieworder' => 1]);
            $order['state'] = $orderStates[$order['current_state']];
            $order['before_commission'] = \Tools::displayPrice($order['total_paid']);
            // TODO: Waiting for paypal infos (reporting lot 2)
            $order['commission'] = '-';
            $order['total_paid'] = '-';
        }

        return $orders;
    }

    /**
     * get last 1000 pending checkout orders
     *
     * @param array $idStates
     *
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    private function getPendingOrders($idStates)
    {
        $idStates = array_map('intval', $idStates);

        $orders = \Db::getInstance()->executeS('
            SELECT o.id_order, o.current_state, o.total_paid, o.date_add, c.id_customer, c.firstname, c.lastname
            FROM `' . _DB_PREFIX_ . 'orders` o
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (o.id_customer = c.id_customer)
            WHERE o.module = "ps_checkout"
            AND o.id_shop = ' . (int) \Context::getContext()->shop->id . '
            AND o.current_state IN (' . implode(',', array_keys($idStates)) . ')
            ORDER BY o.date_add DESC
            LIMIT 1000
        ');

        if (empty($orders)) {
            return [];
        }

        return $orders;
    }
}
