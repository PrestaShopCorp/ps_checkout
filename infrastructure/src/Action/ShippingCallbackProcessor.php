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
use PsCheckout\Core\PayPal\ShippingCallback\Builder\PurchaseUnitsNodeBuilderInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Builder\ShippingOptionsBuilderInterface;
use PsCheckout\Core\PayPal\ShippingCallback\Exception\ShippingCallbackException;
use PsCheckout\Core\PayPal\ShippingCallback\Service\ShippingCallbackProcessorInterface;
use PsCheckout\Core\PayPal\ShippingCallback\ValueObject\ShippingCallbackPayload;
use PsCheckout\Infrastructure\Adapter\CartDataInterface;
use PsCheckout\Infrastructure\Adapter\CartInterface;
use PsCheckout\Infrastructure\Adapter\CountryInterface;
use PsCheckout\Infrastructure\Repository\AddressRepositoryInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Utility\Payload\PaypalAddressRequirementsUtility;
use PsCheckout\Utility\Payload\PaypalCountryCodeUtility;
use PsCheckout\Utility\Payload\PaypalStateCodeMapUtility;
use Psr\Log\LoggerInterface;

class ShippingCallbackProcessor implements ShippingCallbackProcessorInterface
{
    const TEMPORARY_ADDRESS_ALIAS_PREFIX = 'ps_checkout_shipping_callback_';

    /**
     * @var CartInterface
     */
    private $cartAdapter;

    /**
     * @var ShippingOptionsBuilderInterface
     */
    private $shippingOptionsBuilder;

