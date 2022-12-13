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
        if (!$this->countryAdapter->getField('active') ||
            $this->countryAdapter->isNeedDniByCountryId($this->checkoutAddress->id_country)) {
            return false;
        }

        // check if a paypal address already exist for the customer
        $checkSum = $this->checkoutAddress->generateChecksum();
        $alias = $this->addressRepository->retrieveCheckoutAdressAlias($checkSum);
        $id_address = $this->addressRepository->addressAlreadyExist($alias, $id_customer);

        if ($id_address) {
            $this->addressAdapter = new AddressAdapter($id_address); // if yes, update it with the new address
        } else {
            $this->addressAdapter = new AddressAdapter(); // otherwise create a new address
            $this->addressRepository->addChecksum($id_customer, $this->addressAdapter->getField('alias'), $checkSum);
            $this->addressAdapter->fillWith((array) $this->checkoutAddress);
            $this->addressAdapter->setField('alias', $this->checkoutAddress->createAddressAlias());
        }

        if ($this->checkoutAddress->id_state) {
            $this->addressAdapter->setField('id_state', $this->checkoutAddress->id_state);
        }

        if (!$this->addressAdapter->isValid()) {
            return false;
        }

        try {
            $this->addressAdapter->save();
        } catch (\Exception $exception) {
            throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_ADDRESS, $exception);
        }
        $context = \Context::getContext();

        $context->cart->id_address_delivery = $this->addressAdapter->getField('id');
        $context->cart->id_address_invoice = $this->addressAdapter->getField('id');

        $products = $context->cart->getProducts();
        foreach ($products as $product) {
            $context->cart->setProductAddressDelivery(
                $product['id_product'],
                $product['id_product_attribute'],
                $product['id_address_delivery'],
                $this->addressAdapter->getField(
                    'id'
                )
            );
        }

        return $context->cart->save();
    }
}
