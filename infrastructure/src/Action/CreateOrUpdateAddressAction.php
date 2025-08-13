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

namespace PsCheckout\Infrastructure\Action;

use Address;
use Country;
use Exception;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutRequest;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CountryInterface;
use PsCheckout\Infrastructure\Repository\AddressRepositoryInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Utility\Payload\PaypalCountryCodeUtility;

class CreateOrUpdateAddressAction implements CreateOrUpdateAddressActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var CountryInterface
     */
    private $country;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    public function __construct(
        ContextInterface $context,
        CountryInterface $country,
        CountryRepositoryInterface $countryRepository,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->context = $context;
        $this->country = $country;
        $this->countryRepository = $countryRepository;
        $this->addressRepository = $addressRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ExpressCheckoutRequest $expressCheckoutRequest)
    {
        // check if country is available for delivery
        $shopIsoCode = PaypalCountryCodeUtility::getShopIsoCode($expressCheckoutRequest->getShippingCountryCode());

        $idCountry = $this->country->getIdByIsoCode($shopIsoCode);
        $idState = 0;
        $country = new Country((int) $idCountry, null, (int) $this->context->getShop()->id);

        if (!$country->id
            || !$country->active
            || !$country->isAssociatedToShop((int) $this->context->getShop()->id)
            || $this->country->isNeedDniByCountryId($idCountry)
        ) {
            return false;
        }

        if ($country->contains_states) {
            $idState = $this->countryRepository->getStateId((int) $idCountry, $expressCheckoutRequest->getShippingState());
        }

        // check if a PayPal address already exist for the customer and not used
        $paypalAddressAlias = 'Paypal ' . $expressCheckoutRequest->getOrderId();
        $paypalAddressId = $this->addressRepository->getAddressIdByAliasAndCustomer($paypalAddressAlias, $this->context->getCustomer()->id);
        $paypalAddress = new Address($paypalAddressId);
        $isPaypalValidAddressAndNotUsed = $paypalAddress->id && !$paypalAddress->isUsed();

        if ($isPaypalValidAddressAndNotUsed) {
            $address = $paypalAddress; // if yes, update it with the new address
        } else {
            $address = new Address(); // otherwise create a new address
        }

        $address->alias = $paypalAddressAlias;
        $address->id_customer = $this->context->getCustomer()->id;
        $address->firstname = $expressCheckoutRequest->getPayerFirstName();
        $address->lastname = $expressCheckoutRequest->getPayerLastName();
        $address->address1 = $expressCheckoutRequest->getShippingStreet();
        $address->address2 = $expressCheckoutRequest->getShippingStreet2();
        $address->postcode = $expressCheckoutRequest->getShippingPostalCode();
        $address->city = $expressCheckoutRequest->getShippingCity();
        $address->id_country = $idCountry;
        $address->phone = $expressCheckoutRequest->getPayerPhone();
        $address->id_state = $idState;

        if (!$address->validateFields(false)) {
            return false;
        }

        try {
            $address->save();
        } catch (Exception $exception) {
            throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_ADDRESS, $exception);
        }

        $cart = $this->context->getCart();
        $cart->id_address_delivery = $address->id;
        $cart->id_address_invoice = $address->id;

        $products = $cart->getProducts();
        foreach ($products as $product) {
            $cart->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $address->id);
        }

        return $cart->save();
    }
}
