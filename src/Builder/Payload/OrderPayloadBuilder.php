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

namespace PrestaShop\Module\PrestashopCheckout\Builder\Payload;

use libphonenumber\PhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use PrestaShop\Module\PrestashopCheckout\Configuration\PrestaShopConfiguration;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PayPal\PayPalConfiguration;
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

    /**
     * @var bool
     */
    private $isPatch;

    /**
     * @param array $cart
     * @param bool $isPatch
     */
    public function __construct(array $cart, $isPatch = false)
    {
        $this->cart = $cart;
        $this->isPatch = $isPatch;

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
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        /** @var PrestaShopConfiguration $configuration */
        $configuration = $module->getService('ps_checkout.configuration');
        /** @var PayPalConfiguration $paypalConfiguration */
        $paypalConfiguration = $module->getService('ps_checkout.paypal.configuration');

        $shopName = $configuration->get('PS_SHOP_NAME');

        /** @var PaypalAccountRepository $accountRepository */
        $accountRepository = $module->getService('ps_checkout.repository.paypal.account');
        $merchantId = $accountRepository->getMerchantId();

        $node = [
            'intent' => $paypalConfiguration->getIntent(), // capture or authorize
            'custom_id' => (string) $this->cart['cart']['id'], // id_cart or id_order // link between paypal order and prestashop order
            'invoice_id' => '',
            'description' => $this->truncate(
                'Checking out with your cart #' . $this->cart['cart']['id'] . ' from ' . $shopName,
                127
            ),
            'amount' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $this->formatAmount($this->cart['cart']['totals']['total_including_tax']['amount']),
            ],
            'payee' => [
                'merchant_id' => $merchantId,
            ],
        ];

        $psCheckoutCartCollection = new \PrestaShopCollection('PsCheckoutCart');
        $psCheckoutCartCollection->where('id_cart', '=', (int) \Context::getContext()->cart->id);

        /** @var \PsCheckoutCart|false $psCheckoutCart */
        $psCheckoutCart = $psCheckoutCartCollection->getFirst();

        if (false === $this->isPatch && false !== $psCheckoutCart && false === empty($psCheckoutCart->paypal_token)) {
            $node['token'] = $psCheckoutCart->paypal_token;
        }

        if (true === $this->isUpdate) {
            $node['id'] = $this->paypalOrderId;
        } else {
            $roundType = $paypalConfiguration->getRoundType();

            $roundMode = $paypalConfiguration->getPriceRoundMode();

            $node['roundingConfig'] = $roundType . '-' . $roundMode;
        }

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
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');

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

        $phone = !empty($this->cart['addresses']['invoice']->phone) ? $this->cart['addresses']['invoice']->phone : '';
        $phone = empty($phone) && !empty($this->cart['addresses']['invoice']->phone_mobile) ? $this->cart['addresses']['invoice']->phone_mobile : $phone;

        if (!empty($phone)) {
            try {
                $phoneUtil = PhoneNumberUtil::getInstance();
                $parsedPhone = $phoneUtil->parse($phone, $payerCountryIsoCode);
                if ($phoneUtil->isValidNumber($parsedPhone)) {
                    $node['payer']['phone']['phone_number']['national_number'] = $parsedPhone->getNationalNumber();
                    switch ($phoneUtil->getNumberType($parsedPhone)) {
                        case PhoneNumberType::MOBILE:
                            $node['payer']['phone']['phone_type'] = 'MOBILE';
                            break;
                        case PhoneNumberType::PAGER:
                            $node['payer']['phone']['phone_type'] = 'PAGER';
                            break;
                        default:
                            $node['payer']['phone']['phone_type'] = 'OTHER';
                    }
                }
            } catch (\Exception $exception) {
                $module->getLogger()->warning(
                    'Unable to format phone number on PayPal Order payload',
                    [
                        'type' => $this->isUpdate ? 'UPDATE' : 'CREATE',
                        'paypal_order' => $this->paypalOrderId,
                        'id_cart' => (int) $this->cart['cart']['id'],
                        'address_id' => (int) $this->cart['addresses']['invoice']->id,
                        'phone' => $phone,
                        'exception' => $exception,
                    ]
                );
            }
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
     * Build the amount breakdown node
     */
    public function buildAmountBreakdownNode()
    {
        $node = [];
        $amountTotal = $this->cart['cart']['totals']['total_including_tax']['amount'];
        $breakdownItemTotal = 0;
        $breakdownTaxTotal = 0;
        $breakdownShipping = $this->cart['cart']['shipping_cost'];
        $breakdownHandling = 0;
        $breakdownDiscount = 0;

        foreach ($this->cart['products'] as $product => $value) {
            $sku = '';
            $totalWithoutTax = $value['total'];
            $totalWithTax = $value['total_wt'];
            $totalTax = $totalWithTax - $totalWithoutTax;
            $quantity = $value['quantity'];
            $unitPriceWithoutTax = $this->formatAmount($totalWithoutTax / $quantity);
            $unitTax = $this->formatAmount($totalTax / $quantity);
            $breakdownItemTotal += $unitPriceWithoutTax * $quantity;
            $breakdownTaxTotal += $unitTax * $quantity;

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

            $paypalItem = [];
            $paypalItem['name'] = $this->truncate($value['name'], 127);
            $paypalItem['description'] = false === empty($value['attributes']) ? $this->truncate($value['attributes'], 127) : '';
            $paypalItem['sku'] = $this->truncate($sku, 127);
            $paypalItem['unit_amount']['currency_code'] = $this->cart['currency']['iso_code'];
            $paypalItem['unit_amount']['value'] = $unitPriceWithoutTax;
            $paypalItem['tax']['currency_code'] = $this->cart['currency']['iso_code'];
            $paypalItem['tax']['value'] = $unitTax;
            $paypalItem['quantity'] = $quantity;
            $paypalItem['category'] = $value['is_virtual'] === '1' ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS';

            $node['items'][] = $paypalItem;
        }

        $node['amount']['breakdown'] = [
            'item_total' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $this->formatAmount($breakdownItemTotal),
            ],
            'shipping' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $this->formatAmount($breakdownShipping),
            ],
            'tax_total' => [
                'currency_code' => $this->cart['currency']['iso_code'],
                'value' => $this->formatAmount($breakdownTaxTotal),
            ],
        ];

        // set handling cost id needed -> principally used in case of gift_wrapping
        if (!empty($this->cart['cart']['subtotals']['gift_wrapping'])) {
            $breakdownHandling += $this->cart['cart']['subtotals']['gift_wrapping'];
        }

        $remainderValue = $amountTotal - $breakdownItemTotal - $breakdownTaxTotal - $breakdownShipping - $breakdownHandling;

        // In case of rounding issue, if remainder value is negative we use discount value to deduct remainder and if remainder value is positive we use handling value to add remainder
        if ($remainderValue < 0) {
            $breakdownDiscount += abs($remainderValue);
        } else {
            $breakdownHandling += $remainderValue;
        }

        $node['amount']['breakdown']['discount'] = [
            'currency_code' => $this->cart['currency']['iso_code'],
            'value' => $this->formatAmount($breakdownDiscount),
        ];

        $node['amount']['breakdown']['handling'] = [
            'currency_code' => $this->cart['currency']['iso_code'],
            'value' => $this->formatAmount($breakdownHandling),
        ];

        $this->getPayload()->addAndMergeItems($node);
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
     * @return int
     */
    private function getNbDecimalToRound()
    {
        if (in_array($this->cart['currency']['iso_code'], ['HUF', 'JPY', 'TWD'], true)) {
            return 0;
        }

        return 2;
    }

    /**
     * @param float|int|string $amount
     *
     * @return string
     */
    private function formatAmount($amount)
    {
        return sprintf("%01.{$this->getNbDecimalToRound()}f", $amount);
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
        return strtoupper(\Country::getIsoById($countryId));
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
