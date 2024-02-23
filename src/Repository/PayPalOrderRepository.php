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
     * @param string $orderId
     *
     * @return PayPalOrder
     *
     * @throws EntityNotFoundException
     */
    public function getOrderById($orderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_ORDER, 'o')
            ->where('o.`id_order` = ' . pSQL($orderId));
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
    public function getOrderByCartId($cartId)
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
                'funding_source' => $payPalOrder->getFundingSource(),
                'status' => $payPalOrder->getStatus(),
                'payment_source' => $payPalOrder->getPaymentSource(),
            ],
            '`id` = ' . pSQL($payPalOrder->getId())
        );
    }

    /**
     * @param $orderId
     *
     * @return PayPalOrderAuthorization[]
     *
     * @throws EntityNotFoundException
     * @throws PrestaShopDatabaseException
     */
    public function getAuthorizations($orderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_AUTHORIZATION, 'a')
            ->where('a.`id_order` = ' . pSQL($orderId));
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
                'status' => $payPalOrderAuthorization->getStatus(),
                'expiration_time' => $payPalOrderAuthorization->getExpirationTime(),
                'seller_protection' => $payPalOrderAuthorization->getSellerProtection(),
            ],
            '`id` = ' . pSQL($payPalOrderAuthorization->getId())
        );
    }

    /**
     * @param $orderId
     *
     * @return PayPalOrderCapture[]
     *
     * @throws EntityNotFoundException
     * @throws PrestaShopDatabaseException
     */
    public function getCaptures($orderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_CAPTURE, 'c')
            ->where('c.`id_order` = ' . pSQL($orderId));
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
                $capture['updated_at']
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
                'status' => $payPalOrderCapture->getStatus(),
                'final_capture' => $payPalOrderCapture->getFinalCapture(),
                'created_at' => $payPalOrderCapture->getCreatedAt(),
                'updated_at' => $payPalOrderCapture->getUpdatedAt(),
            ],
            '`id` = ' . pSQL($payPalOrderCapture->getId())
        );
    }

    /**
     * @param string $orderId
     *
     * @return PayPalOrderRefund[]
     *
     * @throws EntityNotFoundException
     * @throws PrestaShopDatabaseException
     */
    public function getRefunds($orderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_REFUND, 'r')
            ->where('r.`id_order` = ' . pSQL($orderId));
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
                $refund['seller_payable_breakdown']
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
                'status' => $payPalOrderRefund->getStatus(),
                'invoice_id' => $payPalOrderRefund->getInvoiceId(),
                'custom_id' => $payPalOrderRefund->getCustomId(),
                'acquirer_reference_number' => $payPalOrderRefund->getAcquirerReferenceNumber(),
                'seller_payable_breakdown' => $payPalOrderRefund->getSellerPayableBreakdown(),
            ],
            '`id` = ' . pSQL($payPalOrderRefund->getId())
        );
    }

    /**
     * @param $orderId
     *
     * @return PayPalOrderPurchaseUnit[]
     *
     * @throws EntityNotFoundException
     * @throws PrestaShopDatabaseException
     */
    public function getPurchaseUnits($orderId)
    {
        $query = new DbQuery();
        $query->select('*')
            ->from(self::TABLE_PURCHASE_UNIT, 'p')
            ->where('p.`id_order` = ' . pSQL($orderId));
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
