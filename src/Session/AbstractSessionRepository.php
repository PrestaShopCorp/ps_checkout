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

use Ramsey\Uuid\Uuid;

class AbstractSessionRepository implements SessionRepositoryInterface
{
    protected $table;
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
        $data = isset($sessionData['data']) ? $sessionData['data'] : null;
        $creationDate = date('Y-m-d H:i:s');
        $expirationDate = key_exists('expiration_date', $sessionData) && empty($sessionData['expiration_date']) ?
            null :
            SessionHelper::updateExpirationDate($creationDate);
        $isSSEOpened = isset($sessionData['is_sse_opened']) ? (int) $sessionData['is_sse_opened'] : 0;
        $authToken = isset($sessionData['auth_token']) ? pSQL($sessionData['auth_token']) : null;

        $insertData = [
            'correlation_id' => Uuid::uuid4()->toString(),
            'user_id' => $sessionData['user_id'],
            'shop_id' => $sessionData['shop_id'],
            'is_closed' => (int) $sessionData['is_closed'],
            'auth_token' => $authToken,
            'status' => pSQL($sessionData['status']),
            'created_at' => pSQL($creationDate),
            'updated_at' => pSQL($creationDate),
            'closed_at' => '0000-00-00 00:00:00',
            'expires_at' => pSQL($expirationDate),
            'is_sse_opened' => $isSSEOpened,
            'data' => $data,
        ];

        return $this->db->insert($this->table, $insertData, true);
    }

    /**
     * Get an user session by unique constraint (user_id, shop_uuid, process_type)
     *
     * @param array $sessionData
     *
     * @return \PrestaShop\Module\PrestashopCheckout\Session\Session|null
     */
    public function get(array $sessionData)
    {
        $query = '
            SELECT *
            FROM ' . _DB_PREFIX_ . $this->table .'
            WHERE user_id = ' . $sessionData['user_id'] . '
            AND shop_id = ' . $sessionData['shop_id'] . '
            AND is_closed = ' . (int) $sessionData['is_closed'] . ';
        ';
        $result = $this->db->getRow($query);

        return $result ? new Session($result) : null;
    }

    /**
     * Update an user session
     *
     * @param \PrestaShop\Module\PrestashopCheckout\Session\Session $session
     *
     * @return bool
     */
    public function update(Session $session)
    {
        $data = [
            'status' => pSQL($session->getStatus()),
            'data' => pSQL($session->getData()),
            'expires_at' => pSQL($session->getExpiresAt()),
            'is_sse_opened' => 1 === (int) $session->getIsSSEOpened(),
            'updated_at' => date('Y-m-d H:i:s'),
            'auth_token' => $session->getAuthToken(),
        ];
        $where = '
            user_id = ' . $session->getUserId() . '
            AND shop_id = ' . $session->getShopId() . '
            AND is_closed = ' . $session->getIsClosed();

        return $this->db->update($this->table, $data, $where, 1, true);
    }

    /**
     * Remove an user session
     *
     * @param int $userId
     * @param int $shopId
     * @param int $isClosed
     *
     * @return bool
     */
    public function remove($userId, $shopId, $isClosed)
    {
        $where = '
            user_id = ' . $userId . '
            AND shop_id = ' . $shopId . '
            AND is_closed = ' . (int) $isClosed
        ;

        return $this->db->delete($this->table, $where);
    }

    /**
     * Remove an user session
     *
     * @param int $userId
     * @param int $shopId
     * @param int $isClosed
     *
     * @return bool
     */
    public function close($userId, $shopId, $isClosed)
    {
        $this->db->execute(
            'UPDATE `' . _DB_PREFIX_ . $this->table . '`
            SET `is_closed` = `is_closed` + 1
            WHERE `is_closed` > 0
            ORDER BY `is_closed` DESC'
        );

        $where = '
            user_id = ' . $userId . '
            AND shop_id = ' . $shopId . '
            AND is_closed = ' . (int) $isClosed
        ;
        $data = [
            'is_closed' => 1,
            'closed_at' => date('Y-m-d H:i:s'),
        ];
        return $this->db->update($this->table, $data, $where, 1, true);
    }
}
