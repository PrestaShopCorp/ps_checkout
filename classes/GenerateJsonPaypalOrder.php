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

use PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter;

class GenerateJsonPaypalOrder
{
    public function create(\Context $context)
    {
        return $this->createJsonFromData(
            $this->fetchDataFromCart($context->cart, $context->customer)
        );
    }

    /**
     * @param \Cart Current cart
     * @param \Customer Current customer
     *
     * @return array Data to be added in the Paypal payload
     */
    public function fetchDataFromCart(\Cart $cart, \Customer $customer)
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
                ['id' => $cart->id]
            ),
            'customer' => $customer,
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
     * @param array fetchDataFromCart()
     *
     * @return string paypal order data
     */
    public function createJsonFromData($params)
    {
        $items = [];
        $totalTaxItem = 0;
        $totalAmountItemWithoutTax = 0;

        foreach ($params['products'] as $product => $value) {
            $item = [];

            $item['name'] = $value['name'];
            $item['description'] = strip_tags($value['description_short']);
            $item['sku'] = $value['unity'];
            // $item['url'] = $this->getProductUrlById($value['id_product']); // not allowed for the moment
            $item['unit_amount']['currency_code'] = $params['currency']['iso_code'];
            $item['unit_amount']['value'] = $value['price'];
            $item['tax']['currency_code'] = $params['currency']['iso_code'];
            $item['tax']['value'] = $value['price'] * $value['rate'] / 100;
            $item['quantity'] = $value['quantity'];
            $item['category'] = $value['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';

            $totalTaxItem = $totalTaxItem + ($value['price'] * $value['cart_quantity']);
            $totalAmountItemWithoutTax = $totalAmountItemWithoutTax + ($value['price'] * $value['rate'] / 100) * $value['cart_quantity'];

            $items[] = $item;
        }

        $payload = json_encode([
            'intent' => \Configuration::get('PS_PAY_INTENT'), // capture or authorize
            'custom_id' => (string) $params['cart']['id'], // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => '',
            'description' => 'Checking out with your cart from {SHOP}',
            'soft_descriptor' => 'MR '.$params['addresses']['invoice']->lastname.' '.$params['addresses']['invoice']->firstname,
            'amount' => [
                'currency_code' => $params['currency']['iso_code'],
                'value' => $params['cart']['totals']['total']['amount'],
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => $params['currency']['iso_code'],
                        'value' => $totalTaxItem
                    ],
                    'shipping' => [
                        'currency_code' => $params['currency']['iso_code'],
                        'value' => $params['cart']['subtotals']['shipping']['amount']
                    ],
                    'tax_total' => [
                        'currency_code' => $params['currency']['iso_code'],
                        'value' =>  $totalAmountItemWithoutTax
                    ],
                ]
            ],
            'items' => $items,
            'shipping' => [
                'name' => [
                    'full_name' => 'Mr '.$params['addresses']['shipping']->lastname.' '.$params['addresses']['shipping']->firstname,
                ],
                'address' => [
                    'address_line_1' => $params['addresses']['shipping']->address1,
                    'address_line_2' => $params['addresses']['shipping']->address2,
                    'admin_area_1' => (string) $this->getStateNameById($params['addresses']['shipping']->id_state),
                    'admin_area_2' => $params['addresses']['shipping']->city,
                    'country_code' => $this->getCountryIsoCodeById($params['addresses']['shipping']->id_country),
                    'postal_code' => $params['addresses']['shipping']->postcode
                ]
            ],
            'payer' => [
                'name' => [
                    'given_name' => $params['customer']->lastname,
                    'surname' => $params['customer']->firstname
                ],
                'email_address' => $params['customer']->email,
                'phone' => [
                    'phone_number' => [
                        'national_number' => $params['addresses']['invoice']->phone
                    ],
                    'phone_type' => 'MOBILE' // TODO - Function to determine if phone is mobile or not
                ],
                'birth_date' => $params['customer']->birthday,
                'address' => [
                    'address_line_1' => $params['addresses']['invoice']->address1,
                    'address_line_2' => $params['addresses']['invoice']->address2,
                    'admin_area_1' => (string) $this->getStateNameById($params['addresses']['invoice']->id_state), //The highest level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision.
                    'admin_area_2' => $params['addresses']['invoice']->city, // A city, town, or village. Smaller than admin_area_level_1
                    'country_code' => $this->getCountryIsoCodeById($params['addresses']['invoice']->id_country),
                    'postal_code' => $params['addresses']['invoice']->postcode,
                ]
            ],
            'payee' => [
                'merchant_id' => '7RN2XXLUBYHHS' // merchant id which is return at the end of the onboarding - Test mechant id : 7RN2XXLUBYHHS
            ],
            'application_context' => [
                'brand_name' => 'PrestaShop Payments',
                'locale' => 'bs-BA',
                // 'return_url' => \Tools::getShopDomainSsl(true).__PS_BASE_URI__,
                // 'cancel_url' => 'https://myshop.com/order/cancel',
                'shipping_preference' => 'SET_PROVIDED_ADDRESS'
            ]
        ]);

        return $payload;
    }

    /**
     * Adapter method retrieving a state name from an ID
     *
     * @param int State ID
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
     * @param int Country ID
     *
     * @return string Country ISO code
     */
    private function getCountryIsoCodeById($countryId)
    {
        return \Country::getIsoById($countryId);
    }

    /**
     * Adapter method for getting the product url for the given id product
     *
     * @param int $productId
     *
     * @return string Product Url
     */
    private function getProductUrlById($productId)
    {
        return \Context::getContext()->link->getProductLink(new \Product($productId));
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
