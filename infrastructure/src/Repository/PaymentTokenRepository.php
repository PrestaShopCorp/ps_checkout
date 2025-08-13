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

namespace PsCheckout\Infrastructure\Repository;

use DbQuery;
use Exception;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PaymentToken\Repository\PaymentTokenRepositoryInterface;
use PsCheckout\Core\PaymentToken\ValueObject\PaymentToken;

class PaymentTokenRepository implements PaymentTokenRepositoryInterface
{
    const TABLE_NAME = 'pscheckout_payment_token';

    /**
     * @var \Db
     */
    private $db;

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($customerId = null, $merchantId = null): int
    {
        try {
            $query = new \DbQuery();
            $query->select('COUNT(t.id)');
            $query->from(self::TABLE_NAME, 't');

            if ($merchantId) {
                $query->where(sprintf('t.`merchant_id` = "%s"', pSQL($merchantId)));
            }

            if ($customerId) {
                $query->leftJoin('pscheckout_customer', 'c', 't.`paypal_customer_id` = c.`paypal_customer_id`');
                $query->where(sprintf('c.`id_customer` = %d', (int) $customerId));
            }

            return (int) $this->db->getValue($query);
        } catch (\Exception $exception) {
            throw new PsCheckoutException('Error while counting PayPal Payment Tokens', 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function findVaultedTokensByCustomerAndMerchant(int $customerId, string $merchantId): array
    {
        try {
            $query = new DbQuery();
            $query->select('t.*');
            $query->from(self::TABLE_NAME, 't');
            $query->leftJoin('pscheckout_customer', 'c', 't.`paypal_customer_id` = c.`paypal_customer_id`');

            $query->where(sprintf(
                'c.`id_customer` = %d AND t.`merchant_id` = "%s" AND t.`status` = "VAULTED"',
                $customerId,
                pSQL($merchantId)
            ));

            $query->orderBy('t.`id` ASC');

            $queryResult = $this->db->executeS($query);

            return $queryResult ?: [];
        } catch (\Exception $exception) {
            // Intentionally to not break payment method rendering
            return [];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getOneById(string $vaultId)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from(self::TABLE_NAME);
            $query->where(sprintf('`token_id` = "%s"', pSQL($vaultId)));
            $result = $this->db->getRow($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while fetching PayPal Payment Token', 0, $exception);
        }

        if (!is_array($result) || empty($result)) {
            return null;
        }

        return new PaymentToken(
            $result['token_id'],
            $result['paypal_customer_id'],
            $result['payment_source'],
            json_decode($result['data'], true),
            $result['merchant_id'],
            $result['status'],
            (bool) $result['is_favorite']
        );
    }

    /**
     * {@inheritDoc}
     */
    public function save(PaymentToken $token)
    {
        try {
            $this->db->insert(
                self::TABLE_NAME,
                [
                    'token_id' => pSQL($token->getId()),
                    'paypal_customer_id' => pSQL($token->getPayPalCustomerId()),
                    'payment_source' => pSQL($token->getPaymentSource()),
                    'data' => pSQL(json_encode($token->getData())),
                    'merchant_id' => pSQL($token->getMerchantId()),
                    'status' => pSQL($token->getStatus()),
                    'is_favorite' => (int) $token->isFavorite(),
                ],
                false,
                true,
                \Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Payment Token', 0, $exception);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function setTokenFavorite(string $tokenId, string $customerId)
    {
        try {
            $this->db->update(self::TABLE_NAME, ['is_favorite' => 0], sprintf('`paypal_customer_id` = "%s"', pSQL($customerId)));
            $this->db->update(self::TABLE_NAME, ['is_favorite' => 1], sprintf('`token_id` = "%s"', pSQL($tokenId)));
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while setting PayPal Payment Token as favorite', 0, $exception);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function delete(string $vaultId)
    {
        $this->db->delete(
            self::TABLE_NAME,
            sprintf('`token_id` = "%s"', pSQL($vaultId))
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getAllByCustomerId(int $customerId): array
    {
        try {
            $query = new DbQuery();
            $query->select('t.*');
            $query->from(self::TABLE_NAME, 't');
            $query->leftJoin('pscheckout_customer', 'c', 't.`paypal_customer_id` = c.`paypal_customer_id`');
            $query->where(sprintf('c.`id_customer` = %d', $customerId));
            $query->orderBy('t.`id` ASC');

            $queryResult = $this->db->executeS($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while fetching PayPal Payment Tokens', 0, $exception);
        }

        if (empty($queryResult)) {
            return [];
        }

        return array_map(function ($paymentToken) {
            return $this->buildPaymentToken($paymentToken);
        }, $queryResult);
    }

    /**
     * @param array $result
     *
     * @return PaymentToken
     */
    private function buildPaymentToken(array $result): PaymentToken
    {
        return new PaymentToken(
            $result['token_id'],
            $result['paypal_customer_id'],
            $result['payment_source'],
            json_decode($result['data'], true),
            $result['merchant_id'],
            $result['status'],
            (bool) $result['is_favorite']
        );
    }
}
