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

namespace PrestaShop\Module\PrestashopCheckout\Database;

class TableManager
{
    /**
     * @var \Db
     */
    private $db;

    /**
     * @param \Db|null $db PrestaShop Db instance
     */
    public function __construct(\Db $db = null)
    {
        if (null === $db) {
            $db = \Db::getInstance();
        }

        $this->db = $db;
    }

    /**
     * Create table
     *
     * @return bool
     */
    public function createTable()
    {
        $result = $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_order_matrice` (
            `id_order_matrice` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `id_order_prestashop` int(10) unsigned NOT NULL,
            `id_order_paypal` varchar(20) NOT NULL,
            PRIMARY KEY (`id_order_matrice`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_cart` (
              `id_pscheckout_cart` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_cart` int unsigned NOT NULL,
              `paypal_intent` varchar(20) DEFAULT "CAPTURE",
              `paypal_order` varchar(20) NULL,
              `paypal_status` varchar(30) NULL,
              `paypal_funding` varchar(20) NULL,
              `paypal_token` text DEFAULT NULL,
              `paypal_token_expire` datetime NULL,
              `paypal_authorization_expire` datetime NULL,
              `environment` varchar(20) NULL,
              `isExpressCheckout` tinyint(1) unsigned DEFAULT 0 NOT NULL,
              `isHostedFields` tinyint(1) unsigned DEFAULT 0 NOT NULL,
              `date_add` datetime NOT NULL,
              `date_upd` datetime NOT NULL,
              PRIMARY KEY (`id_pscheckout_cart`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_funding_source` (
            `name` varchar(20) NOT NULL,
            `active` tinyint(1) unsigned DEFAULT 0 NOT NULL,
            `position` tinyint(2) unsigned NOT NULL,
            `id_shop` int unsigned NOT NULL,
            PRIMARY KEY (`name`, `id_shop`),
            INDEX (`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_order` (
            `id` varchar(50) NOT NULL,
            `id_cart` int unsigned NOT NULL,
            `status` varchar(30) NOT NULL,
            `intent` varchar(50) DEFAULT "CAPTURE",
            `funding_source` varchar(50) NOT NULL,
            `payment_source` text,
            `environment` varchar(50) NOT NULL,
            `is_card_fields` tinyint(1) NOT NULL,
            `is_express_checkout` tinyint(1) NOT NULL,
            `customer_intent` varchar(50),
            `payment_token_id` varchar(50),
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_capture` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(30) NOT NULL,
            `final_capture` tinyint(1) NOT NULL,
            `created_at` varchar(50) NOT NULL,
            `updated_at` varchar(50) NOT NULL,
            `seller_protection` text,
            `seller_receivable_breakdown` text,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_refund` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(30) NOT NULL,
            `invoice_id` varchar(50) NOT NULL,
            `custom_id` varchar(50) NOT NULL,
            `acquirer_reference_number` varchar(50) NOT NULL,
            `seller_payable_breakdown` text,
            `id_order_slip` INT UNSIGNED,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_authorization` (
            `id` varchar(50) NOT NULL,
            `id_order` varchar(50) NOT NULL,
            `status` varchar(30) NOT NULL,
            `expiration_time` varchar(50) NOT NULL,
            `seller_protection` text,
            PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_purchase_unit` (
            `id_order` varchar(50) NOT NULL,
            `checksum` varchar(50) NOT NULL,
            `reference_id` varchar(50) NOT NULL,
            `items` text,
            PRIMARY KEY (`reference_id`, `id_order`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_customer` (
            `id_customer` int unsigned NOT NULL,
            `paypal_customer_id` varchar(50) NOT NULL,
            PRIMARY KEY (`id_customer`, `paypal_customer_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ') && $this->db->execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'pscheckout_payment_token` (
            `id` INT UNSIGNED AUTO_INCREMENT,
            `token_id` varchar(50) NOT NULL,
            `paypal_customer_id` varchar(50) NOT NULL,
            `payment_source` varchar(50) NOT NULL,
            `data` text NOT NULL,
            `merchant_id` varchar(50) NOT NULL,
            `status` varchar(50) NOT NULL,
            `is_favorite` tinyint(1) unsigned DEFAULT 0 NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `token_id_merchant_id_paypal_customer_id` (`token_id`, `merchant_id`, `paypal_customer_id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=UTF8;
        ');

        $this->checkTable();

        return (bool) $result;
    }

    /**
     * Drop table
     *
     * @return bool
     */
    public function dropTable()
    {
        // Avoid to loose PayPal data if module is reset or uninstall
        return true;
    }

    /**
     * Migrate data from old table based on id_order to new table based on id_cart
     * PrestaShop can create multiple Order from one Cart, so we need to find associated PayPal Order
     *
     * @return bool
     */
    public function populatePsCartFromOrderMatrice()
    {
        return $this->db->execute('
            INSERT INTO `' . _DB_PREFIX_ . 'pscheckout_cart` (`id_cart`, `paypal_order`, `date_add`, `date_upd`)
            SELECT o.id_cart, om.id_order_paypal, o.date_add, o.date_upd
            FROM `' . _DB_PREFIX_ . 'pscheckout_order_matrice` AS om
            INNER JOIN `' . _DB_PREFIX_ . 'orders` AS o ON (om.id_order_prestashop = o.id_order)
        ');
    }

    /**
     * Check if existing database is up to date
     * Due to `CREATE TABLE IF NOT EXISTS` we need to check if table is up to date
     *
     * @return void
     */
    public function checkTable()
    {
        $databaseFields = [];
        $fields = $this->db->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'pscheckout_cart`');

        if (!empty($fields)) {
            foreach ($fields as $field) {
                $databaseFields[] = $field['Field'];
                if ($field['Field'] === 'paypal_token' && $field['Type'] !== 'text') {
                    $this->db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_cart` CHANGE `paypal_token` `paypal_token` text DEFAULT NULL;');
                }

                if ($field['Field'] === 'paypal_status' && $field['Type'] !== 'varchar(30)') {
                    $this->db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_cart` CHANGE `paypal_status` `paypal_status` varchar(30) NULL;');
                }
            }
        }

        $objectDefinition = \PsCheckoutCart::$definition;
        $objectFields = array_keys($objectDefinition['fields']);
        $missingFields = array_diff($objectFields, $databaseFields);

        if (in_array('environment', $missingFields, true)) {
            $this->db->execute('ALTER TABLE `' . _DB_PREFIX_ . 'pscheckout_cart` ADD COLUMN `environment` varchar(20) DEFAULT NULL;');
        }
    }
}
