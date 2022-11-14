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

namespace PrestaShop\Module\PrestashopCheckout\Builder\Address;

use Address;
use Country;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;
use PrestaShop\Module\PrestashopCheckout\PaypalCountryCodeMatrice;
use PrestaShop\Module\PrestashopCheckout\Repository\CheckoutAddresRepository;

class OrderAddressBuilder extends AddressBuilder
{
    /**
     * @var CheckoutAddress
     */
    public $checkoutAddress;

    public function __construct(CheckoutAddress $checkoutAddress)
    {
        $this->checkoutAddress = $checkoutAddress;
    }

    /**
     * Create address
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function createAddress($id_customer)
    {
        // check if country is available for delivery
        $psIsoCode = (new PaypalCountryCodeMatrice())->getPrestashopIsoCode($this->checkoutAddress->id_country);
        $idCountry = Country::getByIso($psIsoCode);

        $country = new Country((int) $idCountry);

        if (!$country->active || Country::isNeedDniByCountryId($idCountry)) {
            return false;
        }
        /** @var \Ps_checkout $module */
        $module = \Module::getInstanceByName('ps_checkout');
        /** @var \PrestaShop\Module\PrestashopCheckout\Repository\CountryRepository $countryRepository */
        $countryRepository = $module->getService('ps_checkout.repository.country');

        $idState = $countryRepository->getStateId($this->checkoutAddress->id_state);

        // check if a paypal address already exist for the customer
        $repository = new CheckoutAddresRepository();
        $paypalAddress = $repository->addressAlreadyExist('PayPal', $id_customer);

        if ($paypalAddress) {
            $address = new Address($paypalAddress); // if yes, update it with the new address
        } else {
            $address = new Address(); // otherwise create a new address
        }

        $address->alias = $this->createAddressAlias();
        $address->id_customer = $id_customer;
        $address->firstname = $this->checkoutAddress->firstname;
        $address->lastname = $this->checkoutAddress->lastname;
        $address->address1 = $this->checkoutAddress->address1;
        $address->address2 = $this->checkoutAddress->address2;
        $address->postcode = $this->checkoutAddress->postcode;
        $address->city = $this->checkoutAddress->city;
        $address->id_country = $idCountry;
        $address->phone = $this->checkoutAddress->phone;

        if ($idState) {
            $address->id_state = $idState;
        }

        if (!$address->validateFields(false)) {
            return false;
        }

        try {
            $address->save();
        } catch (\Exception $exception) {
            throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_ADDRESS, $exception);
        }
        $context = \Context::getContext();

        $context->cart->id_address_delivery = $address->id;
        $context->cart->id_address_invoice = $address->id;

        $products = $context->cart->getProducts();
        foreach ($products as $product) {
            $context->cart->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $address->id);
        }

        return $context->cart->save();
    }

    public function createAddressAlias()
    {
        return substr($this->checkoutAddress->firstname, 0, 2) .
            substr($this->checkoutAddress->lastname, 0, 2) .
            $this->checkoutAddress->postcode .
            substr($this->checkoutAddress->address1, 0, 2);
    }
}
