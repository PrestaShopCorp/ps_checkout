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
                'id_customer' => (int) $paymentToken->getCustomerId(),
                'id_shop' => (int) $paymentToken->getShopId(),
                'payment_source' => pSQL($paymentToken->getPaymentSource()),
                'data' => json_encode($paymentToken->getData()),
                'is_favorite' => (bool) $paymentToken->isFavorite(),
            ],
            false,
            true,
            Db::REPLACE
        );
    }

    /**
     * @param string $paymentTokenId
     * @return bool
     */
    public function deleteById($paymentTokenId)
    {
        return $this->db->delete(
            PaymentToken::TABLE,
            'id = ' . pSQL($paymentTokenId)
        );
    }

    /**
     * @param int $shopId
     * @return bool
     */
    public function deleteByShopId($shopId)
    {
        return $this->db->delete(
            PaymentToken::TABLE,
            'id_shop = ' . (int) $shopId
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
            ->where('t.`id_customer` =' . (int) $psCustomerId)
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
     * @return bool
     */
    public function setTokenFavorite(PaymentTokenId $id)
    {
        $token = $this->findById($id);
        if ($token) {
            $customerId = $token->getCustomerId();
            $sql = 'UPDATE ' . _DB_PREFIX_ . PaymentToken::TABLE . " SET `is_favorite` = 0 WHERE id_customer = $customerId;";
            $sql .= 'UPDATE ' . _DB_PREFIX_ . PaymentToken::TABLE . " SET `is_favorite` = 1 WHERE id = {$id->getValue()};";
            return $this->db->execute($sql);
        }
        return false;
    }

    /**
     * @param int|null $shopId
     *
     * @return int
     */
    public function getCount($shopId = null)
    {
        $query = new DbQuery();
        $query->select('COUNT(*)')
            ->from(PaymentToken::TABLE, 't');

        if ($shopId) {
            $query->where('t.`id_shop` =' . (int) $shopId);
        }

        return (int) $this->db->getValue($query);
    }

    /**
     * @param array $data
     * @return PaymentToken
     */
    private function buildPaymentTokenObject($data)
    {
        return new PaymentToken(
            $data['id'],
            $data['paypal_customer_id'],
            $data['id_customer'],
            $data['id_shop'],
            $data['payment_source'],
            json_decode($data['data'], true),
            (bool)$data['is_favorite']
        );
    }
}
