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
use PrestaShop\Module\PrestashopCheckout\Customer\ValueObject\CustomerId;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Customer\ValueObject\PayPalCustomerId;

class PayPalCustomerRepository
{
    /**
     * @var Db
     */
    private $db;

    /**
     * @param Db $db
     */
    public function __construct(Db $db)
    {
        $this->db = $db;
    }

    /**
     * @param CustomerId $customerId
     *
     * @return PayPalCustomerId|null
     *
     * @throws PsCheckoutException
     */
    public function findPayPalCustomerIdByCustomerId(CustomerId $customerId)
    {
        try {
            $query = new DbQuery();
            $query->select('`paypal_customer_id`');
            $query->from('pscheckout_customer');
            $query->where(sprintf('`id_customer` = %d', (int) $customerId->getValue()));
            $customerIdPayPal = $this->db->getValue($query);

            return $customerIdPayPal ? new PayPalCustomerId($customerIdPayPal) : null;
        } catch (Exception $exception) {
            throw new PsCheckoutException('Failed to find PayPal Customer ID', 0, $exception);
        }
    }

    /**
     * @param CustomerId $customerId
     * @param PayPalCustomerId $paypalCustomerId
     *
     * @return void
     *
     * @throws PsCheckoutException
     */
    public function save(CustomerId $customerId, PayPalCustomerId $paypalCustomerId)
    {
        try {
            $this->db->insert(
            'pscheckout_customer',
            [
                'id_customer' => (int) $customerId->getValue(),
                'paypal_customer_id' => pSQL($paypalCustomerId->getValue()),
            ]
        );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Failed to save PayPal Customer ID', 0, $exception);
        }
    }
}
