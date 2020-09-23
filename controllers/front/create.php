<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use PrestaShop\Decimal\Number;
use PrestaShop\Decimal\Operation\Rounding;
use PrestaShop\Module\PrestashopCheckout\Api\Payment\Order;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

/**
 * This controller receive ajax call to create a PayPal Order
 */
class Ps_CheckoutCreateModuleFrontController extends ModuleFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @see FrontController::postProcess()
     *
     * @todo Move logic to a Service
     */
    public function postProcess()
    {
        header('content-type:application/json');

        try {
            if (false === $this->checkIfContextIsValid()) {
                throw new PsCheckoutException('The context is not valid', PsCheckoutException::PRESTASHOP_CONTEXT_INVALID);
            }

            if (false === $this->checkIfPaymentOptionIsAvailable()) {
                throw new PsCheckoutException('This payment method is not available.', PsCheckoutException::PRESTASHOP_PAYMENT_UNAVAILABLE);
            }

            $psCheckoutCartCollection = new PrestaShopCollection('PsCheckoutCart');
            $psCheckoutCartCollection->where('id_cart', '=', (int) $this->context->cart->id);

            /** @var PsCheckoutCart|false $psCheckoutCart */
            $psCheckoutCart = $psCheckoutCartCollection->getFirst();

            if (false !== $psCheckoutCart && false === empty($psCheckoutCart->paypal_order)) {
                // @todo Check if PayPal Order status before reuse it
                header('content-type:application/json');
                echo json_encode([
                    'status' => true,
                    'httpCode' => 200,
                    'body' => [
                        'orderID' => $psCheckoutCart->paypal_order,
                    ],
                    'exceptionCode' => null,
                    'exceptionMessage' => null,
                ]);
                exit;
            }

            $response = (new Order($this->context->link))->create(json_encode($this->buildPayload()));

            if (false === $response['status']) {
                throw new PsCheckoutException($response['exceptionMessage'], (int) $response['exceptionCode']);
            }

            if (empty($response['body']['id'])) {
                throw new PsCheckoutException('Paypal order id is missing.', PsCheckoutException::PAYPAL_ORDER_IDENTIFIER_MISSING);
            }

            if (false === $psCheckoutCart) {
                $psCheckoutCart = new PsCheckoutCart();
                $psCheckoutCart->id_cart = (int) $this->context->cart->id;
            }

            $psCheckoutCart->paypal_order = $response['body']['id'];
            $psCheckoutCart->paypal_status = $response['body']['status'];
            $psCheckoutCart->paypal_intent = 'CAPTURE' === Configuration::get('PS_CHECKOUT_INTENT') ? 'CAPTURE' : 'AUTHORIZE';
            $psCheckoutCart->paypal_token = $response['body']['client_token'];
            $psCheckoutCart->paypal_token_expire = (new DateTime())->modify('+3550 seconds')->format('Y-m-d H:i:s');
            $psCheckoutCart->save();

            //@todo remove cookie
            $this->context->cookie->__set('ps_checkout_orderId', $response['body']['id']);

            echo json_encode([
                'status' => true,
                'httpCode' => 200,
                'body' => [
                    'orderID' => $response['body']['id'],
                ],
                'exceptionCode' => null,
                'exceptionMessage' => null,
            ]);
        } catch (Exception $exception) {
            header('HTTP/1.0 400 Bad Request');

            echo json_encode([
                'status' => false,
                'httpCode' => 400,
                'body' => '',
                'exceptionCode' => $exception->getCode(),
                'exceptionMessage' => $exception->getMessage(),
            ]);
        }

        exit;
    }

    /**
     * Check if the context is valid
     *
     * @todo Move to main module class
     *
     * @return bool
     */
    private function checkIfContextIsValid()
    {
        return true === Validate::isLoadedObject($this->context->cart)
            && true === Validate::isUnsignedInt($this->context->cart->id_customer)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_delivery)
            && true === Validate::isUnsignedInt($this->context->cart->id_address_invoice);
    }

    /**
     * Check that this payment option is still available in case the customer changed
     * his address just before the end of the checkout process
     *
     * @todo Move to main module class
     *
     * @return bool
     */
    private function checkIfPaymentOptionIsAvailable()
    {
        $modules = Module::getPaymentModules();

        if (empty($modules)) {
            return false;
        }

        foreach ($modules as $module) {
            if (isset($module['name']) && $this->module->name === $module['name']) {
                return true;
            }
        }

        return false;
    }

    /**
     * @todo Create Service
     *
     * @return array
     *
     * @throws Exception
     * @throws PrestaShopException
     */
    private function buildPayload()
    {
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\PaypalAccountRepository $paypalAccountRepository */
        $paypalAccountRepository = $this->module->getService('ps_checkout.repository.paypal.account');

        $products = $this->context->cart->getProducts();

        $totalOrderWithTax = $this->context->cart->getOrderTotal(true);
        $totalShipping = $this->context->cart->getTotalShippingCost(null, true);
        $totalHandling = $this->context->cart->getGiftWrappingPrice(true);
        $totalItemWithoutTax = 0;
        $totalTax = 0;
        $gender = new Gender($this->context->customer->id_gender, $this->context->cart->id_lang);
        $invoiceAddress = Address::initialize($this->context->cart->id_address_invoice);
        $shippingAddress = Address::initialize($this->context->cart->id_address_delivery);
        $shippingPhoneType = $shippingAddress->phone_mobile ? 'MOBILE' : 'HOME';
        $shippingPhone = '';

        // @todo Check phone formatting
        if (false === empty($shippingAddress->phone)) {
            $shippingPhone = $shippingAddress->phone;
        }

        // @todo Check phone formatting
        if (false === empty($shippingAddress->phone_mobile)) {
            $shippingPhone = $shippingAddress->phone_mobile;
        }

        foreach ($products as $product) {
            $unitProductPriceWithoutTax = $product['total'] / $product['quantity'];
            $totalItemWithoutTax += $unitProductPriceWithoutTax * $product['quantity'];
            $unitTaxAmount = ($product['total_wt'] - $product['total']) / $product['quantity'];
            $totalTax += $unitTaxAmount * $product['quantity'];
        }

        $totalDiscount = abs($totalOrderWithTax - $totalItemWithoutTax - $totalTax - $totalShipping - $totalHandling);

        $payload = [
            'intent' => 'CAPTURE' === Configuration::get('PS_CHECKOUT_INTENT') ? 'CAPTURE' : 'AUTHORIZE',
            'application_context' => [
                'brand_name' => $this->context->shop->name,
                'shipping_preference' => 'SET_PROVIDED_ADDRESS',
            ],
            'payer' => [
                'name' => [
                    'given_name' => $invoiceAddress->firstname,
                    'surname' => $invoiceAddress->lastname,
                ],
                'email_address' => $this->context->customer->email,
                'birth_date' => (false === empty($this->context->customer->birthday) && '0000-00-00' !== $this->context->customer->birthday) ? $this->context->customer->birthday : '',
                'address' => [
                    'address_line_1' => $invoiceAddress->address1,
                    'address_line_2' => (string) $invoiceAddress->address2,
                    'admin_area_1' => (string) State::getNameById($invoiceAddress->id_state),
                    'admin_area_2' => $invoiceAddress->city,
                    'country_code' => Country::getIsoById($invoiceAddress->id_country),
                    'postal_code' => $invoiceAddress->postcode,
                ],
                'phone' => [
                    'phone_type' => $shippingPhoneType,
                    'phone_number' => [
                        'national_number' => $shippingPhone,
                    ],
                ],
            ],
            'custom_id' => (string) $this->context->cart->id,
            'description' => $this->truncate('Checking out with your cart from ' . $this->context->shop->name, 127),
            'soft_descriptor' => $this->truncate($this->context->shop->name, 22),
            'payee' => [
                'merchant_id' => $paypalAccountRepository->getMerchantId(),
            ],
            'amount' => [
                'currency_code' => $this->context->currency->iso_code,
                'value' => (new Number((string) $totalOrderWithTax))->round(2, $this->getRoundMode()),
                'breakdown' => [
                    'item_total' => [
                        'currency_code' => $this->context->currency->iso_code,
                        'value' => (new Number((string) $totalItemWithoutTax))->round(2, $this->getRoundMode()),
                    ],
                    'shipping' => [
                        'currency_code' => $this->context->currency->iso_code,
                        'value' => (new Number((string) $totalShipping))->round(2, $this->getRoundMode()),
                    ],
                    'tax_total' => [
                        'currency_code' => $this->context->currency->iso_code,
                        'value' => (new Number((string) $totalTax))->round(2, $this->getRoundMode()),
                    ],
                    'discount' => [
                        'currency_code' => $this->context->currency->iso_code,
                        'value' => (new Number((string) $totalDiscount))->round(2, $this->getRoundMode()),
                    ],
                    'handling' => [
                        'currency_code' => $this->context->currency->iso_code,
                        'value' => (new Number((string) $totalHandling))->round(2, $this->getRoundMode()),
                    ],
                ],
            ],
            'shipping' => [
                'name' => [
                    'full_name' => $gender->name . ' ' . $shippingAddress->lastname . ' ' . $shippingAddress->firstname,
                ],
                'address' => [
                    'address_line_1' => $shippingAddress->address1,
                    'address_line_2' => (string) $shippingAddress->address2,
                    'admin_area_1' => (string) State::getNameById($shippingAddress->id_state),
                    'admin_area_2' => $shippingAddress->city,
                    'country_code' => Country::getIsoById($shippingAddress->id_country),
                    'postal_code' => $shippingAddress->postcode,
                ],
            ],
        ];

        foreach ($products as $product) {
            $amountUnit = $product['total'] / $product['quantity'];
            $amountTax = ($product['total_wt'] - $product['total']) / $product['quantity'];
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

            $payload['items'][] = [
                'name' => $this->truncate($product['name'], 127),
                'description' => false === empty($value['attributes']) ? $this->truncate($value['attributes'], 127) : '',
                'sku' => $this->truncate($sku, 127),
                'quantity' => $product['quantity'],
                'category' => $product['is_virtual'] ? 'DIGITAL_GOODS' : 'PHYSICAL_GOODS',
                'unit_amount' => [
                    'currency_code' => $this->context->currency->iso_code,
                    'value' => (new Number((string) $amountUnit))->round(2, $this->getRoundMode()),
                ],
                'tax' => [
                    'currency_code' => $this->context->currency->iso_code,
                    'value' => (new Number((string) $amountTax))->round(2, $this->getRoundMode()),
                ],
            ];
        }

        return $payload;
    }

    /**
     * @todo Move to dedicated Order Payload factory
     *
     * @param string $str
     * @param int $limit
     *
     * @return string
     */
    private function truncate($str, $limit)
    {
        if (empty($str)) {
            return '';
        }

        return mb_substr($str, 0, $limit);
    }

    /**
     * @todo Move to dedicated Order Payload factory
     *
     * @return string
     */
    private function getRoundMode()
    {
        $roundMode = (int) Configuration::get('PS_PRICE_ROUND_MODE');

        switch ($roundMode) {
            case PS_ROUND_UP:
                return Rounding::ROUND_CEIL;
            case PS_ROUND_DOWN:
                return Rounding::ROUND_FLOOR;
            case PS_ROUND_HALF_DOWN:
                return Rounding::ROUND_HALF_DOWN;
            case PS_ROUND_HALF_EVEN:
            case PS_ROUND_HALF_ODD:
                return Rounding::ROUND_HALF_EVEN;
            case PS_ROUND_HALF_UP:
            default:
                return Rounding::ROUND_HALF_UP;
        }
    }
}
