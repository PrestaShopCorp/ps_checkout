<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\QueryHandler;

use Currency;
use Exception;
use Order;
use PrestaShopCollection;
use PrestaShopDatabaseException;
use PrestaShopException;
use PsCheckoutCart;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalOrderProvider;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Query\GetPayPalRefundForPDFOrderSlipQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Query\GetPayPalRefundForPDFOrderSlipQueryResult;

class GetPayPalRefundForPDFOrderSlipQueryHandler
{
    /** @var PayPalOrderProvider */
    private $paypalOrderProvider;

    /**
     * @param PayPalOrderProvider $paypalOrderProvider
     */
    public function __construct($paypalOrderProvider)
    {
        $this->paypalOrderProvider = $paypalOrderProvider;
    }

    /**
     * @param GetPayPalRefundForPDFOrderSlipQuery $getPayPalRefundQuery
     *
     * @return GetPayPalRefundForPDFOrderSlipQueryResult
     *
     * @throws PrestaShopDatabaseException|PrestaShopException|PsCheckoutException
     */
    public function handle($getPayPalRefundQuery)
    {
        $orderSlip = $getPayPalRefundQuery->getOrderSlip();

        $order = new Order($orderSlip->id_order);

        if ($order->module !== 'ps_checkout') {
            throw new PsCheckoutException();
        }

        $psCheckoutCartCollection = new PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('id_cart', '=', (int) $order->id_cart);
        $psCheckoutCartCollection->where('paypal_status', 'in', [PsCheckoutCart::STATUS_COMPLETED, PsCheckoutCart::STATUS_PARTIALLY_COMPLETED]);
        $psCheckoutCartCollection->orderBy('date_upd', 'ASC');

        if (!$psCheckoutCartCollection->count()) {
            throw new PsCheckoutException();
        }

        /** @var PsCheckoutCart|bool $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (!$psCheckoutCart) {
            throw new PsCheckoutException();
        }

        try {
            $paypalOrder = $this->paypalOrderProvider->getById($psCheckoutCart->paypal_order);
        } catch (Exception $exception) {
            throw new PsCheckoutException();
        }

        if (!isset($paypalOrder['purchase_units'][0]['payments']['refunds'][0])) {
            throw new PsCheckoutException();
        }

        // TODO: Clean it when we'll have db values
        foreach ($paypalOrder['purchase_units'][0]['payments']['refunds'] as $refund) {
            if (number_format($refund['amount']['value'], 2) !== number_format($orderSlip->amount, 2)) {
                continue;
            }

            $paypalRefund = $refund;
        }

        if (!isset($paypalRefund)) {
            throw new PsCheckoutException();
        }

        return new GetPayPalRefundForPDFOrderSlipQueryResult(
            $paypalRefund['id'],
            $paypalRefund['amount']['value'],
            $paypalRefund['amount']['currency_code'],
            Currency::getIdByIsoCode($paypalRefund['amount']['currency_code'], $order->id_shop),
            $paypalRefund['status'],
            isset($paypalRefund['note_to_payer']) ? $paypalRefund['note_to_payer'] : '',
            isset($paypalRefund['create_time']) ? $paypalRefund['create_time'] : '',
            isset($paypalRefund['update_time']) ? $paypalRefund['update_time'] : ''
        );
    }
}
