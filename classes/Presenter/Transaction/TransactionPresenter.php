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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Transaction;

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
     */
    public function present()
    {
        $transactions = $this->getTransactions();

        foreach ($transactions as &$transaction) {
            $userInfos = $this->getUserInfos($transaction['order_reference']);
            $transaction['transactionID'] = $transaction['transaction_id'];
            $transaction['order_id'] = $this->getOrderIDByOrderReference($transaction['order_reference']);
            $transaction['username'] = $userInfos['name'];
            $transaction['userProfileLink'] = $userInfos['link'];
            $currency = new \Currency($transaction['id_currency']);
            $transaction['before_commission'] = \Tools::displayPrice($transaction['amount'], $currency);
            $transaction['type'] = strpos($transaction['amount'], '-') !== false ? 'Refund' : 'Payment';
            $transaction['commission'] = '-';
            $transaction['total_paid'] = '-';
        }

        return $transactions;
    }

    private function getTransactions()
    {
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'order_payment` o
            WHERE payment_method = "Prestashop Checkout"
            ORDER BY date_add DESC
            LIMIT 1000
        ';

        return \Db::getInstance()->executeS($sql);
    }

    private function getOrderIDByOrderReference($reference)
    {
        $sql = 'SELECT id_order FROM `' . _DB_PREFIX_ . 'orders` o
            WHERE reference = "' . $reference . '"
        ';

        return \Db::getInstance()->getValue($sql);
    }

    /**
     * getUserInfos
     *
     * @param string $orderReference
     *
     * @return string
     */
    private function getUserInfos($orderReference)
    {
        $sql = 'SELECT id_customer FROM `' . _DB_PREFIX_ . 'orders` WHERE reference = "' . $orderReference . '"';
        $userID = \Db::getInstance()->getRow($sql);

        $sql = 'SELECT firstname,lastname FROM `' . _DB_PREFIX_ . 'customer` WHERE id_customer = ' . (int) $userID['id_customer'];
        $user = \Db::getInstance()->getRow($sql);

        return [
            'name' => substr($user['firstname'], 0, 1) . '. ' . $user['lastname'],
            'link' => \Tools::getShopDomainSsl(true) . (new \Link())->getAdminLink('AdminCustomers', $userID),
        ];
    }
}
