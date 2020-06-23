<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PaypalCountryCodeMatrice;
use PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository;

/**
 * Build the payload for creating paypal order
 */
class OrderPayloadBuilder extends Builder implements PayloadBuilderInterface
{
    /**
     * @var array
     */
    private $cart;

    /**
     * @var bool Allow to build the payload with more or less content depending if
     *           the customer use express checkout or not
     */
    private $expressCheckout = false;

    /**
     * Define if we build the payload to create
     * or update a paypal order
     *
     * @var bool
     */
    private $isUpdate = false;

    /**
     * PayPal order id
     *
     * @var string
     */
    private $paypalOrderId;

    public function __construct(array $cart)
    {
        $this->cart = $cart;

        parent::__construct();
    }

    /**
     * Build payload with cart details
     *
     * @throws PsCheckoutException
     */
    public function buildFullPayload()
    {
        parent::buildFullPayload();

        $this->checkPaypalOrderIdWhenUpdate();

        $this->buildBaseNode();
        $this->buildAmountBreakdownNode();
        $this->buildItemsNode();

        if (false === $this->expressCheckout) {
            $this->buildShippingNode();

            if (false === $this->isUpdate) {
                $this->buildPayerNode();
            }
        }

        if (false === $this->isUpdate) {
            $this->buildApplicationContextNode();
        }
    }

    /**
     * Build payload without cart details
     *
     * @throws PsCheckoutException
     */
    public function buildMinimalPayload()
    {
        parent::buildMinimalPayload();

        $this->checkPaypalOrderIdWhenUpdate();

        $this->buildBaseNode();

        if (false === $this->expressCheckout) {
            $this->buildShippingNode();

            if (false === $this->isUpdate) {
                $this->buildPayerNode();
            }
        }

        if (false === $this->isUpdate) {
            $this->buildApplicationContextNode();
        }
    }

    /**
     * Build the basic payload
     */
    public function buildBaseNode()
    {
        $shopName = \Configuration::get(
            'PS_SHOP_NAME',
            null,
            null,
            (int) \Context::getContext()->shop->id
        );

        $node = [
            'intent' => \Configuration::get(
                'PS_CHECKOUT_INTENT',
                null,
                null,
                (int) \Context::getContext()->shop->id
            ), // capture or authorize
            'custom_id' => (string) $this->cart['cart']['id'], // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => '',
            'description' => $this->truncate(
                'Checking out with your cart from ' . $shopName,
                127
            ),
            'soft_descriptor' => $this->truncate(
                $shopName,
                22
            ),
            'amount' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $this->cart['cart']['totals']['total_including_tax']['amount'],
            ],
            'payee' => [
                'merchant_id' => (new PaypalAccountRepository())->getMerchantId(),
            ],
        ];

