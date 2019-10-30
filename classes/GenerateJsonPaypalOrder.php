<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\PrestaShop\Adapter\Cart\CartPresenter;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

/**
 * Generate the payload waited by paypal to create an order (paypal order)
 */
class GenerateJsonPaypalOrder
{
    /**
     * @param \Context $context
     *
     * @return string
     */
    public function create(\Context $context)
    {
        return $this->createJsonFromData(
            $this->fetchDataFromCart($context->cart, $context->customer, $context->language)
        );
    }

    /**
     * @param \Cart $cart Current cart
     * @param \Customer $customer Current customer
     * @param \Language $language Current language
     *
     * @return array Data to be added in the Paypal payload
     */
    public function fetchDataFromCart(\Cart $cart, \Customer $customer, \Language $language)
    {
        // TODO: check cart
        $productList = $cart->getProducts();

        $cartPresenter = new CartPresenter();
        $cartPresenter = $cartPresenter->present($cart);

        $shippingAddress = \Address::initialize($cartPresenter['id_address_delivery']);
        $invoiceAddress = \Address::initialize($cartPresenter['id_address_invoice']);

        return [
            'cart' => array_merge(
                $cartPresenter,
                ['id' => $cart->id],
                ['shipping_cost' => $cart->getTotalShippingCost(null, true)]
            ),
            'customer' => $customer,
            'language' => $language,
            'products' => $productList,
            'addresses' => [
                'shipping' => $shippingAddress,
                'invoice' => $invoiceAddress,
            ],
            'currency' => [
                'iso_code' => $this->getCurrencyIsoFromId($cart->id_currency),
            ],
        ];
    }

    /**
     * Create payload required by paypal api for creating order
     *
     * @param array $params fetchDataFromCart()
     *
     * @return string paypal order data
     */
    public function createJsonFromData($params)
    {
        $items = [];

        $totalProductsWithoutTax = 0;
        $totalTax = 0;

        foreach ($params['products'] as $product => $value) {
            $item = [];

            $item['name'] = $this->truncate($value['name'], 127);
            $item['description'] = $this->truncate(strip_tags($value['description_short']), 127);
            $item['sku'] = $this->truncate($value['unity'], 127);
            $item['unit_amount']['currency_code'] = $params['currency']['iso_code'];
            $item['unit_amount']['value'] = $value['total'] / $value['quantity'];
            $item['tax']['currency_code'] = $params['currency']['iso_code'];
            $item['tax']['value'] = ($value['total_wt'] - $value['total']) / $value['quantity'];
            $item['quantity'] = $value['quantity'];
            $item['category'] = $value['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';

            $totalProductsWithoutTax += $item['unit_amount']['value'] * $value['quantity'];
            $totalTax += $item['tax']['value'] * $value['quantity'];

            $items[] = $item;
        }

        $countryCodeMatrice = new PaypalCountryCodeMatrice();
        $shippingCountryIsoCode = $this->getCountryIsoCodeById($params['addresses']['shipping']->id_country);
        $payerCountryIsoCode = $this->getCountryIsoCodeById($params['addresses']['invoice']->id_country);

        $payload = [
            'intent' => \Configuration::get('PS_CHECKOUT_INTENT'), // capture or authorize
            'custom_id' => (string) $params['cart']['id'], // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => '',
            'description' => $this->truncate('Checking out with your cart from ' . \Configuration::get('PS_SHOP_NAME'), 127),
            'soft_descriptor' => $this->truncate(\Configuration::get('PS_SHOP_NAME'), 22),
            'amount' => [
                'currency_code' => $params['currency']['iso_code'],
                'value' => $params['cart']['totals']['total_including_tax']['amount'],
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => $params['currency']['iso_code'],
                        'value' => $totalProductsWithoutTax,
                    ],
                    'shipping' => [
                        'currency_code' => $params['currency']['iso_code'],
                        'value' => $params['cart']['shipping_cost'],
                    ],
                    'tax_total' => [
                        'currency_code' => $params['currency']['iso_code'],
                        'value' => $totalTax,
                    ],
                ],
            ],
            'items' => $items,
            'shipping' => [
                'name' => [
                    'full_name' => 'Mr ' . $params['addresses']['shipping']->lastname . ' ' . $params['addresses']['shipping']->firstname,
                ],
                'address' => [
                    'address_line_1' => $params['addresses']['shipping']->address1,
                    'address_line_2' => $params['addresses']['shipping']->address2,
                    'admin_area_1' => (string) $this->getStateNameById($params['addresses']['shipping']->id_state),
                    'admin_area_2' => $params['addresses']['shipping']->city,
                    'country_code' => $countryCodeMatrice->getPaypalIsoCode($shippingCountryIsoCode),
                    'postal_code' => $params['addresses']['shipping']->postcode,
                ],
            ],
            'payer' => [
                'name' => [
                    'given_name' => $params['customer']->lastname,
                    'surname' => $params['customer']->firstname,
                ],
                'email_address' => $params['customer']->email,
                'address' => [
                    'address_line_1' => $params['addresses']['invoice']->address1,
                    'address_line_2' => $params['addresses']['invoice']->address2,
                    'admin_area_1' => (string) $this->getStateNameById($params['addresses']['invoice']->id_state), //The highest level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision.
                    'admin_area_2' => $params['addresses']['invoice']->city, // A city, town, or village. Smaller than admin_area_level_1
                    'country_code' => $countryCodeMatrice->getPaypalIsoCode($payerCountryIsoCode),
                    'postal_code' => $params['addresses']['invoice']->postcode,
                ],
            ],
            'payee' => [
                'merchant_id' => (new PaypalAccountRepository())->getMerchantId(),
            ],
            'application_context' => [
                'brand_name' => \Configuration::get('PS_SHOP_NAME'),
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
            ],
            'roundingConfig' => (string) \Configuration::get('PS_ROUND_TYPE') . '-' . (string) \Configuration::get('PS_PRICE_ROUND_MODE'),
        ];

