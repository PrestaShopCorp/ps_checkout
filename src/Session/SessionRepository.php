<?php

/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Session;

use PrestaShop\Module\PrestashopCheckout\Session\Session;
use PrestaShop\Module\PrestashopCheckout\Session\SessionHelper;
use Ramsey\Uuid\Uuid;

class SessionRepository
{
    /**
     * @var \Db
     */
    private $db;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->db = \Db::getInstance();
    }

    /**
     * Save an user session
     *
     * @param array $sessionData
     *
     * @return bool
     */
    public function save(array $sessionData)
    {
        $accountId = isset($sessionData['account_id']) ? $sessionData['account_id'] : null;
        $data = isset($sessionData['data']) ? $sessionData['data'] : null;
        $creationDate = date('Y-m-d H:i:s');
        $expirationDate = SessionHelper::updateExpirationDate($creationDate);
        $insertData = [
            'user_id' => $sessionData['user_id'],
            'shop_id' => $sessionData['shop_id'],
            'process_type' => pSQL($sessionData['process_type']),
            'account_id' => pSQL($accountId),
            'correlation_id' => Uuid::uuid4()->toString(),
            'status' => pSQL($sessionData['status']),
            'data' => pSQL($data),
            'creation_date' => pSQL($creationDate),
            'expiration_date' => pSQL($expirationDate),
        ];

        return $this->db->insert('pscheckout_session', $insertData, true);
    }

    /**
     * Get an user session by unique constraint (user_id, shop_uuid, process_type)
     *
     * @param array $sessionData
     *
     * @return PrestaShop\Module\PrestashopCheckout\Session\Session|null
     */
    public function get(array $sessionData)
    {
        $query = "
            SELECT *
            FROM " . _DB_PREFIX_ . "pscheckout_session
            WHERE user_id = " . $sessionData['user_id'] . "
            AND shop_id = " . $sessionData['shop_id'] . "
            AND process_type = '" . pSQL($sessionData['process_type']) . "';
        ";
        $result = $this->db->getRow($query);

        return $result ? new Session($result) : null;
    }

    /**
     * Update an user session
     *
     * @param PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return bool
     */
    public function update(Session $session)
    {
        $data = [
            'account_id' => pSQL($session->getAccountId()),
            'status' => pSQL($session->getStatus()),
            'data' => pSQL($session->getData()),
            'expiration_date' => pSQL($session->getExpirationDate()),
        ];
        $where = "
            user_id = " . $session->getUserId() . "
            AND shop_id = " . $session->getShopId() . "
            AND process_type = '" . pSQL($session->getProcessType()) . "'
        ";

        return $this->db->update('pscheckout_session', $data, $where, 1, true);
    }

    /**
     * Remove an user session
     *
     * @param int $userId
     * @param int $shopId
     * @param string $processType
     *
     * @return bool
     */
    public function remove(int $userId, string $shopId, string $processType)
    {
        $where = "
            user_id = " . $userId . "
            AND shop_id = " . $shopId . "
            AND process_type = '" . pSQL($processType) . "'
        ";

        return $this->db->delete('pscheckout_session', $where);
    }
}