    /**
     * @var PurchaseUnitsNodeBuilderInterface
     */
    private $purchaseUnitsNodeBuilder;

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

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CartInterface $cartAdapter,
        ShippingOptionsBuilderInterface $shippingOptionsBuilder,
        PurchaseUnitsNodeBuilderInterface $purchaseUnitsNodeBuilder,
        CountryInterface $country,
        CountryRepositoryInterface $countryRepository,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger
    ) {
        $this->cartAdapter = $cartAdapter;
        $this->shippingOptionsBuilder = $shippingOptionsBuilder;
        $this->purchaseUnitsNodeBuilder = $purchaseUnitsNodeBuilder;
        $this->country = $country;
        $this->countryRepository = $countryRepository;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process(int $cartId, ShippingCallbackPayload $payload): array
    {
        $cart = $this->cartAdapter->getCart($cartId);

        if (!$cart) {
            throw new ShippingCallbackException(
                ShippingCallbackException::METHOD_UNAVAILABLE,
                sprintf('Cart %d not found', $cartId)
            );
        }

        $deliveryOptions = $cart->getDeliveryOptionList();

        if ($payload->getCountryCode()
            && ($payload->isAddressEvent() || (empty($deliveryOptions) && !$cart->getDeliveryAddressId()))
        ) {
            $addressId = $this->createTemporaryDeliveryAddress($cart, $payload);
            if ($addressId) {
                $cart->setDeliveryAddressId($addressId);
                $cart->save();
                $deliveryOptions = $cart->getDeliveryOptionList(true);
            }
        }

        if (empty($deliveryOptions)) {
            $this->logger->warning('No delivery options available for cart', [
                'id_cart' => $cartId,
                'id_address_delivery' => $cart->getDeliveryAddressId(),
                'paypal_country_code' => $payload->getCountryCode(),
                'paypal_postal_code' => $payload->getPostalCode(),
                'paypal_admin_area_1' => $payload->getAdminArea1(),
                'paypal_admin_area_2' => $payload->getAdminArea2(),
                'is_address_event' => $payload->isAddressEvent(),
            ]);

            throw new ShippingCallbackException(
                ShippingCallbackException::ADDRESS_ERROR,
                sprintf('No shipping options available for cart %d', $cartId)
            );
        }

        $shippingOptions = $this->shippingOptionsBuilder->build($cartId, $payload->getShippingOptionId());
        $selectedPrice = $this->shippingOptionsBuilder->getSelectedShippingPrice($shippingOptions);

        if ($payload->getShippingOptionId() !== null) {
            $carrierId = (int) substr($payload->getShippingOptionId(), strlen('delivery-option-'));
            $deliveryAddressId = $cart->getDeliveryAddressId();

            if ($carrierId > 0 && $deliveryAddressId > 0) {
                $cart->setDeliveryOption($deliveryAddressId, $carrierId);
                // Cart::setDeliveryOption only sets properties in memory; explicit save is required to persist to DB.
                $cart->save();
            } else {
                $this->logger->warning('PayPal shipping callback: cannot persist carrier selection', [
                    'id_cart' => $cartId,
                    'shipping_option_id' => $payload->getShippingOptionId(),
                    'carrier_id' => $carrierId,
                    'delivery_address_id' => $deliveryAddressId,
                ]);
            }
        }

        $currencyCode = $cart->getCurrencyIsoCode();

        $itemTotal = $cart->getProductsTotalWithoutTax();
        $itemTotalWithTax = $cart->getProductsTotalWithTax();
        $taxTotal = $itemTotalWithTax - $itemTotal;

        $response = $this->purchaseUnitsNodeBuilder->build($payload->getReferenceId(), $currencyCode, $itemTotal, $taxTotal, $selectedPrice, $shippingOptions);
        $response['id'] = $payload->getPaypalOrderId();

        return $response;
    }

    private function createTemporaryDeliveryAddress(CartDataInterface $cart, ShippingCallbackPayload $payload): ?int
    {
        $shopIsoCode = PaypalCountryCodeUtility::getShopIsoCode($payload->getCountryCode());
        $idCountry = (int) $this->country->getIdByIsoCode($shopIsoCode);

        if (!$idCountry) {
            $this->logger->warning('PayPal shipping callback: country not found', [
                'id_cart' => $cart->getId(),
                'country_code' => $payload->getCountryCode(),
                'shop_iso_code' => $shopIsoCode,
            ]);

            return null;
        }

        $idShop = (int) \Context::getContext()->shop->id;

        if (!$this->country->isAvailableForDelivery($idCountry, $idShop)
            || $this->country->isNeedDniByCountryId($idCountry)
        ) {
            $this->logger->warning('PayPal shipping callback: country not available for delivery', [
                'id_cart' => $cart->getId(),
                'shop_iso_code' => $shopIsoCode,
                'id_country' => $idCountry,
            ]);

            return null;
        }

        $idState = 0;

        if ($this->country->containsStates($idCountry) && $payload->getAdminArea1()) {
            if (PaypalAddressRequirementsUtility::usesStateIsoCode($shopIsoCode)) {
                $psIsoCode = PaypalStateCodeMapUtility::getShopStateCode($shopIsoCode, $payload->getAdminArea1());
                $idState = $this->countryRepository->getStateIdByIsoCode($idCountry, $psIsoCode);
            } else {
                $idState = (int) $this->countryRepository->getStateId($idCountry, $payload->getAdminArea1());
            }
        }

        $alias = self::TEMPORARY_ADDRESS_ALIAS_PREFIX . $cart->getId();
        $existingId = $this->addressRepository->getAddressIdByAliasAndCustomer($alias, $cart->getCustomerId());

        $address = $existingId ? new Address($existingId) : new Address();
        $address->alias = $alias;
        $address->id_customer = $cart->getCustomerId();
        $address->id_country = $idCountry;
        $address->id_state = $idState;
        $address->postcode = (string) $payload->getPostalCode();
        $address->city = (string) $payload->getAdminArea2();

        try {
            if (!$this->saveAddressWithRelaxedValidation($address)) {
                throw new \RuntimeException(
                    sprintf('Address::save() returned false (id_country=%d, postcode=%s)', $address->id_country, $address->postcode)
                );
            }
        } catch (\Exception $exception) {
            $this->logger->warning('PayPal shipping callback: failed to save temporary delivery address', [
                'id_cart' => $cart->getId(),
                'id_country' => $idCountry,
                'id_state' => $idState,
                'postcode' => $address->postcode,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }

        return (int) $address->id;
    }

    /**
     * Save a temporary address by relaxing PS required-field validation for fields we intentionally omit.
     * Required fields (firstname, lastname, address1, city) are made temporarily optional via in-memory
     * patches so the address can be stored with only the data available from the PayPal callback.
     * Both Address::$definition and ObjectModel::$fieldsRequiredDatabase are restored in the finally block.
     *
     * @return bool
     *
     * @throws \ReflectionException
     */
    private function saveAddressWithRelaxedValidation(Address $address): bool
    {
        $fieldsToRelax = ['firstname', 'lastname', 'address1', 'city'];

        // $this->def is copied from the PS definition cache at construction time.
        // Patching Address::$definition on an already-constructed instance has no effect.
        // We must patch the instance's own $this->def via reflection.
        $defProp = new \ReflectionProperty(\ObjectModel::class, 'def');
        $defProp->setAccessible(true);
        $originalDef = $defProp->getValue($address);
        $patchedDef = $originalDef;
        foreach ($fieldsToRelax as $field) {
            $patchedDef['fields'][$field]['required'] = false;
        }
        $defProp->setValue($address, $patchedDef);

        // Also clear any merchant-configured required fields for Address.
        $dbRequiredProp = new \ReflectionProperty(\ObjectModel::class, 'fieldsRequiredDatabase');
        $dbRequiredProp->setAccessible(true);
        $originalDbRequired = $dbRequiredProp->getValue();
        $patchedDbRequired = is_array($originalDbRequired) ? $originalDbRequired : [];
        $patchedDbRequired['Address'] = [];
        $dbRequiredProp->setValue(null, $patchedDbRequired);

        try {
            return (bool) $address->save();
        } finally {
            $defProp->setValue($address, $originalDef);
            $dbRequiredProp->setValue(null, $originalDbRequired);
        }
    }
}
