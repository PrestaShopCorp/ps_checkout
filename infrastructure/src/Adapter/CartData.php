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

namespace PsCheckout\Infrastructure\Adapter;

use Cart as PrestaShopCart;
use Currency;

class CartData implements CartDataInterface
{
    /** @var PrestaShopCart */
    private $psCart;

    public function __construct(PrestaShopCart $psCart)
    {
        $this->psCart = $psCart;
    }

    public function getId(): int
    {
        return (int) $this->psCart->id;
    }

    public function getCustomerId(): int
    {
        return (int) $this->psCart->id_customer;
    }

    public function getInvoiceAddressId(): int
    {
        return (int) $this->psCart->id_address_invoice;
    }

    public function getDeliveryAddressId(): int
    {
        return (int) $this->psCart->id_address_delivery;
    }

    public function setDeliveryAddressId(int $addressId): void
    {
        $currentAddressId = (int) $this->psCart->id_address_delivery;
        // updateDeliveryAddressId propagates the new address to ps_cart_product rows so getDeliveryOptionList returns options keyed by the new address.
        $this->psCart->updateDeliveryAddressId($currentAddressId, $addressId);
    }

    public function migrateProductsToDeliveryAddress(int $fromAddressId, int $toAddressId): void
    {
        $this->psCart->updateDeliveryAddressId($fromAddressId, $toAddressId);
    }

    public function setDeliveryOption(int $deliveryAddressId, int $carrierId): void
    {
        $this->psCart->setDeliveryOption([$deliveryAddressId => $carrierId . ',']);
    }

    public function getCurrencyIsoCode(): string
    {
        return (string) (new Currency($this->psCart->id_currency))->iso_code;
    }

    public function getSecureKey(): string
    {
        return (string) $this->psCart->secure_key;
    }

    public function isVirtualCart(): bool
    {
        return (bool) $this->psCart->isVirtualCart();
    }

    public function save(): void
    {
        $this->psCart->update();
    }

    public function getProducts(): array
    {
        return $this->psCart->getProducts(true);
    }

    public function getDeliveryOptionList(bool $flush = false): array
    {
        return $this->psCart->getDeliveryOptionList(null, $flush);
    }

    public function getDeliveryOption(): array
    {
        return (array) $this->psCart->getDeliveryOption(null, false, false);
    }

    public function getProductsTotalWithoutTax(): float
    {
        return (float) $this->psCart->getOrderTotal(false, PrestaShopCart::ONLY_PRODUCTS);
    }

    public function getProductsTotalWithTax(): float
    {
        return (float) $this->psCart->getOrderTotal(true, PrestaShopCart::ONLY_PRODUCTS);
    }

    public function getOrderTotalWithTax(): float
    {
        return (float) $this->psCart->getOrderTotal(true, PrestaShopCart::BOTH);
    }

    public function isAllProductsInStock(): bool
    {
        return (bool) $this->psCart->isAllProductsInStock();
    }

    public function checkAllProductsAreStillAvailableInThisState(): bool
    {
        if (method_exists($this->psCart, 'checkAllProductsAreStillAvailableInThisState')) {
            return (bool) $this->psCart->checkAllProductsAreStillAvailableInThisState();
        }

        return true;
    }

    public function checkAllProductsHaveMinimalQuantities(): bool
    {
        if (method_exists($this->psCart, 'checkAllProductsHaveMinimalQuantities')) {
            return (bool) $this->psCart->checkAllProductsHaveMinimalQuantities();
        }

        return true;
    }
}
