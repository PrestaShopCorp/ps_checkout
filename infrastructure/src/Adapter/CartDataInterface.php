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

interface CartDataInterface
{
    public function getId(): int;

    public function getCustomerId(): int;

    public function getInvoiceAddressId(): int;

    public function getDeliveryAddressId(): int;

    public function setDeliveryAddressId(int $addressId): void;

    public function setDeliveryOption(int $deliveryAddressId, int $carrierId): void;

    public function getCurrencyIsoCode(): string;

    public function getSecureKey(): string;

    public function isVirtualCart(): bool;

    public function save(): void;

    /** @return array<int, array<string, mixed>> */
    public function getProducts(): array;

    /** @return array<mixed, mixed> */
    public function getDeliveryOptionList(bool $flush = false): array;

    /** @return array<mixed, mixed> */
    public function getDeliveryOption(): array;

    public function getProductsTotalWithoutTax(): float;

    public function getProductsTotalWithTax(): float;

    public function getOrderTotalWithTax(): float;

    public function isAllProductsInStock(): bool;

    public function checkAllProductsAreStillAvailableInThisState(): bool;

    public function checkAllProductsHaveMinimalQuantities(): bool;
}
