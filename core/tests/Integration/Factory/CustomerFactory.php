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

namespace PsCheckout\Core\Tests\Integration\Factory;

use Tools;

class CustomerFactory
{
    /**
     * Create a new customer with only the required fields.
     *
     * @param array $data
     *
     * @return \Customer
     */
    public static function create(array $data = []): \Customer
    {
        $customer = new \Customer();

        // Set mandatory fields
        $customer->firstname = $data['firstname'] ?? 'John';  // First name is required
        $customer->lastname = $data['lastname'] ?? 'Doe';     // Last name is required
        $customer->email = $data['email'] ?? 'test@example.com'; // Email is required
        $customer->passwd = Tools::hash($data['passwd'] ?? 'defaultpassword');

        // Optional fields can be omitted since we are focusing on integration tests and only mandatory fields are needed
        $customer->id_shop = $data['id_shop'] ?? 1;  // Default shop ID
        $customer->id_lang = $data['id_lang'] ?? 1;  // Default language ID

        // Save the customer to the database (if needed)
        if (!$customer->add()) {
            throw new \Exception('Failed to create customer.');
        }

        return $customer;
    }
}
