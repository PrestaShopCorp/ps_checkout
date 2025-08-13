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

use Exception;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderCapture;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderCaptureRepositoryInterface;

class PayPalOrderCaptureRepository implements PayPalOrderCaptureRepositoryInterface
{
    const TABLE_NAME = 'pscheckout_capture';

    /**
     * @var \Db
     */
    private $db;

    public function __construct(\Db $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function save(PayPalOrderCapture $payPalOrderCapture)
    {
        try {
            return $this->db->insert(
                self::TABLE_NAME,
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
                \Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Order Capture', 0, $exception);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getByOrderId(string $payPalOrderId): array
    {
        try {
            $query = new \DbQuery();
            $query->select('*');
            $query->from(self::TABLE_NAME);
            $query->where('id_order = "' . pSQL($payPalOrderId) . '"');
            
            $capturesData = $this->db->executeS($query);
            
            if (empty($capturesData)) {
                return [];
            }
            
            $captures = [];
            foreach ($capturesData as $captureData) {
                $captures[] = $this->buildPayPalOrderCapture($captureData);
            }
            
            return $captures;
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while getting PayPal Order Captures', 0, $exception);
        }
    }

    /**
     * Build PayPalOrderCapture entity from database data
     *
     * @param array $captureData
     *
     * @return PayPalOrderCapture
     */
    private function buildPayPalOrderCapture(array $captureData): PayPalOrderCapture
    {
        return new PayPalOrderCapture(
            $captureData['id'],
            $captureData['id_order'],
            $captureData['status'],
            $captureData['created_at'],
            $captureData['updated_at'],
            json_decode($captureData['seller_protection'], true) ?: [],
            json_decode($captureData['seller_receivable_breakdown'], true) ?: [],
            (bool) $captureData['final_capture']
        );
    }
}