        if (true === $this->isUpdate) {
            $node['id'] = $this->paypalOrderId;
        } else {
            $roundType = (string) \Configuration::get(
                'PS_ROUND_TYPE',
                null,
                null,
                (int) \Context::getContext()->shop->id
            );

            $roundMode = (string) \Configuration::get(
                'PS_PRICE_ROUND_MODE',
                null,
                null,
                (int) \Context::getContext()->shop->id
            );

            $node['roundingConfig'] = $roundType . '-' . $roundMode;
        }

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

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build shipping node
     */
    public function buildShippingNode()
    {
        $countryCodeMatrice = new PaypalCountryCodeMatrice();
        $shippingCountryIsoCode = $this->getCountryIsoCodeById($this->cart['addresses']['shipping']->id_country);

        $gender = new \Gender($this->cart['customer']->id_gender, $this->cart['language']->id);
        $genderName = $gender->name;

        $node['shipping'] = [
            'name' => [
                'full_name' => $genderName . ' ' . $this->cart['addresses']['shipping']->lastname . ' ' . $this->cart['addresses']['shipping']->firstname,
            ],
            'address' => [
                'address_line_1' => (string) $this->cart['addresses']['shipping']->address1,
                'address_line_2' => (string) $this->cart['addresses']['shipping']->address2,
                'admin_area_1' => (string) $this->getStateNameById($this->cart['addresses']['shipping']->id_state),
                'admin_area_2' => (string) $this->cart['addresses']['shipping']->city,
                'country_code' => (string) $countryCodeMatrice->getPaypalIsoCode($shippingCountryIsoCode),
                'postal_code' => (string) $this->cart['addresses']['shipping']->postcode,
            ],
        ];

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build payer node
     */
    public function buildPayerNode()
    {
        $countryCodeMatrice = new PaypalCountryCodeMatrice();
        $payerCountryIsoCode = $this->getCountryIsoCodeById($this->cart['addresses']['invoice']->id_country);

        $node['payer'] = [
            'name' => [
                'given_name' => (string) $this->cart['addresses']['invoice']->firstname,
                'surname' => (string) $this->cart['addresses']['invoice']->lastname,
            ],
            'email_address' => (string) $this->cart['customer']->email,
            'address' => [
                'address_line_1' => (string) $this->cart['addresses']['invoice']->address1,
                'address_line_2' => (string) $this->cart['addresses']['invoice']->address2,
                'admin_area_1' => (string) $this->getStateNameById($this->cart['addresses']['invoice']->id_state), //The highest level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision.
                'admin_area_2' => (string) $this->cart['addresses']['invoice']->city, // A city, town, or village. Smaller than admin_area_level_1
                'country_code' => (string) $countryCodeMatrice->getPaypalIsoCode($payerCountryIsoCode),
                'postal_code' => (string) $this->cart['addresses']['invoice']->postcode,
            ],
        ];

        // Add optional birthdate if provided
        if (!empty($this->cart['customer']->birthday) && $this->cart['customer']->birthday !== '0000-00-00') {
            $node['payer']['birth_date'] = (string) $this->cart['customer']->birthday;
        }

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build application context node
     *
     * NO_SHIPPING: The client can customize his address int the paypal pop-up (used in express checkout mode)
     * SET_PROVIDED_ADDRESS: The address is provided by prestashop and the client
     * cannot change/edit his address in the paypal pop-up
     */
    public function buildApplicationContextNode()
    {
        $node['application_context'] = [
            'brand_name' => \Configuration::get(
                'PS_SHOP_NAME',
                null,
                null,
                (int) \Context::getContext()->shop->id
            ),
            'shipping_preference' => $this->expressCheckout ? 'GET_FROM_FILE' : 'SET_PROVIDED_ADDRESS',
        ];

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build the paypal items node
     */
    public function buildItemsNode()
    {
        $node = [];

        foreach ($this->cart['products'] as $product => $value) {
            $paypalItem = [];

            $sku = '';

            if (false === empty($value['reference'])) {
                $sku = $value['reference'];
            }

            if (false === empty($value['ean13'])) {
                $sku = $value['ean13'];
            }

            if (false === empty($value['isbn'])) {
                $sku = $value['isbn'];
            }

            if (false === empty($value['upc'])) {
                $sku = $value['upc'];
            }

            $paypalItem['name'] = $this->truncate($value['name'], 127);
            $paypalItem['description'] = false === empty($value['attributes']) ? $this->truncate($value['attributes'], 127) : '';
            $paypalItem['sku'] = $this->truncate($sku, 127);
            $paypalItem['unit_amount']['currency_code'] = $this->cart['currency']['iso_code'];
            $paypalItem['unit_amount']['value'] = $value['total'] / $value['quantity'];
            $paypalItem['tax']['currency_code'] = $this->cart['currency']['iso_code'];
            $paypalItem['tax']['value'] = ($value['total_wt'] - $value['total']) / $value['quantity'];
            $paypalItem['quantity'] = $value['quantity'];
            $paypalItem['category'] = $value['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';

            $node['items'][] = $paypalItem;
        }

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Build the amount breakdown node
     */
    public function buildAmountBreakdownNode()
    {
        $totalProductWithoutTax = $this->calcTotalProductWithoutTax();
        $totalTax = $this->calcTotalTax();

        $node['amount']['breakdown'] = [
            'item_total' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $totalProductWithoutTax,
            ],
            'shipping' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $this->cart['cart']['shipping_cost'],
            ],
            'tax_total' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $totalTax,
            ],
        ];

        // define by default the handling at 0
        $handlingValue = 0;

        // set handling cost id needed -> principally used in case of gift_wrapping
        if (isset($this->cart['cart']['subtotals']['gift_wrapping'])) {
            $handlingValue += $this->cart['cart']['subtotals']['gift_wrapping']['amount'];

            $node['amount']['breakdown']['handling'] = [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $handlingValue,
            ];
        }

        // Calc the discount value. Dicount value can also be used in case of a rounding issue.
        // PrestaShop and PayPal doesn't handle rounding in the same way. In some cases (eg: amount ended with .35, .55 etc ... with a 10% discount),
        // the amount value of PrestaShop is different of the value calc by PayPal (paypal always has .1 or .2 more than prestashop).
        // In order to avoid this difference, we put it into the discount field in order to get the correct value.
        // (the surplus value (calc by paypal) is deducts from the total amount in order to get the same amount of prestashop)
        $node['amount']['breakdown']['discount'] = [
            'currency_code' => $this->cart['currency']['iso_code'],
            'value' => abs($this->cart['cart']['totals']['total_including_tax']['amount'] - $totalProductWithoutTax - $totalTax - $this->cart['cart']['shipping_cost'] - $handlingValue),
        ];

        $this->getPayload()->addAndMergeItems($node);
    }

    /**
     * Calc products total amount without tax
     *
     * @return float
     */
    private function calcTotalProductWithoutTax()
    {
        $totalProductsWithoutTax = 0;

        foreach ($this->cart['products'] as $product) {
            // calc total for the current product in the loop
            $unitProductPriceWithoutTax = $product['total'] / $product['quantity'];
            $productsPriceWithoutTax = $unitProductPriceWithoutTax * $product['quantity'];

            // adding the amount for the current product to the total amount
            $totalProductsWithoutTax += $productsPriceWithoutTax;
        }

        return $totalProductsWithoutTax;
    }

    /**
     * Calc the total tax
     *
     * @return float
     */
    private function calcTotalTax()
    {
        $totalTax = 0;

        foreach ($this->cart['products'] as $product) {
            // calc total tax for the current product in the loop
            $unitTaxAmount = ($product['total_wt'] - $product['total']) / $product['quantity'];
            $totalTaxAmount = $unitTaxAmount * $product['quantity'];

            // adding the amount tax for the current product to the total tax amount
            $totalTax += $totalTaxAmount;
        }

        return $totalTax;
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
        $currency_wt_decimal = ['HUF', 'JPY', 'TWD'];

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
     * @throws PsCheckoutException
     */
    private function checkPaypalOrderIdWhenUpdate()
    {
        if (true === $this->isUpdate && empty($this->paypalOrderId)) {
            throw new PsCheckoutException('PayPal order ID is required when building payload for update an order', PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING);
        }
    }

    /**
     * Setter $expressCheckout
     *
     * @param bool $expressCheckout
     */
    public function setExpressCheckout($expressCheckout)
    {
        $this->expressCheckout = $expressCheckout;
    }

    /**
     * Setter $isUpdate
     *
     * @param bool $bool
     */
    public function setIsUpdate($bool)
    {
        $this->isUpdate = $bool;
    }

    /**
     * Setter $paypalOrderId
     *
     * @param string $id
     */
    public function setPaypalOrderId($id)
    {
        $this->paypalOrderId = $id;
    }

    /**
     * Getter $paypalOrderId
     */
    public function getPaypalOrderId()
    {
        return $this->paypalOrderId;
    }

    /**
     * Getter $expressCheckout
     */
    public function getExpressCheckout()
    {
        return $this->expressCheckout;
    }
}
