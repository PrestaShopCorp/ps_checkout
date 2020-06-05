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

use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;
use PrestaShop\Module\PrestashopCheckout\PaypalCountryCodeMatrice;

class ps_checkoutExpressCheckoutModuleFrontController extends ModuleFrontController
{
    /**
     * @var Ps_checkout
     */
    public $module;

    /**
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function postProcess()
    {
        $isAjax = Tools::getValue('ajax');

        if ($isAjax) {
            return;
        }

        $token = Tools::getValue('expressCheckoutToken');

        if ($token !== Tools::getToken()) {
            throw new PrestaShopException('Bad token');
        }

        $paypalOrder = Tools::getValue('paypalOrder');

        if (empty($paypalOrder)) {
            throw new PrestaShopException('Paypal order cannot be empty');
        }

        $paypalOrder = json_decode($paypalOrder, true);

        if (false === $this->context->customer->isLogged()) {
            // @todo Extract factory in a Service.
            $this->createAndLoginCustomer(
                $paypalOrder['payer']['email_address'],
                $paypalOrder['payer']['name']['given_name'],
                $paypalOrder['payer']['name']['surname']
            );
        }

        // Always 0 index because we are not using the paypal marketplace system
        // This index is only used in a marketplace context
        // @todo Extract factory in a Service.
        $this->createAddress(
            $paypalOrder['payer']['name']['given_name'],
            $paypalOrder['payer']['name']['surname'],
            $paypalOrder['purchase_units'][0]['shipping']['address']['address_line_1'],
            false === empty($paypalOrder['purchase_units'][0]['shipping']['address']['address_line_2']) ? $paypalOrder['purchase_units'][0]['shipping']['address']['address_line_2'] : '',
            $paypalOrder['purchase_units'][0]['shipping']['address']['postal_code'],
            $paypalOrder['purchase_units'][0]['shipping']['address']['admin_area_2'],
            $paypalOrder['purchase_units'][0]['shipping']['address']['country_code'],
            false === empty($paypalOrder['payer']['phone']) ? $paypalOrder['payer']['phone']['phone_number']['national_number'] : ''
        );

        $this->context->cookie->__set('paypalOrderId', $paypalOrder['id']);
        $this->context->cookie->__set('paypalEmail', $paypalOrder['payer']['email_address']);

        $this->module->getLogger()->info(sprintf(
            'Express checkout - token : %s PayPal Order : %s',
            $token,
            $paypalOrder->id
        ));

        $this->redirectToCheckout();
    }

    /**
     * Handle creation and customer login
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     *
     * @throws PrestaShopException
     */
    private function createAndLoginCustomer(
        $email,
        $firstName,
        $lastName
    ) {
        // Note this controller is used only start PrestaShop 1.7
        if (false === method_exists('Customer', 'customerExists')
            || false === method_exists($this->context, 'updateCustomer')
        ) {
            return;
        }

        $idCustomerExists = Customer::customerExists($email, true);

        $customer = (0 !== $idCustomerExists)
            ? new Customer($idCustomerExists)
            : $this->createCustomer(
                $email,
                $firstName,
                $lastName
            )
        ;

        $this->context->updateCustomer($customer);
    }

    /**
     * Create a customer
     *
     * @todo Extract factory in a Service.
     *
     * @param string $email
     * @param string $firstName
     * @param string $lastName
     *
     * @return Customer
     */
    private function createCustomer($email, $firstName, $lastName)
    {
        $customer = new Customer();
        $customer->email = $email;
        $customer->firstname = $firstName;
        $customer->lastname = $lastName;
        $customer->passwd = Tools::passwdGen();
        $customer->save();

        return $customer;
    }

    /**
     * Create address
     *
     * @todo Extract factory in a Service.
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $address1
     * @param string $address2
     * @param string $postcode
     * @param string $city
     * @param string $countryIsoCode
     * @param string $phone
     *
     * @return bool
     */
    private function createAddress(
        $firstName,
        $lastName,
        $address1,
        $address2,
        $postcode,
        $city,
        $countryIsoCode,
        $phone
    ) {
        // check if country is available for delivery
        $psIsoCode = (new PaypalCountryCodeMatrice())->getPrestashopIsoCode($countryIsoCode);
        $idCountry = Country::getByIso($psIsoCode);

        $country = new Country($idCountry);

        if (0 === (int) $country->active) {
            return false;
        }

        // check if a paypal address already exist for the customer
        $paypalAddress = $this->getPayPalAddressId('PayPal', $this->context->customer->id);
        $address = (0 !== $paypalAddress) ? new Address($paypalAddress) : new Address();

        $address->alias = 'PayPal';
        $address->id_customer = $this->context->customer->id;
        $address->firstname = $firstName;
        $address->lastname = $lastName;
        $address->address1 = $address1;
        $address->address2 = $address2;
        $address->postcode = $postcode;
        $address->city = $city;
        $address->id_country = $idCountry;
        $address->phone = $phone;
        $address->save();

        $this->context->cart->id_address_delivery = $address->id;
        $this->context->cart->id_address_invoice = $address->id;

        $products = $this->context->cart->getProducts();
        foreach ($products as $product) {
            $this->context->cart->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $address->id);
        }

        return $this->context->cart->save();
    }

    /**
     * @param string $alias
     * @param int $id_customer
     *
     * @return int Address identifier
     */
    private function getPayPalAddressId($alias, $id_customer)
    {
        $query = new DbQuery();
        $query->select('id_address');
        $query->from('address');
        $query->where('alias = \'' . pSQL($alias) . '\'');
        $query->where('id_customer = ' . (int) $id_customer);
        $query->where('deleted = 0');

        return (int) Db::getInstance()->getValue($query);
    }

    /**
     * Ajax: Create and return paypal order
     *
     * @throws PsCheckoutException
     */
    public function displayAjaxCreatePaypalOrder()
    {
        $product = Tools::getValue('product');

        if (!empty($product)) {
            $product = json_decode($product);

            $cart = new Cart();
            $cart->id_currency = $this->context->currency->id;
            $cart->id_lang = $this->context->language->id;
            $cart->add();
            $cart->updateQty(
                $product->quantity_wanted,
                $product->id_product,
                $product->id_product_attribute === '0' ? null : $product->id_product_attribute,
                $product->id_customization === 0 ? false : $product->id_customization,
                $operator = 'up'
            );
            $cart->update();

            $this->module->getLogger()->info(sprintf(
                'Express checkout : Create Cart %s',
                (int) $cart->id
            ));

            $this->context->cart = $cart;
            $this->context->cookie->__set('id_cart', $cart->id);
        }

        $paypalOrder = new CreatePaypalOrderHandler($this->context);
        $paypalOrder = $paypalOrder->handle(true);

        echo json_encode($paypalOrder);
    }

    private function redirectToCheckout()
    {
        Tools::redirect(
            $this->context->link->getPageLink(
                'order',
                true,
                $this->context->language->id
            )
        );
    }
}
