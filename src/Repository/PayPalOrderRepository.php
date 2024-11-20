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
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrderAuthorization;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrderCapture;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrderPurchaseUnit;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\Entity\PayPalOrderRefund;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShop\Module\PrestashopCheckout\PayPal\PaymentToken\ValueObject\PaymentTokenId;

class PayPalOrderRepository
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
     * @param PayPalOrder $payPalOrder
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function savePayPalOrder(PayPalOrder $payPalOrder)
    {
        try {
            return $this->db->insert(
            PayPalOrder::TABLE,
            [
                'id' => pSQL($payPalOrder->getId()->getValue()),
                'id_cart' => (int) $payPalOrder->getIdCart(),
                'status' => pSQL($payPalOrder->getStatus()),
                'intent' => pSQL($payPalOrder->getIntent()),
                'funding_source' => pSQL($payPalOrder->getFundingSource()),
                'payment_source' => pSQL(json_encode($payPalOrder->getPaymentSource())),
                'environment' => pSQL($payPalOrder->getEnvironment()),
                'is_card_fields' => (int) $payPalOrder->isCardFields(),
                'is_express_checkout' => (int) $payPalOrder->isExpressCheckout(),
                'customer_intent' => pSQL(implode(',', $payPalOrder->getCustomerIntent())),
                'payment_token_id' => $payPalOrder->getPaymentTokenId() ? pSQL($payPalOrder->getPaymentTokenId()->getValue()) : null,
            ],
            false,
            true,
            Db::REPLACE
        );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Order', 0, $exception);
        }
    }

    /**
     * @param PayPalOrderId $payPalOrderId
     *
     * @return PayPalOrder
     *
     * @throws PsCheckoutException
     */
    public function getPayPalOrderById(PayPalOrderId $payPalOrderId)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from(PayPalOrder::TABLE, 'o');
            $query->where(sprintf('o.`id` = "%s"', pSQL($payPalOrderId->getValue())));
            $queryResult = $this->db->getRow($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while retrieve PayPal Order', 0, $exception);
        }

        if (empty($queryResult)) {
            throw new PsCheckoutException('PayPal Order not found');
        }

        return new PayPalOrder(
            $queryResult['id'],
            (int) $queryResult['id_cart'],
            $queryResult['intent'],
            $queryResult['funding_source'],
            $queryResult['status'],
            json_decode($queryResult['payment_source'], true),
            $queryResult['environment'],
            $queryResult['is_card_fields'],
            $queryResult['is_express_checkout'],
            explode(',', $queryResult['customer_intent']),
            $queryResult['payment_token_id'] ? new PaymentTokenId($queryResult['payment_token_id']) : null
        );
    }

    /**
     * @param int $cartId
     *
     * @return PayPalOrder
     *
     * @throws PsCheckoutException
     */
    public function getPayPalOrderByCartId($cartId)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from(PayPalOrder::TABLE, 'o');
            $query->where(sprintf('o.`id_cart` = %d', (int) $cartId));
            $queryResult = $this->db->getRow($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while retrieve PayPal Order', 0, $exception);
        }

        if (empty($queryResult)) {
            throw new PsCheckoutException('PayPal Order not found');
        }

        return new PayPalOrder(
            $queryResult['id'],
            (int) $queryResult['id_cart'],
            $queryResult['intent'],
            $queryResult['funding_source'],
            $queryResult['status'],
            json_decode($queryResult['payment_source'], true),
            $queryResult['environment'],
            $queryResult['is_card_fields'],
            $queryResult['is_express_checkout'],
            explode(',', $queryResult['customer_intent']),
            $queryResult['payment_token_id'] ? new PaymentTokenId($queryResult['payment_token_id']) : null
        );
    }

    /**
     * @param PayPalOrderId $payPalOrderId
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function deletePayPalOrder(PayPalOrderId $payPalOrderId)
    {
        try {
            $this->db->delete(PayPalOrder::TABLE, sprintf('`id` = "%s"', pSQL($payPalOrderId->getValue())));
            $this->db->delete(PayPalOrderAuthorization::TABLE, sprintf('`id_order` = "%s"', pSQL($payPalOrderId->getValue())));
            $this->db->delete(PayPalOrderRefund::TABLE, sprintf('`id_order` = "%s"', pSQL($payPalOrderId->getValue())));
            $this->db->delete(PayPalOrderCapture::TABLE, sprintf('`id_order` = "%s"', pSQL($payPalOrderId->getValue())));
            $this->db->delete(PayPalOrderPurchaseUnit::TABLE, sprintf('`id_order` = "%s"', pSQL($payPalOrderId->getValue())));
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while deleting PayPal Order', 0, $exception);
        }

        return true;
    }

    /**
     * @param PayPalOrderAuthorization $payPalOrderAuthorization
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function savePayPalOrderAuthorization(PayPalOrderAuthorization $payPalOrderAuthorization)
    {
        try {
            return $this->db->insert(
                PayPalOrderAuthorization::TABLE,
                [
                    'id' => pSQL($payPalOrderAuthorization->getId()),
                    'id_order' => pSQL($payPalOrderAuthorization->getIdOrder()),
                    'status' => pSQL($payPalOrderAuthorization->getStatus()),
                    'expiration_time' => pSQL($payPalOrderAuthorization->getExpirationTime()),
                    'seller_protection' => pSQL(json_encode($payPalOrderAuthorization->getSellerProtection())),
                ],
                false,
                true,
                Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Order Authorization', 0, $exception);
        }
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderAuthorization[]
     *
     * @throws PsCheckoutException
     */
    public function getPayPalOrderAuthorizations($payPalOrderId)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from(PayPalOrderAuthorization::TABLE, 'a');
            $query->where(sprintf('a.`id_order` = "%s"', pSQL($payPalOrderId)));
            $queryResult = $this->db->executeS($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while retrieve PayPal Order Authorization', 0, $exception);
        }

        if (empty($queryResult)) {
            throw new PsCheckoutException('PayPal Order not found');
        }

        return array_map(function ($authorization) {
            return new PayPalOrderAuthorization(
                $authorization['id'],
                $authorization['id_order'],
                $authorization['status'],
                $authorization['expiration_time'],
                json_decode($authorization['seller_protection'], true)
            );
        }, $queryResult);
    }

    /**
     * @param PayPalOrderCapture $payPalOrderCapture
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function savePayPalOrderCapture(PayPalOrderCapture $payPalOrderCapture)
    {
        try {
            return $this->db->insert(
                PayPalOrderCapture::TABLE,
                [
                    'id' => pSQL($payPalOrderCapture->getId()),
                    'id_order' => pSQL($payPalOrderCapture->getIdOrder()),
                    'status' => pSQL($payPalOrderCapture->getStatus()),
                    'final_capture' => (int) $payPalOrderCapture->getFinalCapture(),
                    'created_at' => pSQL($payPalOrderCapture->getCreatedAt()),
                    'updated_at' => pSQL($payPalOrderCapture->getUpdatedAt()),
                    'seller_protection' => pSQL(json_encode($payPalOrderCapture->getSellerProtection())),
                    'seller_receivable_breakdown' => pSQL(json_encode($payPalOrderCapture->getSellerReceivableBreakdown())),
                ],
                false,
                true,
                Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Order Capture', 0, $exception);
        }
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderCapture[]
     *
     * @throws PsCheckoutException
     */
    public function getPayPalOrderCaptures($payPalOrderId)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from(PayPalOrderCapture::TABLE, 'c');
            $query->where(sprintf('c.`id_order` = "%s"', pSQL($payPalOrderId)));
            $queryResult = $this->db->executeS($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while retrieve PayPal Order Capture', 0, $exception);
        }

        if (empty($queryResult)) {
            throw new PsCheckoutException('PayPal Order not found');
        }

        return array_map(function ($capture) {
            return new PayPalOrderCapture(
                $capture['id'],
                $capture['id_order'],
                $capture['status'],
                $capture['final_capture'],
                $capture['created_at'],
                $capture['updated_at'],
                json_decode($capture['seller_protection'], true),
                json_decode($capture['seller_receivable_breakdown'], true)
            );
        }, $queryResult);
    }

    /**
     * @param PayPalOrderRefund $payPalOrderRefund
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function savePayPalOrderRefund(PayPalOrderRefund $payPalOrderRefund)
    {
        try {
            return $this->db->insert(
                PayPalOrderRefund::TABLE,
                [
                    'id' => pSQL($payPalOrderRefund->getId()),
                    'id_order' => pSQL($payPalOrderRefund->getIdOrder()),
                    'status' => pSQL($payPalOrderRefund->getStatus()),
                    'invoice_id' => pSQL($payPalOrderRefund->getStatus()),
                    'custom_id' => pSQL($payPalOrderRefund->getCustomId()),
                    'acquirer_reference_number' => pSQL($payPalOrderRefund->getAcquirerReferenceNumber()),
                    'seller_payable_breakdown' => pSQL(json_encode($payPalOrderRefund->getSellerPayableBreakdown())),
                    'id_order_slip' => (int) $payPalOrderRefund->getIdOrderSlip(),
                ],
                false,
                true,
                Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Order Refund', 0, $exception);
        }
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderRefund[]
     *
     * @throws PsCheckoutException
     */
    public function getPayPalOrderRefunds($payPalOrderId)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from(PayPalOrderRefund::TABLE, 'r');
            $query->where(sprintf('r.`id_order` = "%s"', pSQL($payPalOrderId)));
            $queryResult = $this->db->executeS($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while retrieve PayPal Order Refund', 0, $exception);
        }

        if (empty($queryResult)) {
            throw new PsCheckoutException('PayPal Order not found');
        }

        return array_map(function ($refund) {
            return new PayPalOrderRefund(
                $refund['id'],
                $refund['id_order'],
                $refund['status'],
                $refund['invoice_id'],
                $refund['custom_id'],
                $refund['acquirer_reference_number'],
                json_decode($refund['seller_payable_breakdown'], true),
                $refund['id_order_slip']
            );
        }, $queryResult);
    }

    /**
     * @param PayPalOrderPurchaseUnit $payPalOrderPurchaseUnit
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function savePayPalOrderPurchaseUnit(PayPalOrderPurchaseUnit $payPalOrderPurchaseUnit)
    {
        try {
            return $this->db->insert(
                PayPalOrderPurchaseUnit::TABLE,
                [
                    'id_order' => pSQL($payPalOrderPurchaseUnit->getIdOrder()),
                    'checksum' => pSQL($payPalOrderPurchaseUnit->getChecksum()),
                    'reference_id' => pSQL($payPalOrderPurchaseUnit->getReferenceId()),
                    'items' => pSQL(json_encode($payPalOrderPurchaseUnit->getItems())),
                ],
                false,
                true,
                Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Order Purchase Unit', 0, $exception);
        }
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderPurchaseUnit[]
     *
     * @throws PsCheckoutException
     */
    public function getPayPalOrderPurchaseUnits($payPalOrderId)
    {
        try {
            $query = new DbQuery();
            $query->select('*');
            $query->from(PayPalOrderPurchaseUnit::TABLE, 'p');
            $query->where(sprintf('p.`id_order` = "%s"', pSQL($payPalOrderId)));
            $queryResult = $this->db->executeS($query);
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while retrieve PayPal Order Purchase Unit', 0, $exception);
        }

        if (empty($queryResult)) {
            throw new PsCheckoutException('PayPal Order not found');
        }

        return array_map(function ($purchaseUnit) {
            return new PayPalOrderPurchaseUnit(
                $purchaseUnit['id_order'],
                $purchaseUnit['checksum'],
                $purchaseUnit['reference_id'],
                json_decode($purchaseUnit['items'], true)
            );
        }, $queryResult);
    }
}
