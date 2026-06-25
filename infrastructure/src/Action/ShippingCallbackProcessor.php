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
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\AddressRepositoryInterface;
use PsCheckout\Infrastructure\Service\CountryResolutionException;
use PsCheckout\Infrastructure\Service\PaypalAddressResolverInterface;
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
     * @var ContextInterface
     */
    private $context;

    /**
     * @var PaypalAddressResolverInterface
     */
    private $addressResolver;

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
        ContextInterface $context,
        PaypalAddressResolverInterface $addressResolver,
        AddressRepositoryInterface $addressRepository,
        LoggerInterface $logger
    ) {
        $this->cartAdapter = $cartAdapter;
        $this->shippingOptionsBuilder = $shippingOptionsBuilder;
        $this->purchaseUnitsNodeBuilder = $purchaseUnitsNodeBuilder;
        $this->context = $context;
        $this->addressResolver = $addressResolver;
        $this->addressRepository = $addressRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function process(int $cartId, ShippingCallbackPayload $payload): array
    {
        $this->logger->info('PayPal shipping callback: processing', [
            'id_cart' => $cartId,
            'is_address_event' => $payload->isAddressEvent(),
            'country_code' => $payload->getCountryCode(),
            'postal_code' => $payload->getPostalCode(),
            'shipping_option_id' => $payload->getShippingOptionId(),
        ]);

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
                // Migrate product rows from every address currently in the delivery option list to
                // the temp address. Using only psCart->id_address_delivery as the "from" address
                // misses product rows that were never migrated on a prior callback (they remain
                // keyed under the original customer address), causing getDeliveryOptionList to
                // compute carriers for the wrong address.
                $existingAddressKeys = array_keys($deliveryOptions);
                foreach ($existingAddressKeys as $fromAddressId) {
                    if ((int) $fromAddressId !== $addressId) {
                        $cart->migrateProductsToDeliveryAddress((int) $fromAddressId, $addressId);
                    }
                }
                $cart->setDeliveryAddressId($addressId);
                $cart->save();
                $deliveryOptions = $cart->getDeliveryOptionList(true);

                $this->logger->info('PayPal shipping callback: delivery address updated', [
                    'id_cart' => $cartId,
                    'id_address' => $addressId,
                    'migrated_from_addresses' => $existingAddressKeys,
                    'delivery_option_keys_after' => array_keys($deliveryOptions),
                ]);
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

        $shippingOptions = $this->shippingOptionsBuilder->build($cartId, $payload->getShippingOptionId(), $deliveryOptions);

        if (empty($shippingOptions)) {
            $this->logger->warning('No ps_checkout-enabled carriers available for cart', [
                'id_cart' => $cartId,
            ]);

            throw new ShippingCallbackException(
                ShippingCallbackException::ADDRESS_ERROR,
                sprintf('No shipping options available for cart %d after filtering', $cartId)
            );
        }

        $selectedPrice = $this->shippingOptionsBuilder->getSelectedShippingPrice($shippingOptions);

        if ($payload->getShippingOptionId() !== null) {
            $shippingOptionId = $payload->getShippingOptionId();
            $prefix = 'delivery-option-';

            if (strncmp($shippingOptionId, $prefix, strlen($prefix)) !== 0) {
                throw new ShippingCallbackException(
                    ShippingCallbackException::METHOD_UNAVAILABLE,
                    sprintf('Unrecognised shipping option ID format: %s', $shippingOptionId)
                );
            }

            $carrierId = (int) substr($shippingOptionId, strlen($prefix));

            $knownIds = array_column($shippingOptions, 'id');
            if (!in_array($shippingOptionId, $knownIds, true)) {
                throw new ShippingCallbackException(
                    ShippingCallbackException::METHOD_UNAVAILABLE,
                    sprintf('Unknown shipping option ID: %s', $shippingOptionId)
                );
            }

            $deliveryAddressId = $cart->getDeliveryAddressId();

            if ($carrierId > 0 && $deliveryAddressId > 0) {
                // $deliveryOptions is keyed by ps_cart_product.id_address_delivery, not cart.id_address_delivery.
                // When updateDeliveryAddressId fails to propagate the temp address to product rows, these two
                // differ (e.g. temp=47, product rows=44). Use the address actually in the list so
                // Cart::setDeliveryOption passes its internal validation and does not silently discard the entry.
                $deliveryOptionAddresses = array_keys($deliveryOptions);
                $effectiveAddressId = (!empty($deliveryOptionAddresses) && !in_array($deliveryAddressId, $deliveryOptionAddresses, true))
                    ? (int) reset($deliveryOptionAddresses)
                    : $deliveryAddressId;

                $deliveryOptionBefore = $cart->getDeliveryOption();
                $cart->setDeliveryOption($effectiveAddressId, $carrierId);
                // Cart::setDeliveryOption only sets properties in memory; explicit save is required to persist to DB.
                $cart->save();
                $this->logger->info('PayPal shipping callback: delivery option updated', [
                    'id_cart' => $cartId,
                    'shipping_option_id' => $shippingOptionId,
                    'carrier_id' => $carrierId,
                    'delivery_address_id' => $deliveryAddressId,
                    'effective_address_id' => $effectiveAddressId,
                    'delivery_option_list_addresses' => $deliveryOptionAddresses,
                    'delivery_option_before' => $deliveryOptionBefore,
                    'delivery_option_after' => $cart->getDeliveryOption(),
                ]);
            } else {
                $this->logger->warning('PayPal shipping callback: cannot persist carrier selection', [
                    'id_cart' => $cartId,
                    'shipping_option_id' => $shippingOptionId,
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
        $idShop = (int) $this->context->getShop()->id;

        try {
            $resolved = $this->addressResolver->resolveCountryState(
                (string) $payload->getCountryCode(),
                $payload->getAdminArea1(),
                $idShop
            );
        } catch (CountryResolutionException $e) {
            if ($e->getCode() === CountryResolutionException::COUNTRY_NOT_FOUND) {
                $this->logger->warning('PayPal shipping callback: country not found', [
                    'id_cart' => $cart->getId(),
                    'country_code' => $payload->getCountryCode(),
                    'shop_iso_code' => $e->getShopIsoCode(),
                ]);
            } else {
                $this->logger->warning('PayPal shipping callback: country not available for delivery', [
                    'id_cart' => $cart->getId(),
                    'shop_iso_code' => $e->getShopIsoCode(),
                    'id_country' => $e->getIdCountry(),
                ]);
            }

            throw new ShippingCallbackException(
                ShippingCallbackException::COUNTRY_ERROR,
                $e->getMessage()
            );
        }

        $idCountry = $resolved->idCountry;
        $idState = $resolved->idState;

        $alias = self::TEMPORARY_ADDRESS_ALIAS_PREFIX . $cart->getId();
        $existingId = $this->addressRepository->getAddressIdByAliasAndCustomer($alias, $cart->getCustomerId());

        $address = $existingId ? new Address($existingId) : new Address();
        $address->alias = $alias;
        $address->id_customer = $cart->getCustomerId();
        $address->id_country = $idCountry;
        $address->id_state = $idState;
        $address->postcode = (string) $payload->getPostalCode();
        $address->city = (string) $payload->getAdminArea2();
        if (!$existingId) {
            $address->other = (string) $cart->getDeliveryAddressId();
        }

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

        $this->logger->info('PayPal shipping callback: temporary delivery address ' . ($existingId ? 'updated' : 'created'), [
            'id_cart' => $cart->getId(),
            'id_address' => (int) $address->id,
            'id_country' => $idCountry,
            'id_state' => $idState,
            'city' => $address->city,
            'postcode' => $address->postcode,
        ]);

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
