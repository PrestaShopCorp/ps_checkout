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

use Db;
use DbQuery;
use Exception;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\PayPal\Customer\Repository\PayPalCustomerRepositoryInterface;

class PayPalCustomerRepository implements PayPalCustomerRepositoryInterface
{
    const TABLE_NAME = 'pscheckout_customer';

    /**
     * @var \Db
     */
    private $db;

    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * {@inheritDoc}
     */
    public function getPayPalCustomerIdByCustomerId(int $customerId)
    {
        try {
            $query = new DbQuery();
            $query->select('`paypal_customer_id`');
            $query->from(self::TABLE_NAME);
            $query->where(sprintf('`id_customer` = %d', (int) $customerId));
            $payPalCustomerId = $this->db->getValue($query);

            return $payPalCustomerId ?? null;
        } catch (Exception $exception) {
            throw new PsCheckoutException('Failed to find PayPal Customer ID', 0, $exception);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function save(int $customerId, string $paypalCustomerId)
    {
        try {
            $this->db->insert(
                self::TABLE_NAME,
                [
                    'id_customer' => $customerId,
                    'paypal_customer_id' => pSQL($paypalCustomerId),
                ],
                false,
                true,
                \Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Failed to save PayPal Customer ID', 0, $exception);
        }
    }
}
