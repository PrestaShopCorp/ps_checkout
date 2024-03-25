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
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderAuthorization;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderCapture;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderPurchaseUnit;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderRefund;
use PrestaShop\Module\PrestashopCheckout\PayPal\Order\ValueObject\PayPalOrderId;
use PrestaShopDatabaseException;

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
     * @throws PrestaShopDatabaseException
     */
    public function createPayPalOrder(PayPalOrder $payPalOrder)
    {
        return $this->db->insert(
            PayPalOrder::TABLE,
            [
                'id' => pSQL($payPalOrder->getId()),
                'id_cart' => (int) $payPalOrder->getIdCart(),
                'status' => pSQL($payPalOrder->getStatus()),
                'intent' => pSQL($payPalOrder->getIntent()),
                'funding_source' => pSQL($payPalOrder->getFundingSource()),
                'payment_source' => json_encode($payPalOrder->getPaymentSource()),
                'environment' => pSQL($payPalOrder->getEnvironment()),
                'is_card_fields' => $payPalOrder->isCardFields(),
                'is_express_checkout' => $payPalOrder->isExpressCheckout(),
                'customer_intent' => pSQL($payPalOrder->getCustomerIntent()),
            ]
        );
    }

    /**
     * @param PayPalOrderId $payPalOrderId
     *
     * @return PayPalOrder
     *
     * @throws Exception
     */
    public function getPayPalOrderById(PayPalOrderId $payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(PayPalOrder::TABLE, 'o')
            ->where('o.`id_order` = "' . pSQL($payPalOrderId->getValue()) . '"');
        $queryResult = $this->db->getRow($query);

        if (!$queryResult) {
            throw new Exception('PayPal Order not found');
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
            $queryResult['customer_intent']
        );
    }

    /**
     * @param int $cartId
     *
     * @return PayPalOrder
     *
     * @throws Exception
     */
    public function getPayPalOrderByCartId($cartId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(PayPalOrder::TABLE, 'o')
            ->where('o.`id_cart` = ' . (int) $cartId);
        $queryResult = $this->db->getRow($query);
        if (!$queryResult) {
            throw new Exception('PayPal Order not found');
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
            $queryResult['customer_intent']
        );
    }

    /**
     * @param PayPalOrder $payPalOrder
     *
     * @return bool
     */
    public function updatePayPalOrder(PayPalOrder $payPalOrder)
    {
        return $this->db->update(
            PayPalOrder::TABLE,
            [
                'funding_source' => pSQL($payPalOrder->getFundingSource()),
                'status' => pSQL($payPalOrder->getStatus()),
                'payment_source' => pSQL(json_encode($payPalOrder->getPaymentSource())),
                'is_card_fields' => $payPalOrder->isCardFields(),
                'is_express_checkout' => $payPalOrder->isExpressCheckout(),
            ],
            '`id` = ' . pSQL($payPalOrder->getId())
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return bool
     */
    public function deletePayPalOrder($payPalOrderId)
    {
        $orderId = pSQL($payPalOrderId);
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . PayPalOrder::TABLE . "` WHERE `id` = $orderId;"
            . 'DELETE FROM `' . _DB_PREFIX_ . PayPalOrderAuthorization::TABLE . "` WHERE `id_order` = $orderId;"
            . 'DELETE FROM `' . _DB_PREFIX_ . PayPalOrderRefund::TABLE . "` WHERE `id_order` = $orderId;"
            . 'DELETE FROM `' . _DB_PREFIX_ . PayPalOrderCapture::TABLE . "` WHERE `id_order` = $orderId;"
            . 'DELETE FROM `' . _DB_PREFIX_ . PayPalOrderPurchaseUnit::TABLE . "` WHERE `id_order` = $orderId;";

        return $this->db->execute($sql);
    }

    /**
     * @param PayPalOrderAuthorization $payPalOrderAuthorization
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function createPayPalOrderAuthorization(PayPalOrderAuthorization $payPalOrderAuthorization)
    {
        return $this->db->insert(
            PayPalOrderAuthorization::TABLE,
            [
                'id' => pSQL($payPalOrderAuthorization->getId()),
                'id_order' => pSQL($payPalOrderAuthorization->getIdOrder()),
                'status' => pSQL($payPalOrderAuthorization->getStatus()),
                'expiration_time' => pSQL($payPalOrderAuthorization->getExpirationTime()),
                'seller_protection' => pSQL($payPalOrderAuthorization->getSellerProtection()),
            ]
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderAuthorization[]
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    public function getPayPalOrderAuthorizations($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(PayPalOrderAuthorization::TABLE, 'a')
            ->where('a.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->executeS($query);
        if (!$queryResult) {
            throw new Exception('PayPal Order not found');
        }

        return array_map(function ($authorization) {
            return new PayPalOrderAuthorization(
                $authorization['id'],
                $authorization['id_order'],
                $authorization['status'],
                $authorization['expiration_time'],
                $authorization['seller_protection']
            );
        }, $queryResult);
    }

    /**
     * @param PayPalOrderAuthorization $payPalOrderAuthorization
     *
     * @return bool
     */
    public function updateAuthorization(PayPalOrderAuthorization $payPalOrderAuthorization)
    {
        return $this->db->update(
            PayPalOrderAuthorization::TABLE,
            [
                'status' => pSQL($payPalOrderAuthorization->getStatus()),
                'expiration_time' => pSQL($payPalOrderAuthorization->getExpirationTime()),
                'seller_protection' => pSQL($payPalOrderAuthorization->getSellerProtection()),
            ],
            '`id` = ' . pSQL($payPalOrderAuthorization->getId())
        );
    }

    /**
     * @param PayPalOrderCapture $payPalOrderCapture
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function createPayPalOrderCapture(PayPalOrderCapture $payPalOrderCapture)
    {
        return $this->db->insert(
            PayPalOrderCapture::TABLE,
            [
                'id' => pSQL($payPalOrderCapture->getId()),
                'id_order' => pSQL($payPalOrderCapture->getIdOrder()),
                'status' => pSQL($payPalOrderCapture->getStatus()),
                'final_capture' => (bool) $payPalOrderCapture->getFinalCapture(),
                'created_at' => pSQL($payPalOrderCapture->getCreatedAt()),
                'updated_at' => pSQL($payPalOrderCapture->getUpdatedAt()),
                'seller_protection' => pSQL($payPalOrderCapture->getSellerProtection()),
                'seller_receivable_breakdown' => pSQL(json_encode($payPalOrderCapture->getSellerReceivableBreakdown())),
            ]
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderCapture[]
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    public function getPayPalOrderCaptures($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(PayPalOrderCapture::TABLE, 'c')
            ->where('c.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->executeS($query);
        if (!$queryResult) {
            throw new Exception('PayPal Order not found');
        }

        return array_map(function ($capture) {
            return new PayPalOrderCapture(
                $capture['id'],
                $capture['id_order'],
                $capture['status'],
                $capture['final_capture'],
                $capture['created_at'],
                $capture['updated_at'],
                $capture['seller_protection'],
                json_decode($capture['seller_receivable_breakdown'], true)
            );
        }, $queryResult);
    }

    /**
     * @param PayPalOrderCapture $payPalOrderCapture
     *
     * @return bool
     */
    public function updateCapture(PayPalOrderCapture $payPalOrderCapture)
    {
        return $this->db->update(
            PayPalOrderCapture::TABLE,
            [
                'status' => pSQL($payPalOrderCapture->getStatus()),
                'final_capture' => pSQL($payPalOrderCapture->getFinalCapture()),
                'created_at' => pSQL($payPalOrderCapture->getCreatedAt()),
                'updated_at' => pSQL($payPalOrderCapture->getUpdatedAt()),
                'seller_protection' => pSQL($payPalOrderCapture->getSellerProtection()),
                'seller_receivable_breakdown' => pSQL(json_encode($payPalOrderCapture->getSellerReceivableBreakdown())),
            ],
            '`id` = ' . pSQL($payPalOrderCapture->getId())
        );
    }

    /**
     * @param PayPalOrderRefund $payPalOrderRefund
     *
     * @return bool
     *
     * @throws PrestaShopDatabaseException
     */
    public function createPayPalOrderRefund(PayPalOrderRefund $payPalOrderRefund)
    {
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
            ]
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderRefund[]
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    public function getPayPalOrderRefunds($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(PayPalOrderRefund::TABLE, 'r')
            ->where('r.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->executeS($query);
        if (!$queryResult) {
            throw new Exception('PayPal Order not found');
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
     * @param PayPalOrderRefund $payPalOrderRefund
     *
     * @return bool
     */
    public function updateRefund(PayPalOrderRefund $payPalOrderRefund)
    {
        return $this->db->update(
            PayPalOrderRefund::TABLE,
            [
                'status' => pSQL($payPalOrderRefund->getStatus()),
                'invoice_id' => pSQL($payPalOrderRefund->getInvoiceId()),
                'custom_id' => pSQL($payPalOrderRefund->getCustomId()),
                'acquirer_reference_number' => pSQL($payPalOrderRefund->getAcquirerReferenceNumber()),
                'seller_payable_breakdown' => pSQL($payPalOrderRefund->getSellerPayableBreakdown()),
            ],
            '`id` = ' . pSQL($payPalOrderRefund->getId())
        );
    }

    public function createPayPalOrderPurchaseUnit(PayPalOrderPurchaseUnit $payPalOrderPurchaseUnit)
    {
        return $this->db->insert(
            PayPalOrderRefund::TABLE,
            [
                'id_order' => pSQL($payPalOrderPurchaseUnit->getIdOrder()),
                'checksum' => pSQL($payPalOrderPurchaseUnit->getChecksum()),
                'reference_id' => pSQL($payPalOrderPurchaseUnit->getReferenceId()),
                'items' => pSQL(json_encode($payPalOrderPurchaseUnit->getItems())),
            ]
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderPurchaseUnit[]
     *
     * @throws Exception
     * @throws PrestaShopDatabaseException
     */
    public function getPayPalOrderPurchaseUnits($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(PayPalOrderPurchaseUnit::TABLE, 'p')
            ->where('p.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->executeS($query);
        if (!$queryResult) {
            throw new Exception('PayPal Order not found');
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

    /**
     * @param PayPalOrderPurchaseUnit $payPalOrderPurchaseUnit
     *
     * @return bool
     */
    public function updatePurchaseUnit(PayPalOrderPurchaseUnit $payPalOrderPurchaseUnit)
    {
        return $this->db->update(
            PayPalOrderPurchaseUnit::TABLE,
            [
                'checksum' => $payPalOrderPurchaseUnit->getChecksum(),
                'items' => $payPalOrderPurchaseUnit->getItems(),
            ],
            '`id_order` = ' . pSQL($payPalOrderPurchaseUnit->getIdOrder() . ' AND `reference_id` = ' . $payPalOrderPurchaseUnit->getReferenceId())
        );
    }
}
