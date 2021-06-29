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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Order;

use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\OrderStates;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Repository\OrderRepository;
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

        $idStates = array_map('intval', $orderStates);

        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        /** @var OrderRepository $repository */
        $repository = $module->getService('ps_checkout.repository.order');
        $orders = $repository->findByStates(\Context::getContext()->shop->id, $idStates);

        foreach ($orders as &$order) {
            $order['username'] = substr($order['firstname'], 0, 1) . '. ' . $order['lastname'];
            $order['userProfileLink'] = $link->getAdminLink('AdminCustomers', true, [], ['id_customer' => $order['id_customer'], 'viewcustomer' => 1]);
            $order['orderLink'] = $link->getAdminLink('AdminOrders', true, [], ['id_order' => $order['id_order'], 'vieworder' => 1]);
            $order['state'] = $orderStates[$order['current_state']];
            $order['before_commission'] = \Tools::displayPrice($order['total_paid'], \Currency::getCurrencyInstance($order['id_currency']));
            // TODO: Waiting for paypal infos (reporting lot 2)
            $order['commission'] = '-';
            $order['total_paid'] = '-';
        }

        return $orders;
    }
}
