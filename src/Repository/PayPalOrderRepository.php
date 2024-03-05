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
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrder;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderAuthorization;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderCapture;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderPurchaseUnit;
use PrestaShop\Module\PrestashopCheckout\Entity\PayPalOrderRefund;
use PrestaShop\PrestaShop\Core\Foundation\Database\EntityNotFoundException;
use PrestaShopDatabaseException;

class PayPalOrderRepository
{
    const TABLE_ORDER = 'pscheckout_order';
    const TABLE_CAPTURE = 'pscheckout_capture';
    const TABLE_REFUND = 'pscheckout_refund';
    const TABLE_AUTHORIZATION = 'pscheckout_authorization';
    const TABLE_PURCHASE_UNIT = 'pscheckout_purchase_unit';

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
            self::TABLE_ORDER,
            [
                'id' => pSQL($payPalOrder->getId()),
                'id_cart' => (int) $payPalOrder->getIdCart(),
                'funding_source' => pSQL($payPalOrder->getFundingSource()),
                'status' => pSQL($payPalOrder->getStatus()),
                'payment_source' => pSQL($payPalOrder->getPaymentSource()),
                'environment' => pSQL($payPalOrder->getEnvironment()),
                'is_card_fields' => $payPalOrder->isCardFields(),
                'is_express_checkout' => $payPalOrder->isExpressCheckout(),
            ]
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrder
     *
     * @throws EntityNotFoundException
     */
    public function getPayPalOrderById($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_ORDER, 'o')
            ->where('o.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->getRow($query);
        if (!$queryResult) {
            throw new EntityNotFoundException('PayPal Order not found');
        }

        return new PayPalOrder($queryResult['id'], (int) $queryResult['id_cart'], $queryResult['funding_source'], $queryResult['status'], $queryResult['payment_source']);
    }

    /**
     * @param int $cartId
     *
     * @return PayPalOrder
     *
     * @throws EntityNotFoundException
     */
    public function getPayPalOrderByCartId($cartId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_ORDER, 'o')
            ->where('o.`id_cart` = ' . (int) $cartId);
        $queryResult = $this->db->getRow($query);
        if (!$queryResult) {
            throw new EntityNotFoundException('PayPal Order not found');
        }

