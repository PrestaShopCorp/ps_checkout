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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\QueryHandler;

use Customer;
use Currency;
use Exception;
use Group;
use OrderSlip;
use PrestaShopCollection;
use PrestaShopException;
use PsCheckoutCart;
use Validate;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Query\GetPayPalRefundQuery;
use PrestaShop\Module\PrestashopCheckout\PayPal\Payment\Refund\Query\GetPayPalRefundQueryResult;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalOrderProvider;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class GetPayPalRefundQueryHandler
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
     * @param GetPayPalRefundQuery $getRefundTotalAmountQuery
     *
     * @return GetPayPalRefundQueryResult
     *
     * @throws PsCheckoutException|PrestaShopException
     */
    public function handle(GetPayPalRefundQuery $getRefundTotalAmountQuery)
    {
        $order = $getRefundTotalAmountQuery->getOrder();

        $psCheckoutCartCollection = new PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('id_cart', '=', $order->id_cart);
        $psCheckoutCartCollection->where('paypal_status', 'in', [PsCheckoutCart::STATUS_COMPLETED, PsCheckoutCart::STATUS_PARTIALLY_COMPLETED]);
        $psCheckoutCartCollection->orderBy('date_upd', 'ASC');

        if (!$psCheckoutCartCollection->count()) {
            throw new PsCheckoutException(sprintf('Unable to retrieve a PsCheckoutCartCollection for Cart %s', $order->id_cart));
        }

        /** @var PsCheckoutCart|bool $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (!$psCheckoutCart) {
            throw new PsCheckoutException(sprintf('Unable to retrieve a PsCheckoutCart for Cart %s', $order->id_cart));
        }

        try {
            $paypalOrder = $this->paypalOrderProvider->getById($psCheckoutCart->paypal_order);
        } catch (Exception $exception) {
            throw new PsCheckoutException(sprintf('Unable to retrieve PayPal Order %s', $psCheckoutCart->paypal_order));
        }

        if (!isset($paypalOrder['purchase_units'][0])) {
            throw new PsCheckoutException(sprintf('Unable to get a Purchase Unit for PayPal Order %s', $psCheckoutCart->paypal_order));
        }

        $purchaseUnit = $paypalOrder['purchase_units'][0];
        if (!isset($purchaseUnit['payments']['captures'][0])) {
            throw new PsCheckoutException(sprintf('Unable to get Capture for Order %s', $order->id));
        }

        $capture = $purchaseUnit['payments']['captures'][0];

        /** @var OrderSlip[]|bool $orderSlipCollection */
        $orderSlipCollection = $order->getOrderSlipsCollection()->getResults();

        if (!$orderSlipCollection) {
            throw new PsCheckoutException(sprintf('Unable to get OrderSlip for Order %s', $order->id));
        }

        /** @var OrderSlip $orderSlip */
        $orderSlip = end($orderSlipCollection);

        if (!Validate::isLoadedObject($orderSlip)) {
            throw new PsCheckoutException(sprintf('Unable to load last OrderSlip with id %s', $orderSlip->id));
        }

        $customer = new Customer((int) $order->id_customer);
        $useTax = Group::getPriceDisplayMethod((int) $customer->id_default_group);

        $amount = $orderSlip->total_products_tax_incl;
        if ($useTax) {
            $amount = $orderSlip->total_products_tax_excl;
        }

        if ($orderSlip->shipping_cost) {
            if ($useTax) {
                $amount += $orderSlip->total_shipping_tax_excl;
            } else {
                $amount += $orderSlip->total_shipping_tax_incl;
            }
        }

        // Refund based on product prices, but do not refund the voucher amount
        $cartRuleTotal = 0;
        if ($orderSlip->order_slip_type == 1 && is_array($cartRules = $order->getCartRules())) {
            foreach ($cartRules as $cartRule) {
                if ($useTax) {
                    $cartRuleTotal -= $cartRule['value_tax_excl'];
                } else {
                    $cartRuleTotal -= $cartRule['value'];
                }
            }
        }

        $amount += $cartRuleTotal;

        if ($amount <= 0) {
            throw new PsCheckoutException('Refund amount cannot be less than or equal to zero');
        }

        $totalCaptured = (float) $capture['amount']['value'];

        $totalAlreadyRefund = 0;
        if (isset($purchaseUnit['payments']['refunds'])) {
            $totalAlreadyRefund = array_reduce($purchaseUnit['payments']['refunds'], function ($totalRefunded, $refund) {
                return $totalRefunded + (float) $refund['amount']['value'];
            });
        }

        if ($totalCaptured < $amount + $totalAlreadyRefund) {
            throw new PsCheckoutException(sprintf('Refund amount %s is greater than captured amount %s', $totalCaptured, $amount));
        }

        $currency = new Currency($order->id_currency);

        return new GetPayPalRefundQueryResult(
            $psCheckoutCart->getPaypalOrderId(),
            $capture['id'],
            (string) $amount,
            $currency->iso_code
        );
    }
}
