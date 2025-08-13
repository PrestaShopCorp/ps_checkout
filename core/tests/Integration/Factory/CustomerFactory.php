<?php

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
        $customer->passwd = isset($data['passwd']) ? Tools::encrypt($data['passwd']) : Tools::encrypt('defaultpassword'); // Password is required and should be hashed

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
