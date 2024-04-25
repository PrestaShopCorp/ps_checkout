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
use Exception;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\Entity\PaymentToken;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;

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

    /**
     * @param PaymentToken $paymentToken
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function save(PaymentToken $paymentToken)
    {
        try {
            return $this->db->insert(
                PaymentToken::TABLE,
                [
                    'token_id' => pSQL($paymentToken->getId()->getValue()),
                    'paypal_customer_id' => pSQL($paymentToken->getPayPalCustomerId()->getValue()),
                    'payment_source' => pSQL($paymentToken->getPaymentSource()),
                    'data' => pSQL(json_encode($paymentToken->getData())),
                    'merchant_id' => pSQL($paymentToken->getMerchantId()),
                    'status' => pSQL($paymentToken->getStatus()),
                    'is_favorite' => (int) $paymentToken->isFavorite(),
                ],
                false,
                true,
                Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Payment Token', 0, $exception);
        }
    }

    /**
     * @param PaymentTokenId $paymentTokenId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function deleteById(PaymentTokenId $paymentTokenId)
    {
        try {
            return $this->db->delete(PaymentToken::TABLE, sprintf('`token_id` = "%s"', pSQL($paymentTokenId->getValue())));
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while deleting PayPal Payment Token', 0, $exception);
        }
    }

    /**
     * @param int $psCustomerId
     *
     * @return PaymentToken[]
     *
     * @throws PsCheckoutException
     */
    public function findByPrestaShopCustomerId($psCustomerId, $onlyVaulted = false, $merchantId = null)
    {
        try {
            $query = new DbQuery();
            $query->select('t.*');
            $query->from(PaymentToken::TABLE, 't');
            $query->leftJoin('pscheckout_customer', 'c', 't.`paypal_customer_id` = c.`paypal_customer_id`');
            $query->where(sprintf('c.`id_customer` = %d', (int) $psCustomerId));
//            $query->orderBy('t.`is_favorite` DESC');
            $query->orderBy('t.`id` ASC');

            if ($merchantId) {
                $query->where(sprintf('t.`merchant_id` = "%s"', pSQL($merchantId)));
            }

            if ($onlyVaulted) {
                $query->where('t.`status` = "VAULTED"');
            }

            $queryResult = $this->db->executeS($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while fetching PayPal Payment Tokens', 0, $exception);
        }

        if (empty($queryResult)) {
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
     * @throws PsCheckoutException
     */
    public function findByPayPalCustomerId(PayPalCustomerId $customerId)
    {
        try {
            $query = new DbQuery();
            $query->select('t.*');
            $query->from(PaymentToken::TABLE, 't');
            $query->where(sprintf('t.`paypal_customer_id` = "%s"', pSQL($customerId->getValue())));
            $query->orderBy('t.`is_favorite` DESC');
            $query->orderBy('t.`id` ASC');
            $queryResult = $this->db->executeS($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while fetching PayPal Payment Tokens', 0, $exception);
        }

        if (empty($queryResult)) {
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
     *
     * @throws PsCheckoutException
     */
    public function findById(PaymentTokenId $id)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from(PaymentToken::TABLE);
            $query->where(sprintf('`token_id` = "%s"', pSQL($id->getValue())));
            $result = $this->db->getRow($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while fetching PayPal Payment Token', 0, $exception);
        }

        if ($result && is_array($result)) {
            return $this->buildPaymentTokenObject($result);
        }

        return false;
    }

    /**
     * @param PaymentTokenId $paymentTokenId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function setTokenFavorite(PaymentTokenId $paymentTokenId)
    {
        $token = $this->findById($paymentTokenId);

        if ($token) {
            $customerId = $token->getPayPalCustomerId()->getValue();

            try {
                $this->db->update(PaymentToken::TABLE, ['is_favorite' => 0], sprintf('`paypal_customer_id` = "%s"', pSQL($customerId)));
                $this->db->update(PaymentToken::TABLE, ['is_favorite' => 1], sprintf('`token_id` = "%s"', pSQL($paymentTokenId->getValue())));
            } catch (Exception $exception) {
                throw new PsCheckoutException('Error while setting PayPal Payment Token as favorite', 0, $exception);
            }

            return true;
        }

        return false;
    }

    /**
     * @param int|null $customerId
     *
     * @return int
     *
     * @throws PsCheckoutException
     */
    public function getCount($customerId = null, $merchantId = null)
    {
        try {
            $query = new DbQuery();
            $query->select('COUNT(t.id)');
            $query->from(PaymentToken::TABLE, 't');

            if ($merchantId) {
                $query->where(sprintf('t.`merchant_id` = "%s"', pSQL($merchantId)));
            }

            if ($customerId) {
                $query->leftJoin('pscheckout_customer', 'c', 't.`paypal_customer_id` = c.`paypal_customer_id`');
                $query->where(sprintf('c.`id_customer` = %d', (int) $customerId));
            }

            return (int) $this->db->getValue($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while counting PayPal Payment Tokens', 0, $exception);
        }
    }

    /**
     * @param array $data
     *
     * @return PaymentToken
     */
    private function buildPaymentTokenObject(array $data)
    {
        return new PaymentToken(
            $data['token_id'],
            $data['paypal_customer_id'],
            $data['payment_source'],
            json_decode($data['data'], true),
            $data['merchant_id'],
            $data['status'],
            (bool) $data['is_favorite']
        );
    }
}