        return new PayPalOrder($queryResult['id'], (int) $queryResult['id_cart'], $queryResult['funding_source'], $queryResult['status'], $queryResult['payment_source']);
    }

    /**
     * @param PayPalOrder $payPalOrder
     *
     * @return bool
     */
    public function updatePayPalOrder(PayPalOrder $payPalOrder)
    {
        return $this->db->update(
            self::TABLE_ORDER,
            [
                'funding_source' => pSQL($payPalOrder->getFundingSource()),
                'status' => pSQL($payPalOrder->getStatus()),
                'payment_source' => pSQL($payPalOrder->getPaymentSource()),
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
        $sql = 'DELETE FROM `' . _DB_PREFIX_ . self::TABLE_ORDER . "` WHERE `id` = $orderId;"
            . 'DELETE FROM `' . _DB_PREFIX_ . self::TABLE_AUTHORIZATION . "` WHERE `id_order` = $orderId;"
            . 'DELETE FROM `' . _DB_PREFIX_ . self::TABLE_REFUND . "` WHERE `id_order` = $orderId;"
            . 'DELETE FROM `' . _DB_PREFIX_ . self::TABLE_CAPTURE . "` WHERE `id_order` = $orderId;"
            . 'DELETE FROM `' . _DB_PREFIX_ . self::TABLE_PURCHASE_UNIT . "` WHERE `id_order` = $orderId;";

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
            self::TABLE_AUTHORIZATION,
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
     * @throws EntityNotFoundException
     * @throws PrestaShopDatabaseException
     */
    public function getPayPalOrderAuthorizations($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_AUTHORIZATION, 'a')
            ->where('a.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->executeS($query);
        if (!$queryResult) {
            throw new EntityNotFoundException('PayPal Order not found');
        }

        if (array_key_exists(0, $queryResult)) {
            $queryResult = [$queryResult];
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
            self::TABLE_AUTHORIZATION,
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
            self::TABLE_CAPTURE,
            [
                'id' => pSQL($payPalOrderCapture->getId()),
                'id_order' => pSQL($payPalOrderCapture->getIdOrder()),
                'status' => pSQL($payPalOrderCapture->getStatus()),
                'final_capture' => (bool) $payPalOrderCapture->getFinalCapture(),
                'created_at' => pSQL($payPalOrderCapture->getCreatedAt()),
                'updated_at' => pSQL($payPalOrderCapture->getUpdatedAt()),
                'seller_protection' => pSQL($payPalOrderCapture->getSellerProtection()),
                'seller_receivable_breakdown' => pSQL($payPalOrderCapture->getSellerReceivableBreakdown()),
            ]
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderCapture[]
     *
     * @throws EntityNotFoundException
     * @throws PrestaShopDatabaseException
     */
    public function getPayPalOrderCaptures($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_CAPTURE, 'c')
            ->where('c.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->executeS($query);
        if (!$queryResult) {
            throw new EntityNotFoundException('PayPal Order not found');
        }

        if (array_key_exists(0, $queryResult)) {
            $queryResult = [$queryResult];
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
                $capture['seller_receivable_breakdown']
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
            self::TABLE_CAPTURE,
            [
                'status' => pSQL($payPalOrderCapture->getStatus()),
                'final_capture' => pSQL($payPalOrderCapture->getFinalCapture()),
                'created_at' => pSQL($payPalOrderCapture->getCreatedAt()),
                'updated_at' => pSQL($payPalOrderCapture->getUpdatedAt()),
                'seller_protection' => pSQL($payPalOrderCapture->getSellerProtection()),
                'seller_receivable_breakdown' => pSQL($payPalOrderCapture->getSellerReceivableBreakdown()),
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
            self::TABLE_REFUND,
            [
                'id' => pSQL($payPalOrderRefund->getId()),
                'id_order' => pSQL($payPalOrderRefund->getIdOrder()),
                'status' => pSQL($payPalOrderRefund->getStatus()),
                'invoice_id' => pSQL($payPalOrderRefund->getStatus()),
                'custom_id' => pSQL($payPalOrderRefund->getCustomId()),
                'acquirer_reference_number' => pSQL($payPalOrderRefund->getAcquirerReferenceNumber()),
                'seller_payable_breakdown' => pSQL($payPalOrderRefund->getSellerPayableBreakdown()),
                'id_order_slip' => (int) $payPalOrderRefund->getIdOrderSlip(),
            ]
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderRefund[]
     *
     * @throws EntityNotFoundException
     * @throws PrestaShopDatabaseException
     */
    public function getPayPalOrderRefunds($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_REFUND, 'r')
            ->where('r.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->executeS($query);
        if (!$queryResult) {
            throw new EntityNotFoundException('PayPal Order not found');
        }
        if (array_key_exists(0, $queryResult)) {
            $queryResult = [$queryResult];
        }

        return array_map(function ($refund) {
            return new PayPalOrderRefund(
                $refund['id'],
                $refund['id_order'],
                $refund['status'],
                $refund['invoice_id'],
                $refund['custom_id'],
                $refund['acquirer_reference_number'],
                $refund['seller_payable_breakdown'],
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
            self::TABLE_REFUND,
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
            self::TABLE_REFUND,
            [
                'id_order' => pSQL($payPalOrderPurchaseUnit->getIdOrder()),
                'checksum' => pSQL($payPalOrderPurchaseUnit->getChecksum()),
                'reference_id' => pSQL($payPalOrderPurchaseUnit->getReferenceId()),
                'items' => pSQL($payPalOrderPurchaseUnit->getItems()),
            ]
        );
    }

    /**
     * @param string $payPalOrderId
     *
     * @return PayPalOrderPurchaseUnit[]
     *
     * @throws EntityNotFoundException
     * @throws PrestaShopDatabaseException
     */
    public function getPayPalOrderPurchaseUnits($payPalOrderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_PURCHASE_UNIT, 'p')
            ->where('p.`id_order` = ' . pSQL($payPalOrderId));
        $queryResult = $this->db->executeS($query);
        if (!$queryResult) {
            throw new EntityNotFoundException('PayPal Order not found');
        }
        if (array_key_exists(0, $queryResult)) {
            $queryResult = [$queryResult];
        }

        return array_map(function ($purchaseUnit) {
            return new PayPalOrderPurchaseUnit(
                $purchaseUnit['id_order'],
                $purchaseUnit['checksum'],
                $purchaseUnit['reference_id'],
                $purchaseUnit['items']
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
            self::TABLE_PURCHASE_UNIT,
            [
                'checksum' => $payPalOrderPurchaseUnit->getChecksum(),
                'items' => $payPalOrderPurchaseUnit->getItems(),
            ],
            '`id_order` = ' . pSQL($payPalOrderPurchaseUnit->getIdOrder() . ' AND `reference_id` = ' . $payPalOrderPurchaseUnit->getReferenceId())
        );
    }
}
