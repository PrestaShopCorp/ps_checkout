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
use PsCheckout\Core\PayPal\Order\Entity\PayPalOrderAuthorization;
use PsCheckout\Core\PayPal\Order\Repository\PayPalOrderAuthorizationRepositoryInterface;

class PayPalOrderAuthorizationRepository implements PayPalOrderAuthorizationRepositoryInterface
{
    const TABLE_NAME = 'pscheckout_authorization';

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
    public function save(PayPalOrderAuthorization $payPalOrderAuthorization)
    {
        try {
            return $this->db->insert(
                self::TABLE_NAME,
                [
                    'id' => pSQL($payPalOrderAuthorization->getId()),
                    'id_order' => pSQL($payPalOrderAuthorization->getIdOrder()),
                    'status' => pSQL($payPalOrderAuthorization->getStatus()),
                    'expiration_time' => pSQL($payPalOrderAuthorization->getExpirationTime()),
                    'seller_protection' => pSQL(json_encode($payPalOrderAuthorization->getSellerProtection())),
                ],
                false,
                true,
                \Db::REPLACE
            );
        } catch (Exception $exception) {
            throw new PsCheckoutException('Error while saving PayPal Order Authorization', 0, $exception);
        }
    }
}