        // define by default the handling at 0
        $handlingValue = 0;

        // set handling cost id needed -> principally used in case of gift_wrapping
        if (isset($params['cart']['subtotals']['gift_wrapping'])) {
            $handlingValue += $params['cart']['subtotals']['gift_wrapping']['amount'];

            $payload['amount']['breakdown']['handling'] = [
                'currency_code' => $params['currency']['iso_code'],
                'value' => $handlingValue,
            ];
        }

        // Calc the discount value. Dicount value can also be used in case of a rounding issue.
        // PrestaShop and PayPal doesn't handle rounding in the same way. In some cases (eg: amount ended with .35, .55 etc ... with a 10% discount),
        // the amount value of PrestaShop is different of the value calc by PayPal (paypal always has .1 or .2 more than prestashop).
        // In order to avoid this difference, we put it into the discount field in order to get the correct value.
        // (the surplus value (calc by paypal) is deducts from the total amount in order to get the same amount of prestashop)
        $payload['amount']['breakdown']['discount'] = [
            'currency_code' => $params['currency']['iso_code'],
            'value' => abs($params['cart']['totals']['total_including_tax']['amount'] - $totalProductsWithoutTax - $totalTax - $params['cart']['shipping_cost'] - $handlingValue),
        ];

        // TODO: Disabled temporary: Need to handle country indicator
        // Add optional phone number if provided
        // if (!empty($params['addresses']['invoice']->phone)) {
        //     $payload['payer']['phone'] = [
        //         'phone_number' => [
        //             'national_number' => $params['addresses']['invoice']->phone,
        //         ],
        //         'phone_type' => 'MOBILE', // TODO - Function to determine if phone is mobile or not
        //     ];
        // }

        // Add optional birthdate if provided
        if (!empty($params['customer']->birthday) && $params['customer']->birthday !== '0000-00-00') {
            $payload['payer']['birth_date'] = $params['customer']->birthday;
        }

        return json_encode($payload);
    }

    /**
     * Function that allow to truncate fields to match the
     * paypal api requirements
     *
     * @param string $str
     * @param int $limit
     *
     * @return string
     */
    private function truncate($str, $limit)
    {
        if (empty($str)) {
            return (string) $str;
        }

        return mb_substr($str, 0, $limit);
    }

    /**
     * Get decimal to round correspondent to the payment currency used
     * Advise from PayPal: Always round to 2 decimals except for HUF, JPY and TWD
     * currencies which require a round with 0 decimal
     *
     * @param string $currencyIsoCode iso code of the currency used to pass the payment
     *
     * @return int
     */
    public function getNbDecimalToRound($currencyIsoCode)
    {
        $currency_wt_decimal = array('HUF', 'JPY', 'TWD');

        if (in_array($currencyIsoCode, $currency_wt_decimal)) {
            return 0;
        }

        return 2;
    }

    /**
     * Adapter method retrieving a state name from an ID
     *
     * @param int $stateId State ID
     *
     * @return string State name
     */
    private function getStateNameById($stateId)
    {
        return \State::getNameById($stateId);
    }

    /**
     * Use the core to retrieve a country ISO code from its ID
     *
     * @param int $countryId Country ID
     *
     * @return string Country ISO code
     */
    private function getCountryIsoCodeById($countryId)
    {
        return \Country::getIsoById($countryId);
    }

    /**
     * Get currency iso code from id currency
     *
     * @param int $currencyId
     *
     * @return string Currency iso code
     */
    private function getCurrencyIsoFromId($currencyId)
    {
        $currency = \Currency::getCurrency($currencyId);

        return $currency['iso_code'];
    }
}
