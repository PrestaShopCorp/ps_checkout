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

use Country;
use PrestaShop\Module\PrestashopCheckout\Adapter\AddressAdapter;
use PrestaShop\Module\PrestashopCheckout\Exception\PsCheckoutException;

class PaypalAddressBuilder extends AddressBuilder
{
    /**
     * Create address
     *
     * @return bool
     *
     * @throws PsCheckoutException
     */
    public function createAddress($id_customer)
    {
        if (!$this->checkoutAddress->country->getField('active') || Country::isNeedDniByCountryId($this->idCountry)) {
            return false;
        }

        // check if a paypal address already exist for the customer
        $checkSum = $this->generateChecksum();
        $alias = $this->checkoutAddressRepository->retrieveCheckoutAdressAlias($checkSum);
        $paypalAddress = $this->checkoutAddressRepository->addressAlreadyExist($alias, $id_customer);

        if ($paypalAddress) {
            $addressAdapter = new AddressAdapter($paypalAddress); // if yes, update it with the new address
        } else {
            $addressAdapter = new AddressAdapter(); // otherwise create a new address
            $this->checkoutAddressRepository->addChecksum($id_customer, $addressAdapter->getField('alias'), $checkSum);
            $addressAdapter->fillWith((array) $this->checkoutAddress);
            $addressAdapter->setField('alias', $this->createAddressAlias());
        }

        if ($this->idState) {
            $addressAdapter->setField('id_state', $this->idState);
        }

        if (!$addressAdapter->isValid()) {
            return false;
        }

        try {
            $addressAdapter->save();
        } catch (\Exception $exception) {
            throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_ADDRESS, $exception);
        }
        $context = \Context::getContext();

        $context->cart->id_address_delivery = $addressAdapter->getField('id');
        $context->cart->id_address_invoice = $addressAdapter->getField('id');

        $products = $context->cart->getProducts();
        foreach ($products as $product) {
            $context->cart->setProductAddressDelivery(
                $product['id_product'], $product['id_product_attribute'],
                $product['id_address_delivery'], $addressAdapter->getField('id'
                ));
        }

        return $context->cart->save();
    }
}
