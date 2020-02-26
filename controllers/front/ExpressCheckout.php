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
use PrestaShop\Module\PrestashopCheckout\Handler\CreatePaypalOrderHandler;
use PrestaShop\Module\PrestashopCheckout\PaypalCountryCodeMatrice;
use PrestaShop\Module\PrestashopCheckout\PaypalOrder;

class ps_checkoutExpressCheckoutModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $isAjax = \Tools::getValue('ajax');

        if ($isAjax) {
            return false;
        }

        $token = \Tools::getValue('expressCheckoutToken');

        if ($token !== \Tools::getToken()) {
            throw new \PrestaShopException('Bad token');
        }

        $paypalOrder = \Tools::getValue('paypalOrder');

        if (empty($paypalOrder)) {
            throw new \PrestaShopException('Paypal order cannot be empty');
        }

        $paypalOrder = json_decode($paypalOrder);

        if (false === $this->context->customer->isLogged()) {
            $this->createAndLoginCustomer($paypalOrder->payer);
        }

        // Always 0 index because we are not using the paypal marketplace system
        // This index is only used in a marketplace context
        $this->createAddress($paypalOrder->purchase_units[0]->shipping);

        $this->context->cookie->__set('paypalOrderId', $paypalOrder->id);
        $this->context->cookie->__set('paypalEmail', $paypalOrder->payer->email_address);

        $this->redirectToCheckout();
    }

    /**
     * Handle creation and customer login
     *
     * @param object $payer
     */
    private function createAndLoginCustomer($payer)
    {
        $idCustomerExists = \Customer::customerExists($payer->email_address, true);

        if (0 === $idCustomerExists) {
            $customer = $this->createCustomer($payer);
        } else {
            $customer = new \Customer((int) $idCustomerExists);
        }

        $this->context->updateCustomer($customer);
    }

    /**
     * Create a customer
     *
     * @param object $payerNode
     *
     * @return \Customer
     */
    public function createCustomer($payerNode)
    {
        $customer = new \Customer();
        $customer->email = $payerNode->email_address;
        $customer->firstname = $payerNode->name->given_name;
        $customer->lastname = $payerNode->name->surname;
        $customer->passwd = \Tools::passwdGen();
        $customer->save();

        return $customer;
    }

    /**
     * Create address
     *
     * @param object $shipping
     */
    private function createAddress($shipping)
    {
        // check if country is available for delivery
        $paypalIsoCode = $shipping->address->country_code;
        $psIsoCode = (new PaypalCountryCodeMatrice())->getPrestashopIsoCode($paypalIsoCode);
        $idCountry = \Country::getByIso($psIsoCode);

        $country = new \Country($idCountry);

        if (0 === (int) $country->active) {
            return false;
        }

        // check if a paypal address already exist for the customer
        $paypalAddress = $this->addressAlreadyExist('PayPal', $this->context->customer->id);

        if (false !== $paypalAddress) {
            $address = new \Address($paypalAddress); // if yes, update it with the new address
        } else {
            $address = new \Address(); // otherwise create a new address
        }

        $address->alias = 'PayPal';
        $address->id_customer = $this->context->customer->id;
        $address->firstname = strstr($shipping->name->full_name, ' ', true);
        $address->lastname = strstr($shipping->name->full_name, ' ');
        $address->address1 = $shipping->address->address_line_1;

        if (isset($shipping->address->address_line_2) && !empty($shipping->address->address_line_2)) {
            $address->address2 = $shipping->address->address_line_2;
        }

        $address->postcode = $shipping->address->postal_code;
        $address->city = $shipping->address->admin_area_2;
        $address->id_country = $idCountry;
        $address->save();

        $this->context->cart->id_address_delivery = $address->id;
        $this->context->cart->id_address_invoice = $address->id;

        $products = $this->context->cart->getProducts();
        foreach ($products as $product) {
            $this->context->cart->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $address->id);
        }

        $this->context->cart->save();
    }

    /**
     * Check if address already exist, if yes return the id_address
     *
     * @param string $alias
     * @param int $id_customer
     *
     * @return int
     */
    private function addressAlreadyExist($alias, $id_customer)
    {
        $query = new DbQuery();
        $query->select('id_address');
        $query->from('address');
        $query->where('alias = \'' . pSQL($alias) . '\'');
        $query->where('id_customer = ' . (int) $id_customer);
        $query->where('deleted = 0');

        return Db::getInstance()->getValue($query);
    }

    /**
     * Ajax: Create and return paypal order
     *
     * @return string $paypalOrder
     */
    public function displayAjaxCreatePaypalOrder()
    {
        $product = \Tools::getValue('product');

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
