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
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutShippingData;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Adapter\CountryInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Repository\PsCheckoutAddressRepositoryInterface;
use PsCheckout\Utility\Payload\PaypalAddressRequirementsUtility;
use PsCheckout\Utility\Payload\PaypalCountryCodeUtility;
use PsCheckout\Utility\Payload\PaypalStateCodeMapUtility;

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
     * @var PsCheckoutAddressRepositoryInterface
     */
    private $psCheckoutAddressRepository;

    public function __construct(
        ContextInterface $context,
        CountryInterface $country,
        CountryRepositoryInterface $countryRepository,
        PsCheckoutAddressRepositoryInterface $psCheckoutAddressRepository
    ) {
        $this->context = $context;
        $this->country = $country;
        $this->countryRepository = $countryRepository;
        $this->psCheckoutAddressRepository = $psCheckoutAddressRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ExpressCheckoutShippingData $shippingData)
    {
        if (!$shippingData->getCountryCode()) {
            return false;
        }

        // check if country is available for delivery
        $shopIsoCode = PaypalCountryCodeUtility::getShopIsoCode($shippingData->getCountryCode());

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
            $state = $shippingData->getState();
            if ($state !== null && $state !== '') {
                if (PaypalAddressRequirementsUtility::usesStateIsoCode($shopIsoCode)) {
                    $psIsoCode = PaypalStateCodeMapUtility::getShopStateCode($shopIsoCode, $state);
                    $idState = $this->countryRepository->getStateIdByIsoCode((int) $idCountry, $psIsoCode);
                } else {
                    $idState = $this->countryRepository->getStateId((int) $idCountry, $state);
                }
            }
        }

        $idCustomer = (int) $this->context->getCustomer()->id;

        if ($idCustomer === 0) {
            return false;
        }

        $checksum = md5((string) json_encode([
            'id_customer' => $idCustomer,
            'firstname' => $shippingData->getFirstName(),
            'lastname' => $shippingData->getLastName(),
            'address1' => $shippingData->getStreet(),
            'address2' => $shippingData->getStreet2(),
            'postcode' => $shippingData->getPostalCode(),
            'city' => $shippingData->getCity(),
            'id_country' => (int) $idCountry,
            'id_state' => (int) $idState,
            'phone' => $shippingData->getPhone(),
        ]));

        $existingAddressId = $this->psCheckoutAddressRepository->getAddressIdByChecksumAndCustomer($checksum, $idCustomer);

        if ($existingAddressId > 0) {
            $address = new Address($existingAddressId);
        } else {
            $address = new Address();
            $address->alias = 'Paypal ' . $shippingData->getOrderId();
            $address->id_customer = $idCustomer;
            $address->firstname = $shippingData->getFirstName();
            $address->lastname = $shippingData->getLastName();
            $address->address1 = $shippingData->getStreet();
            $address->address2 = $shippingData->getStreet2();
            $address->postcode = $shippingData->getPostalCode();
            $address->city = $shippingData->getCity();
            $address->id_country = $idCountry;
            $address->phone = $shippingData->getPhone();
            $address->id_state = $idState;

            if (!$address->validateFields(false)) {
                return false;
            }

            try {
                $address->save();
            } catch (Exception $exception) {
                throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_ADDRESS, $exception);
            }

            if (!$this->psCheckoutAddressRepository->saveAddress($address->id, $idCustomer, $checksum)) {
                \PrestaShopLogger::addLog(
                    'CreateOrUpdateAddressAction: failed to track address ' . (int) $address->id . ' for customer ' . $idCustomer,
                    3
                );
            }
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
