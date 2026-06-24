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

namespace PsCheckout\Core\Tests\Integration\WebhookDispatcher\Repository;

use PsCheckout\Core\Tests\Integration\BaseTestCase;
use PsCheckout\Infrastructure\Repository\WebhookEventRepository;

/**
 * Integration tests for WebhookEventRepository::claim() atomicity and status transitions.
 *
 * These tests verify the two-step INSERT IGNORE + conditional UPDATE protocol that provides
 * event-level idempotency for PayPal webhook deliveries. Each test runs inside a DB transaction
 * that is rolled back in tearDown() by BaseTestCase, leaving no persistent test data.
 *
 * Run manually inside Docker: make php-integration-core
 */
class WebhookEventRepositoryTest extends BaseTestCase
{
    /**
     * @var WebhookEventRepository
     */
    private $repository;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        \Db::getInstance()->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_webhook_event` (
                `id` varchar(50) NOT NULL,
                `event_type` varchar(100) NOT NULL,
                `resource_id` varchar(50) NOT NULL,
                `status` varchar(20) NOT NULL DEFAULT \'processing\',
                `error` text DEFAULT NULL,
                `date_add` datetime NOT NULL,
                `date_upd` datetime NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=UTF8
        ');
    }

    public static function tearDownAfterClass(): void
    {
        \Db::getInstance()->execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'pscheckout_webhook_event`');

        parent::tearDownAfterClass();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new WebhookEventRepository(\Db::getInstance());
    }

    // -------------------------------------------------------------------------
    // claim() — five scenarios
    // -------------------------------------------------------------------------

    public function testClaimNewEventReturnsTrue(): void
    {
        $result = $this->repository->claim('wh-new-001', 'PAYMENT.CAPTURE.COMPLETED', 'order-abc');

        $this->assertTrue($result);

        $row = $this->fetchRow('wh-new-001');
        $this->assertNotNull($row);
        $this->assertSame('processing', $row['status']);
        $this->assertSame('PAYMENT.CAPTURE.COMPLETED', $row['event_type']);
        $this->assertSame('order-abc', $row['resource_id']);
    }

    public function testClaimDuplicateRecentProcessingReturnsFalse(): void
    {
        // Insert a "processing" row with date_upd = now to simulate in-flight processing
        \Db::getInstance()->insert(
            'pscheckout_webhook_event',
            [
                'id' => 'wh-dup-001',
                'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
                'resource_id' => 'order-abc',
                'status' => 'processing',
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ]
        );

        $result = $this->repository->claim('wh-dup-001', 'PAYMENT.CAPTURE.COMPLETED', 'order-abc');

        $this->assertFalse($result);
    }

    public function testClaimStaleProcessingReturnsTrue(): void
    {
        // Insert a "processing" row with date_upd > 10 minutes ago to simulate a PHP-FPM crash.
        // PHP time is used here for consistency with WebhookEventRepository::claim().
        \Db::getInstance()->insert(
            'pscheckout_webhook_event',
            [
                'id' => 'wh-stale-001',
                'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
                'resource_id' => 'order-abc',
                'status' => 'processing',
                'date_add' => date('Y-m-d H:i:s', time() - 20 * 60),
                'date_upd' => date('Y-m-d H:i:s', time() - 11 * 60),
            ]
        );

        $result = $this->repository->claim('wh-stale-001', 'PAYMENT.CAPTURE.COMPLETED', 'order-abc');

        $this->assertTrue($result);

        $row = $this->fetchRow('wh-stale-001');
        $this->assertSame('processing', $row['status']);
    }

    public function testClaimFailedReturnsTrue(): void
    {
        \Db::getInstance()->insert(
            'pscheckout_webhook_event',
            [
                'id' => 'wh-fail-001',
                'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
                'resource_id' => 'order-abc',
                'status' => 'failed',
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ]
        );

        $result = $this->repository->claim('wh-fail-001', 'PAYMENT.CAPTURE.COMPLETED', 'order-abc');

        $this->assertTrue($result);

        $row = $this->fetchRow('wh-fail-001');
        $this->assertSame('processing', $row['status']);
    }

    public function testClaimSucceededReturnsFalse(): void
    {
        \Db::getInstance()->insert(
            'pscheckout_webhook_event',
            [
                'id' => 'wh-done-001',
                'event_type' => 'PAYMENT.CAPTURE.COMPLETED',
                'resource_id' => 'order-abc',
                'status' => 'succeeded',
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ]
        );

        $result = $this->repository->claim('wh-done-001', 'PAYMENT.CAPTURE.COMPLETED', 'order-abc');

        $this->assertFalse($result);

        // Row must remain unchanged
        $row = $this->fetchRow('wh-done-001');
        $this->assertSame('succeeded', $row['status']);
    }

    // -------------------------------------------------------------------------
    // markSucceeded() / markFailed()
    // -------------------------------------------------------------------------

    public function testMarkSucceeded(): void
    {
        $this->repository->claim('wh-ms-001', 'PAYMENT.CAPTURE.COMPLETED', 'order-abc');

        $this->repository->markSucceeded('wh-ms-001');

        $row = $this->fetchRow('wh-ms-001');
        $this->assertSame('succeeded', $row['status']);
        $this->assertNull($row['error']);
    }

    public function testMarkFailed(): void
    {
        $this->repository->claim('wh-mf-001', 'PAYMENT.CAPTURE.COMPLETED', 'order-abc');

        $this->repository->markFailed('wh-mf-001', 'Something went wrong');

        $row = $this->fetchRow('wh-mf-001');
        $this->assertSame('failed', $row['status']);
        $this->assertSame('Something went wrong', $row['error']);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * @param string $id
     *
     * @return array<string, mixed>|null
     */
    private function fetchRow(string $id): ?array
    {
        $result = \Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'pscheckout_webhook_event` WHERE `id` = \'' . pSQL($id) . '\''
        );

        return $result ?: null;
    }
}
