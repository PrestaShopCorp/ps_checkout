<?php

namespace PsCheckout\Core\Tests\Integration\Factory;

use Order;

class OrderFactory
{
    /**
     * @param array $data
     *
     * @return Order
     */
    public static function create(array $data = []): Order
    {
        $order = new Order();

        // Set required fields
        $order->id_address_delivery = $data['id_address_delivery'] ?? 1;
        $order->id_address_invoice = $data['id_address_invoice'] ?? 1;
        $order->id_cart = $data['id_cart'] ?? 1;
        $order->id_currency = $data['id_currency'] ?? 1;
        $order->id_lang = $data['id_lang'] ?? 1;
        $order->id_customer = $data['id_customer'] ?? 1;
        $order->id_carrier = $data['id_carrier'] ?? 1;
        $order->payment = $data['payment'] ?? 'PayPal';
        $order->module = $data['module'] ?? 'ps_checkout';
        $order->total_paid = $data['total_paid'] ?? 0.000000;
        $order->total_paid_real = $data['total_paid_real'] ?? 0.000000;
        $order->total_products = $data['total_products'] ?? 0.000000;
        $order->total_products_wt = $data['total_products_wt'] ?? 0.000000;
        $order->conversion_rate = $data['conversion_rate'] ?? 1.000000;
        $order->secure_key = $data['secure_key'] ?? md5(uniqid(rand(), true));
        $order->current_state = $data['current_state'] ?? 1;

        // Generate a unique reference if not provided
        if (!isset($data['reference'])) {
            $order->reference = 'TEST-' . uniqid();
        } else {
            $order->reference = $data['reference'];
        }

        // Save the order to database
        $order->add();

        return $order;
    }

    /**
     * @param int $idOrder
     *
     * @return void
     */
    public static function delete(int $idOrder): void
    {
        $order = new Order($idOrder);
        if ($order->id) {
            $order->delete();
        }
    }
}
