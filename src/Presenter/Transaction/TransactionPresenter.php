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

namespace PrestaShop\Module\PrestashopCheckout\Presenter\Transaction;

use Context;
use PrestaShop\Module\PrestashopCheckout\Adapter\CurrencyAdapter;
use PrestaShop\Module\PrestashopCheckout\Adapter\LinkAdapter;
use PrestaShop\Module\PrestashopCheckout\Adapter\ToolsAdapter;
use PrestaShop\Module\PrestashopCheckout\Presenter\PresenterInterface;
use PrestaShop\Module\PrestashopCheckout\Repository\OrderPaymentRepository;
use PrestaShopDatabaseException;
use Ps_checkout;

/**
 * Present the pending orders for the reporting
 */
class TransactionPresenter implements PresenterInterface
{
    /**
     * @var Context
     */
    private $context;
    /**
     * @var OrderPaymentRepository
     */
    private $orderPaymentRepository;
    /**
     * @var LinkAdapter
     */
    private $linkAdapter;
    /**
     * @var ToolsAdapter
     */
    private $toolsAdapter;
    /**
     * @var CurrencyAdapter
     */
    private $currencyAdapter;
    /**
     * @var Ps_checkout
     */
    private $module;

    public function __construct(
        Ps_checkout $module,
        Context $context,
        OrderPaymentRepository $orderPaymentRepository,
        LinkAdapter $linkAdapter,
        ToolsAdapter $toolsAdapter,
        CurrencyAdapter $currencyAdapter
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->orderPaymentRepository = $orderPaymentRepository;
        $this->linkAdapter = $linkAdapter;
        $this->toolsAdapter = $toolsAdapter;
        $this->currencyAdapter = $currencyAdapter;
    }

    /**
     * present pending orders
     *
     * @return array
     *
     * @throws PrestaShopDatabaseException
     */
    public function present()
    {
        $transactions = $this->orderPaymentRepository->findAllPSCheckoutModule((int) $this->context->shop->id);

        foreach ($transactions as &$transaction) {
            $transaction['transactionID'] = $transaction['transaction_id'];
            $transaction['order_id'] = $transaction['id_order'];
            $transaction['orderLink'] = $this->linkAdapter->getAdminLink('AdminOrders', true, [], ['id_order' => $transaction['id_order'], 'vieworder' => 1]);
            $transaction['username'] = substr($transaction['firstname'], 0, 1) . '. ' . $transaction['lastname'];
            $transaction['userProfileLink'] = $this->linkAdapter->getAdminLink('AdminCustomers', true, [], ['id_customer' => $transaction['id_customer'], 'viewcustomer' => 1]);
            $transaction['before_commission'] = $this->toolsAdapter->displayPrice($transaction['amount'], $this->currencyAdapter->getCurrencyInstance((int) $transaction['id_currency']));
            $transaction['type'] = strpos($transaction['amount'], '-') !== false ? 'Refund' : 'Payment';
            $transaction['typeForDisplay'] = ($transaction['type'] === 'Refund') ? $this->module->l('Refund', 'translations') : $this->module->l('Payment', 'translations');
            $transaction['commission'] = '-';
            $transaction['total_paid'] = '-';
        }

        return $transactions;
    }
}
