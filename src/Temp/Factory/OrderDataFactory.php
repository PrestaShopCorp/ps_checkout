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

namespace PrestaShop\Module\PrestashopCheckout\Temp\Factory;

use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;
use PrestaShop\Module\PrestashopCheckout\Temp\Builder\CreateOrderPayloadBuilder;
use PrestaShop\Module\PrestashopCheckout\Temp\Adapter\OrderDataAdapter;
use PrestaShop\Module\PrestashopCheckout\Temp\Provider\OrderDataProvider;

class OrderDataFactory
{
    /** @var PaypalAccountRepository */
    private $accountRepository;

    /** @var OrderDataAdapter */
    private $orderDataAdapter;

    public function __construct($accountRepository, $orderDataAdapter)
    {
        $this->accountRepository = $accountRepository;
        $this->orderDataAdapter = $orderDataAdapter;
    }

    /**
     * @return array
     */
    public function createFromContext($toArray = true)
    {
        $context = \Context::getContext();
        /** @var \CartCore $cart */
        $cart = $context->cart;
        $currency = new \CurrencyCore($cart->id_currency, $cart->id_lang, $cart->id_shop);
        $customer = new \CustomerCore($cart->id_customer);
        $deliveryAddress = new \AddressCore(
            $cart->id_address_delivery,
            $cart->id_lang
        );
        $invoiceAddress = new \AddressCore(
            $cart->id_address_invoice,
            $cart->id_lang
        );
        $shop = $context->shop;
        $psCheckout = new \PsCheckoutCart();

        $total_with_taxes = 0;
        try {
            $total_with_taxes = $cart->getOrderTotal();
        } catch (\Exception $e) {}

        $data = [
            'cart' => [
                'id' => $cart->id,
                'id_lang' => $cart->id_lang,
                'items' => $cart->getProducts(),
                'shipping_cost' => '',
                'subtotals' => [
                    'gift_wrapping' => [
                        'amount' => !empty(true) ? 'y' : ''
                    ]
                ],
                'total_with_taxes' => $total_with_taxes
            ],
            'currency' => [
                'iso_code' => $currency->iso_code
            ],
            'customer' => [
                'birthday' => !empty($customer->birthday) ? $customer->birthday : '',
                'email_address' => $customer->email,
                'id_gender' => $customer->id_gender
            ],
            'payee' => [
                'email_address' => $this->accountRepository->getMerchantEmail(),
                'merchant_id' => $this->accountRepository->getMerchantId()
            ],
            'payer' => [
                'address_line_1' => $invoiceAddress->address1,
                'address_line_2' => $invoiceAddress->address2,
                'admin_area_2' => $invoiceAddress->city,
                'given_name' => $invoiceAddress->firstname,
                'id_country' => $invoiceAddress->id_country,
                'id_state' => $invoiceAddress->id_state,
                'surname' => $invoiceAddress->lastname,
                'payer_id' => '',
                'phone' => $invoiceAddress->phone,
                'phone_mobile' => $invoiceAddress->phone_mobile,
                'postcode' => $invoiceAddress->postcode
            ],
            'psCheckout' => [
                'isExpressCheckout' => $psCheckout->isExpressCheckout()
            ],
            'shipping' => [
                'address_line_1' => $deliveryAddress->address1,
                'address_line_2' => $deliveryAddress->address2,
                'admin_area_2' => $deliveryAddress->city,
                'given_name' => $deliveryAddress->firstname,
                'id_country' => $deliveryAddress->id_country,
                'id_state' => $deliveryAddress->id_state,
                'surname' => $deliveryAddress->lastname,
                'postcode' => $deliveryAddress->postcode
            ],
            'shop' => [
                'name' => $shop->name
            ]
        ];

        return $this->createFromArray($data, $toArray);
    }

    /**
     * @param array $data
     * @param bool $toArray
     *
     * @return array
     */
    public function createFromArray(array $data, $toArray = true)
    {
        $builder = new CreateOrderPayloadBuilder(new OrderDataProvider($data, $this->orderDataAdapter));

        return $builder->buildPayload($toArray);
    }
}
