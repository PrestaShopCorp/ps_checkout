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
                $idState = \Configuration::get($key);
                $orderStates[$idState]['color'] = $value;
                $orderStates[$idState]['name'] = $orderTranslations[$key];
            }
        }

        $orders = $this->getPendingOrders($orderStates);

        foreach ($orders as &$order) {
            $order['username'] = $this->getUsername($order['id_customer']);
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
     * getUsername
     *
     * @param string|int $userID
     *
     * @return string
     */
    private function getUsername($userID)
    {
        $sql = 'SELECT firstname,lastname FROM `' . _DB_PREFIX_ . 'customer` o WHERE id_customer = ' . $userID;
        $user = \Db::getInstance()->getRow($sql);

        return substr($user['firstname'], 0, 1) . '. ' . $user['lastname'];
    }

    /**
     * get last 1000 pending checkout orders
     *
     * @param array $idStates
     *
     * @return mixed
     */
    private function getPendingOrders($idStates)
    {
        $idStates = array_map('intval', $idStates);
        $sql = 'SELECT id_order, current_state, total_paid, date_add, id_customer
            FROM `' . _DB_PREFIX_ . 'orders` o
            WHERE o.module = "ps_checkout"
            AND current_state IN (' . implode(',', array_keys($idStates)) . ')
            ORDER BY date_add DESC
            LIMIT 1000
        ';

        return \Db::getInstance()->executeS($sql);
    }

    /**
     * castToInt
     *
     * @param string $stringToCast
     *
     * @return int
     */
    private function castToInt($stringToCast)
    {
        return (int) $stringToCast;
    }
}
