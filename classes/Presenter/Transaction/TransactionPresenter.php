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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Transaction;

use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;

/**
 * Present the pending orders for the reporting
 */
class TransactionPresenter implements PresenterInterface
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
        $transactions = $this->getTransactions();
        $module = \Module::getInstanceByName('ps_checkout');

        foreach ($transactions as &$transaction) {
            $transaction['transactionID'] = $transaction['transaction_id'];
            $transaction['order_id'] = $transaction['id_order'];
            $transaction['orderLink'] = $link->getAdminLink('AdminOrders', true, [], ['id_order' => $transaction['id_order'], 'vieworder' => 1]);
            $transaction['username'] = substr($transaction['firstname'], 0, 1) . '. ' . $transaction['lastname'];
            $transaction['userProfileLink'] = $link->getAdminLink('AdminCustomers', true, [], ['id_customer' => $transaction['id_customer'], 'viewcustomer' => 1]);
            $transaction['before_commission'] = \Tools::displayPrice($transaction['amount'], \Currency::getCurrencyInstance((int) $transaction['id_currency']));
            $transaction['type'] = strpos($transaction['amount'], '-') !== false ? 'Refund' : 'Payment';
            $transaction['typeForDisplay'] = ($transaction['type'] === 'Refund') ? $module->l('Refund', 'translations') : $module->l('Payment', 'translations');
            $transaction['commission'] = '-';
            $transaction['total_paid'] = '-';
        }

        return $transactions;
    }

    /**
     * @return array
     *
     * @throws \PrestaShopDatabaseException
     */
    private function getTransactions()
    {
        $transactions = \Db::getInstance()->executeS('
            SELECT op.*, o.id_order, c.id_customer, c.firstname, c.lastname
            FROM `' . _DB_PREFIX_ . 'order_payment` op
            INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.reference = op.order_reference)
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.id_customer = o.id_customer)
            WHERE o.module = "ps_checkout"
            AND op.transaction_id IS NOT NULL
            AND op.transaction_id != ""
            AND o.id_shop = ' . (int) \Context::getContext()->shop->id . '
            ORDER BY op.date_add DESC
            LIMIT 1000
        ');

        if (empty($transactions)) {
            return [];
        }

        return $transactions;
    }
}
