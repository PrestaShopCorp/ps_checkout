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

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

use PrestaShop\Module\PrestashopCheckout\PaypalCountryCodeMatrice;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

/**
 * Build the payload for creating paypal order
 */
class OrderPayloadBuilder implements PayloadBuilderInterface
{
    /**
     * @var Payload
     */
    private $payload;

    /**
     * @var array
     */
    private $cart;

    public function __construct(array $cart)
    {
        $this->reset();
        $this->cart = $cart;
    }

    public function reset()
    {
        $this->payload = new Payload();
    }

    /**
     * Return the result of the payload
     * Clean the builder to be ready to producing a new payload
     *
     * @return Payload
     */
    public function getPayload()
    {
        $payload = $this->payload;
        $this->reset();

        return $payload;
    }

    /**
     * Build payload with cart details
     */
    public function buildFullPayload()
    {
        $this->reset();

        $this->buildBaseNode();
        $this->buildAmountBreakdownNode();
    }

    /**
     * Build payload without cart details
     */
    public function buildMinimalPayload()
    {
        $this->reset();

        $this->buildBaseNode();
    }

    /**
     * Build the basic payload
     */
    public function buildBaseNode()
    {
        $countryCodeMatrice = new PaypalCountryCodeMatrice();
        $shippingCountryIsoCode = $this->getCountryIsoCodeById($this->cart['addresses']['shipping']->id_country);
        $payerCountryIsoCode = $this->getCountryIsoCodeById($this->cart['addresses']['invoice']->id_country);

        $this->payload->items += [
            'intent' => \Configuration::get('PS_CHECKOUT_INTENT'), // capture or authorize
            'custom_id' => (string) $this->cart['cart']['id'], // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => '',
            'description' => $this->truncate('Checking out with your cart from ' . \Configuration::get('PS_SHOP_NAME'), 127),
            'soft_descriptor' => $this->truncate(\Configuration::get('PS_SHOP_NAME'), 22),
            'amount' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $this->cart['cart']['totals']['total_including_tax']['amount'],
            ],
            'shipping' => [
                'name' => [
                    'full_name' => 'Mr ' . $this->cart['addresses']['shipping']->lastname . ' ' . $this->cart['addresses']['shipping']->firstname,
                ],
                'address' => [
                    'address_line_1' => $this->cart['addresses']['shipping']->address1,
                    'address_line_2' => $this->cart['addresses']['shipping']->address2,
                    'admin_area_1' => (string) $this->getStateNameById($this->cart['addresses']['shipping']->id_state),
                    'admin_area_2' => $this->cart['addresses']['shipping']->city,
                    'country_code' => $countryCodeMatrice->getPaypalIsoCode($shippingCountryIsoCode),
                    'postal_code' => $this->cart['addresses']['shipping']->postcode,
                ],
            ],
            'payer' => [
                'name' => [
                    'given_name' => $this->cart['customer']->lastname,
                    'surname' => $this->cart['customer']->firstname,
                ],
                'email_address' => $this->cart['customer']->email,
                'address' => [
                    'address_line_1' => $this->cart['addresses']['invoice']->address1,
                    'address_line_2' => $this->cart['addresses']['invoice']->address2,
                    'admin_area_1' => (string) $this->getStateNameById($this->cart['addresses']['invoice']->id_state), //The highest level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision.
                    'admin_area_2' => $this->cart['addresses']['invoice']->city, // A city, town, or village. Smaller than admin_area_level_1
                    'country_code' => $countryCodeMatrice->getPaypalIsoCode($payerCountryIsoCode),
                    'postal_code' => $this->cart['addresses']['invoice']->postcode,
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
        if (!empty($this->cart['customer']->birthday) && $this->cart['customer']->birthday !== '0000-00-00') {
            $this->payload->items['payer']['birth_date'] = $this->cart['customer']->birthday;
        }
    }

    /**
     * Build the paypal items node
     *
     * @return array
     */
    public function buildItemsNode()
    {
        $paypalItems = [];

        $totalProductsWithoutTax = 0;
        $totalTax = 0;

        foreach ($this->cart['products'] as $product => $value) {
            $paypalItem = [];

            $paypalItem['name'] = $this->truncate($value['name'], 127);
            $paypalItem['description'] = $this->truncate(strip_tags($value['description_short']), 127);
            $paypalItem['sku'] = $this->truncate($value['unity'], 127);
            $paypalItem['unit_amount']['currency_code'] = $this->cart['currency']['iso_code'];
            $paypalItem['unit_amount']['value'] = $value['total'] / $value['quantity'];
            $paypalItem['tax']['currency_code'] = $this->cart['currency']['iso_code'];
            $paypalItem['tax']['value'] = ($value['total_wt'] - $value['total']) / $value['quantity'];
            $paypalItem['quantity'] = $value['quantity'];
            $paypalItem['category'] = $value['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';

            $totalProductsWithoutTax += $paypalItem['unit_amount']['value'] * $value['quantity'];
            $totalTax += $paypalItem['tax']['value'] * $value['quantity'];

            $paypalItems[] = $paypalItem;
        }

        $this->payload->items['items'] = $paypalItems;

        return [
            'totalProductsWithTax' => $totalProductsWithoutTax,
            'totalTax' => $totalTax,
        ];
    }

    /**
     * Build the amount breakdown node
     */
    public function buildAmountBreakdownNode()
    {
        $totals = $this->buildItemsNode();

        $this->payload->items['amount']['breakdown'] = [
            'item_total' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $totals['totalProductsWithTax'],
            ],
            'shipping' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $this->cart['cart']['shipping_cost'],
            ],
            'tax_total' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $totals['totalTax'],
            ],
        ];

        // define by default the handling at 0
        $handlingValue = 0;

        // set handling cost id needed -> principally used in case of gift_wrapping
        if (isset($this->cart['cart']['subtotals']['gift_wrapping'])) {
            $handlingValue += $this->cart['cart']['subtotals']['gift_wrapping']['amount'];

            $this->payload->items['amount']['breakdown']['handling'] = [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $handlingValue,
            ];
        }

        // Calc the discount value. Dicount value can also be used in case of a rounding issue.
        // PrestaShop and PayPal doesn't handle rounding in the same way. In some cases (eg: amount ended with .35, .55 etc ... with a 10% discount),
        // the amount value of PrestaShop is different of the value calc by PayPal (paypal always has .1 or .2 more than prestashop).
        // In order to avoid this difference, we put it into the discount field in order to get the correct value.
        // (the surplus value (calc by paypal) is deducts from the total amount in order to get the same amount of prestashop)
        $this->payload->items['amount']['breakdown']['discount'] = [
            'currency_code' => $this->cart['currency']['iso_code'],
            'value' => abs($this->cart['cart']['totals']['total_including_tax']['amount'] - $totals['totalProductsWithTax'] - $totals['totalTax'] - $this->cart['cart']['shipping_cost'] - $handlingValue),
        ];
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
    private function getNbDecimalToRound($currencyIsoCode)
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
}
