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

namespace PrestaShop\Module\PrestashopCheckout\Repository;

/**
 * Class OrderPaymentRepository used to interact with the DB order_payment table
 */
class OrderPaymentRepository
{
    /**
     * @param int $shopId
     *
     * @return array
     */
    public function findAllPSCheckoutModule($shopId)
    {
        $transactions = \Db::getInstance()->executeS('
            SELECT op.*, o.id_order, c.id_customer, c.firstname, c.lastname
            FROM `' . _DB_PREFIX_ . 'order_payment` op
            INNER JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.reference = op.order_reference)
            INNER JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.id_customer = o.id_customer)
            WHERE o.module = "ps_checkout"
            AND op.transaction_id IS NOT NULL
            AND op.transaction_id != ""
            AND o.id_shop = ' . (int) $shopId . '
            ORDER BY op.date_add DESC
            LIMIT 1000
        ');

        if (empty($transactions)) {
            return [];
        }

        return $transactions;
    }
}
