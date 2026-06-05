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

use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Core\WebhookDispatcher\Repository\WebhookEventRepositoryInterface;

class WebhookEventRepository implements WebhookEventRepositoryInterface
{
    const TABLE_NAME = 'pscheckout_webhook_event';

    /**
     * Minutes after which a "processing" row is considered stale and may be reclaimed.
     * Covers PHP-FPM crashes and hard timeouts.
     */
    const STALE_PROCESSING_THRESHOLD_MINUTES = 10;

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
    public function claim(string $webhookId, string $eventType, string $resourceId): bool
    {
        // Use PHP time throughout so all timestamps share one controllable source of truth,
        // consistent with markSucceeded() / markFailed() which already use date().
        $now = date('Y-m-d H:i:s');
        $staleThreshold = date('Y-m-d H:i:s', time() - self::STALE_PROCESSING_THRESHOLD_MINUTES * 60);

        // Step 1: try to insert a new row (new event path).
        $inserted = $this->db->insert(
            self::TABLE_NAME,
            [
                'id' => pSQL($webhookId),
                'event_type' => pSQL($eventType),
                'resource_id' => pSQL($resourceId),
                'status' => 'processing',
                'date_add' => $now,
                'date_upd' => $now,
            ],
            false,
            true,
            \Db::INSERT_IGNORE
        );

        // INSERT IGNORE returns false only on a real DB error (not on a duplicate key).
        // A duplicate key silently returns true with Affected_Rows = 0.
        if ($inserted === false) {
            throw new PsCheckoutException('WebhookEventRepository::claim INSERT failed: ' . $this->db->getMsgError());
        }

        if ($this->db->Affected_Rows() > 0) {
            return true;
        }

        // Step 2: row already exists — try to reclaim if it is failed or stale.
        // Atomic conditional UPDATE: only one concurrent caller can get Affected_Rows() = 1.
        $updated = $this->db->execute(
            'UPDATE `' . _DB_PREFIX_ . self::TABLE_NAME . '`
            SET `status` = \'processing\', `date_upd` = \'' . $now . '\'
            WHERE `id` = \'' . pSQL($webhookId) . '\'
              AND (
                  `status` = \'failed\'
                  OR (`status` = \'processing\'
                      AND `date_upd` < \'' . $staleThreshold . '\')
              )'
        );

        if ($updated === false) {
            throw new PsCheckoutException('WebhookEventRepository::claim UPDATE failed: ' . $this->db->getMsgError());
        }

        return $this->db->Affected_Rows() > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function markSucceeded(string $webhookId): void
    {
        $this->db->update(
            self::TABLE_NAME,
            [
                'status' => 'succeeded',
                'date_upd' => date('Y-m-d H:i:s'),
            ],
            '`id` = \'' . pSQL($webhookId) . '\''
        );
    }

    /**
     * {@inheritDoc}
     */
    public function markFailed(string $webhookId, string $error): void
    {
        $this->db->update(
            self::TABLE_NAME,
            [
                'status' => 'failed',
                'error' => pSQL($error, true),
                'date_upd' => date('Y-m-d H:i:s'),
            ],
            '`id` = \'' . pSQL($webhookId) . '\''
        );
    }
}
