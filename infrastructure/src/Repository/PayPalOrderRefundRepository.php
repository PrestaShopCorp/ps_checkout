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
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderRefund;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderRefundRepositoryInterface;

class PayPalOrderRefundRepository implements PayPalOrderRefundRepositoryInterface
{
    const TABLE_NAME = 'pscheckout_refund';

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
    public function save(PayPalOrderRefund $payPalOrderRefund)
    {
        try {
            return $this->db->insert(
                self::TABLE_NAME,
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
                \Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Order Refund', 0, $exception);
        }
    }
}
