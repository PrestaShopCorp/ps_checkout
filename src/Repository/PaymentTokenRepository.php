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
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;
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
                'id' => pSQL($paymentToken->getId()->getValue()),
                'paypal_customer_id' => pSQL($paymentToken->getPayPalCustomerId()->getValue()),
                'payment_source' => pSQL($paymentToken->getPaymentSource()),
                'data' => json_encode($paymentToken->getData()),
                'merchant_id' => $paymentToken->getMerchantId(),
                'is_favorite' => (bool) $paymentToken->isFavorite(),
            ],
            false,
            true,
            Db::REPLACE
        );
    }

    /**
     * @param PaymentTokenId $paymentTokenId
     *
     * @return bool
     */
    public function deleteById(PaymentTokenId $paymentTokenId)
    {
        return $this->db->delete(
            PaymentToken::TABLE,
            '`id` = "' . pSQL($paymentTokenId->getValue()) . '"'
        );
    }

    /**
     * @param int $psCustomerId
     *
     * @return PaymentToken[]
     *
     * @throws PrestaShopDatabaseException
     */
    public function findByPrestaShopCustomerId($psCustomerId)
    {
        $query = new DbQuery();
        $query->select('t.*')
            ->from(PaymentToken::TABLE, 't')
            ->leftJoin('pscheckout_customer', 'c', 't.`paypal_customer_id` = c.`paypal_customer_id`')
            ->where('c.`id_customer` =' . (int) $psCustomerId)
            ->orderBy('t.`is_favorite` DESC')
            ->orderBy('t.`id` ASC');
        $queryResult = $this->db->executeS($query);

        if (!$queryResult) {
            return [];
        }

        return array_map(function ($paymentToken) {
            return $this->buildPaymentTokenObject($paymentToken);
        }, $queryResult);
    }

    /**
     * @param PayPalCustomerId $customerId
     *
     * @return PaymentToken[]
     *
     * @throws PrestaShopDatabaseException
     */
    public function findByPayPalCustomerId(PayPalCustomerId $customerId)
    {
        $query = new DbQuery();
        $query->select('t.*')
            ->from(PaymentToken::TABLE, 't')
            ->where('t.`paypal_customer_id` =' . pSQL($customerId->getValue()))
            ->orderBy('t.`is_favorite` DESC')
            ->orderBy('t.`id` ASC');
        $queryResult = $this->db->executeS($query);

        if (!$queryResult) {
            return [];
        }

        return array_map(function ($paymentToken) {
            return $this->buildPaymentTokenObject($paymentToken);
        }, $queryResult);
    }

    /**
     * @param PaymentTokenId $id
     *
     * @return PaymentToken|false
     */
    public function findById(PaymentTokenId $id)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(PaymentToken::TABLE)
            ->where('id = ' . $id->getValue());
        $result = $this->db->getRow($query);

        if ($result && is_array($result)) {
            return $this->buildPaymentTokenObject($result);
        }

        return false;
    }

    /**
     * @param PaymentTokenId $id
     *
     * @return bool
     */
    public function setTokenFavorite(PaymentTokenId $id)
    {
        $token = $this->findById($id);
        if ($token) {
            $customerId = $token->getPayPalCustomerId()->getValue();
            $sql = 'UPDATE ' . _DB_PREFIX_ . PaymentToken::TABLE . " SET `is_favorite` = 0 WHERE id_customer = $customerId;";
            $sql .= 'UPDATE ' . _DB_PREFIX_ . PaymentToken::TABLE . " SET `is_favorite` = 1 WHERE id = {$id->getValue()};";

            return $this->db->execute($sql);
        }

        return false;
    }

    /**
     * @param int|null $customerId
     *
     * @return int
     */
    public function getCount($customerId = null)
    {
        $query = new DbQuery();
        $query->select('COUNT(t.*)')
            ->from(PaymentToken::TABLE, 't');

        if ($customerId) {
            $query->leftJoin('pscheckout_customer', 'c', 't.`paypal_customer_id` = c.`paypal_customer_id`')
                ->where('c.`id_customer` =' . (int) $customerId);
        }

        return (int) $this->db->getValue($query);
    }

    /**
     * @param array $data
     *
     * @return PaymentToken
     */
    private function buildPaymentTokenObject($data)
    {
        return new PaymentToken(
            $data['id'],
            $data['paypal_customer_id'],
            $data['payment_source'],
            json_decode($data['data'], true),
            $data['merchant_id'],
            (bool) $data['is_favorite']
        );
    }
}
