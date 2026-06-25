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
use Exception;
use Psr\Log\LoggerInterface;
use PsCheckout\Core\Customer\Request\ValueObject\ExpressCheckoutShippingData;
use PsCheckout\Core\Exception\PsCheckoutException;
use PsCheckout\Infrastructure\Adapter\ContextInterface;
use PsCheckout\Infrastructure\Repository\PsCheckoutAddressRepositoryInterface;
use PsCheckout\Infrastructure\Service\CountryResolutionException;
use PsCheckout\Infrastructure\Service\PaypalAddressResolverInterface;

class CreateOrUpdateAddressAction implements CreateOrUpdateAddressActionInterface
{
    /**
     * @var ContextInterface
     */
    private $context;

    /**
     * @var PaypalAddressResolverInterface
     */
    private $addressResolver;

    /**
     * @var PsCheckoutAddressRepositoryInterface
     */
    private $psCheckoutAddressRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ContextInterface $context,
        PaypalAddressResolverInterface $addressResolver,
        PsCheckoutAddressRepositoryInterface $psCheckoutAddressRepository,
        LoggerInterface $logger
    ) {
        $this->context = $context;
        $this->addressResolver = $addressResolver;
        $this->psCheckoutAddressRepository = $psCheckoutAddressRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(ExpressCheckoutShippingData $shippingData)
    {
        if (!$shippingData->getCountryCode()) {
            $this->logger->warning('CreateOrUpdateAddressAction: missing country code in shipping data', ['orderId' => $shippingData->getOrderId()]);

            return false;
        }

        $shopId = (int) $this->context->getShop()->id;

        try {
            $resolved = $this->addressResolver->resolveCountryState(
                $shippingData->getCountryCode(),
                $shippingData->getState() ?: null,
                $shopId
            );
        } catch (CountryResolutionException $e) {
            if ($e->getCode() === CountryResolutionException::COUNTRY_NOT_FOUND) {
                $this->logger->warning('CreateOrUpdateAddressAction: country not found', ['isoCode' => $e->getShopIsoCode(), 'orderId' => $shippingData->getOrderId()]);
            } else {
                $this->logger->warning('CreateOrUpdateAddressAction: country not available for delivery', ['isoCode' => $e->getShopIsoCode(), 'idCountry' => $e->getIdCountry(), 'orderId' => $shippingData->getOrderId()]);
            }

            return false;
        }

        $idCountry = $resolved->idCountry;
        $idState = $resolved->idState;

        $idCustomer = (int) $this->context->getCustomer()->id;

        if ($idCustomer === 0) {
            $this->logger->warning('CreateOrUpdateAddressAction: no customer in context', ['orderId' => $shippingData->getOrderId()]);

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
            $addressId = $existingAddressId;
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
                $this->logger->warning('CreateOrUpdateAddressAction: address field validation failed', ['orderId' => $shippingData->getOrderId(), 'idCustomer' => $idCustomer]);

                return false;
            }

            try {
                $address->save();
            } catch (Exception $exception) {
                throw new PsCheckoutException($exception->getMessage(), PsCheckoutException::PSCHECKOUT_EXPRESS_CHECKOUT_CANNOT_SAVE_ADDRESS, $exception);
            }

            if (!$this->psCheckoutAddressRepository->saveAddress($address->id, $idCustomer, $checksum)) {
                $this->logger->error('CreateOrUpdateAddressAction: failed to track address in ps_checkout_address', ['idAddress' => $address->id, 'idCustomer' => $idCustomer, 'orderId' => $shippingData->getOrderId()]);
            }

            $addressId = $address->id;
        }

        $cart = $this->context->getCart();
        $oldAddressId = (int) $cart->id_address_delivery;
        $deliveryOptionBefore = (string) $cart->delivery_option;

        $this->logger->info('CreateOrUpdateAddressAction: updating cart address', [
            'orderId' => $shippingData->getOrderId(),
            'cartId' => (int) $cart->id,
            'oldAddressId' => $oldAddressId,
            'newAddressId' => (int) $addressId,
            'deliveryOptionBefore' => $deliveryOptionBefore,
        ]);

        $cart->id_address_delivery = $addressId;
        $cart->id_address_invoice = $addressId;

        $products = $cart->getProducts();
        foreach ($products as $product) {
            $cart->setProductAddressDelivery($product['id_product'], $product['id_product_attribute'], $product['id_address_delivery'], $addressId);
        }

        $result = $cart->save();

        // When the address changes, migrate the stored delivery option key so the carrier selected
        // via the shipping callback remains valid after the temp address is replaced by the real one.
        if ($result && $oldAddressId > 0 && $oldAddressId !== (int) $addressId) {
            $rawOption = json_decode($deliveryOptionBefore, true);
            $carrierKey = null;
            if (is_array($rawOption)) {
                if (isset($rawOption[$oldAddressId])) {
                    // Normal case: delivery option is keyed by the temp address id.
                    $carrierKey = $rawOption[$oldAddressId];
                } else {
                    // Fallback: product rows may have kept the original customer address
                    // (e.g. key=44) rather than the temp address (e.g. 47) because
                    // Cart::updateDeliveryAddressId only updates rows matching the old address.
                    // Pick any entry not already keyed by the new real address.
                    foreach ($rawOption as $optionAddressId => $key) {
                        if ((int) $optionAddressId !== (int) $addressId) {
                            $carrierKey = $key;

                            break;
                        }
                    }
                }
            }

            if ($carrierKey !== null) {
                $cart->setDeliveryOption([(int) $addressId => $carrierKey]);
                $cart->save();
                $this->logger->info('CreateOrUpdateAddressAction: migrated delivery option to new address', [
                    'orderId' => $shippingData->getOrderId(),
                    'cartId' => (int) $cart->id,
                    'oldAddressId' => $oldAddressId,
                    'newAddressId' => (int) $addressId,
                    'deliveryOptionBefore' => $deliveryOptionBefore,
                    'deliveryOptionAfter' => (string) $cart->delivery_option,
                ]);
            } else {
                $this->logger->info('CreateOrUpdateAddressAction: address changed but no delivery option to migrate', [
                    'orderId' => $shippingData->getOrderId(),
                    'cartId' => (int) $cart->id,
                    'oldAddressId' => $oldAddressId,
                    'newAddressId' => (int) $addressId,
                    'deliveryOptionBefore' => $deliveryOptionBefore,
                ]);
            }
        }

        return $result;
    }
}
