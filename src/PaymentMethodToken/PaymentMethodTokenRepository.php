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

namespace PrestaShop\Module\PrestashopCheckout\PaymentMethodToken;

use Db;
use DbQuery;
use Exception;
use PrestaShop\Module\PrestashopCheckout\PaymentMethodToken\ValueObject\PaymentMethodTokenId;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;

class PaymentMethodTokenRepository
{
    /**
     * @var Db
     */
    private $db;

    /**
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * @param PayPalCustomerId $customerId
     * @param int $pageSize
     * @param int $pageNumber
     *
     * @return array
     *
     * @throws Exception
     */
    public function findByCustomerId(PayPalCustomerId $customerId, $pageSize, $pageNumber)
    {
        try {
            $query = new DbQuery();
            $query->from('pscheckout_payment_token');
            $query->where('`paypal_customer_id` = ' . (int) $customerId->getValue());
            $query->limit($pageSize, ($pageNumber - 1) * $pageSize);
            $results = $this->db->executeS($query);

            if (false === $results) {
                throw new Exception('Failed to get PayPal Payment Method tokens from database.');
            }

            $paymentMethodTokens = [];

            foreach ($results as $result) {
                $paymentMethodTokens[] = [
                    'id' => $result['id'],
                    'paypal_customer_id' => $result['paypal_customer_id'],
                    'payment_source' => $result['payment_source'],
                    'data' => json_decode($result['data'], true),
                ];
            }

            return $paymentMethodTokens;
        } catch (Exception $exception) {
            throw new Exception('Failed to get PayPal Payment Method tokens.', 0, $exception);
        }
    }

    /**
     * @param PaymentMethodTokenId $id
     * @param PayPalCustomerId $paypalCustomerId
     * @param string $paymentSource
     * @param array $data
     *
     * @return void
     *
     * @throws Exception
     */
    public function save(PaymentMethodTokenId $id, PayPalCustomerId $paypalCustomerId, $paymentSource, array $data)
    {
        try {
            $this->db->insert(
                'pscheckout_payment_token',
                [
                    'id' => pSQL($id->getValue()),
                    'paypal_customer_id' => pSQL($paypalCustomerId->getValue()),
                    'payment_source' => pSQL($paymentSource),
                    'data' => pSQL(json_encode($data)),
                ]
            );
        } catch (Exception $exception) {
            throw new Exception('Failed to save PayPal Payment Method token.', 0, $exception);
        }
    }

    /**
     * @param PayPalCustomerId $customerId
     *
     * @return int
     *
     * @throws Exception
     */
    public function getTotalItems(PayPalCustomerId $customerId)
    {
        $query = new DbQuery();
        $query->select('COUNT(*)');
        $query->from('pscheckout_payment_token');
        $query->where('`paypal_customer_id` = ' . (int) $customerId->getValue());
        $totalItems = $this->db->getValue($query);

        if (false === $totalItems) {
            throw new Exception('Failed to get PayPal Payment Method tokens from database.');
        }

        return (int) $totalItems;
    }
}
