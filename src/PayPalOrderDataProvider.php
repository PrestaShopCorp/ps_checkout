<?php

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class PayPalOrderDataProvider
{
    /**
     * @param string $paypalOrderId
     * @return array
     * @throws PsCheckoutException
     */
    public function getOrder($paypalOrderId)
    {
        $paypalOrder = new \PrestaShop\Module\PrestashopCheckout\PaypalOrder($paypalOrderId);
        $order = $paypalOrder->getOrder();

        if (empty($order)) {
            throw new PsCheckoutException(sprintf('Unable to retrieve Paypal Order for %s', $paypalOrderId), PsCheckoutException::PRESTASHOP_ORDER_NOT_FOUND);
        }

        return [
            'id' => $order['id'],
            'transactionIdentifier' => !empty($order['purchase_units'][0]['payments']['captures'][0]['id']) ? $order['purchase_units'][0]['payments']['captures'][0]['id'] : '',
            'transactionStatus' => !empty($order['purchase_units'][0]['payments']['captures'][0]['status']) ? $order['purchase_units'][0]['payments']['captures'][0]['status'] : '',
            'status' => $order['status'],
            'orderAmount' => $order['purchase_units'][0]['amount']['value'],
            'intent' => $paypalOrder->getOrderIntent(),
            'captures' => !empty($order['purchase_units'][0]['payments']['captures']) ? $order['purchase_units'][0]['payments']['captures'] : null,
        ];
    }
}
