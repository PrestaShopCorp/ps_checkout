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

use Db;
use DbQuery;
use PrestaShop\Module\PrestashopCheckout\Entity\PaymentToken;
use PrestaShopDatabaseException;

class PaymentTokenRepository
{
    /**
     * @var Db
     */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    public function save(PaymentToken $paymentToken)
    {
        return $this->db->insert(
            PaymentToken::TABLE,
            [
                'id' => $paymentToken->getId(),
                'paypal_customer_id' => $paymentToken->getPayPalCustomerId(),
                'payment_source' => $paymentToken->getPaymentSource(),
                'data' => $paymentToken->getData(),
                'is_favorite' => $paymentToken->isFavorite(),
            ],
            false,
            true,
            Db::REPLACE
        );
    }

    /**
     * @param $customerId
     *
     * @return PaymentToken[]
     *
     * @throws PrestaShopDatabaseException
     */
    public function getAllByCustomerId($customerId)
    {
        $query = new DbQuery();
        $query->select('t.`id`, t.`paypal_customer_id`, t.`payment_source`, t.`data`, t.`is_favorite`')
            ->from(PaymentToken::TABLE, 't')
            ->leftJoin('pscheckout_customer', 'c', 't.`paypal_customer_id` = c.`paypal_customer_id`')
            ->where('c.`id_customer` =' . (int) $customerId)
            ->orderBy('t.`is_favorite` DESC')
            ->orderBy('t.`id` ASC');
        $queryResult = $this->db->executeS($query);

        if (!$queryResult) {
            return [];
        }

        return array_map(function ($paymentSource) {
            return new PaymentToken(
                $paymentSource['id'],
                $paymentSource['paypal_customer_id'],
                $paymentSource['payment_source'],
                $paymentSource['data'],
                (bool) $paymentSource['is_favorite']
            );
        }, $queryResult);
    }
}
