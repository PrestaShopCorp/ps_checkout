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

namespace PsCheckout\Core\Util;

use PsCheckout\Infrastructure\Adapter\ConfigurationInterface;
use PsCheckout\Infrastructure\Adapter\LinkInterface;
use PsCheckout\Infrastructure\Repository\CountryRepositoryInterface;
use PsCheckout\Infrastructure\Service\PaypalStateNameResolver;
use PsCheckout\Utility\Common\StringUtility;
use PsCheckout\Utility\Payload\OrderPayloadUtility;

class ExperienceContextHelper
{
    /**
     * @var ConfigurationInterface
     */
    private $configuration;

    /**
     * @var LinkInterface
     */
    private $link;

    /**
     * @var CountryRepositoryInterface
     */
    private $countryRepository;

    /**
     * @var PaypalStateNameResolver
     */
    private $stateNameResolver;

    public function __construct(
        ConfigurationInterface $configuration,
        LinkInterface $link,
        CountryRepositoryInterface $countryRepository,
        PaypalStateNameResolver $stateNameResolver
    ) {
        $this->configuration = $configuration;
        $this->link = $link;
        $this->countryRepository = $countryRepository;
        $this->stateNameResolver = $stateNameResolver;
    }

    /**
     * Builds the common experience_context array shared by APM payment sources.
     *
     * Always sets brand_name, shipping_preference, return_url, and cancel_url.
     * Adds locale only when the cart language provides a PayPal-supported BCP-47 code.
     *
     * @param array<string, mixed> $cart
     *
     * @return array<string, mixed>
     */
    public function buildBaseContext(array $cart): array
    {
        $context = [
            'brand_name' => StringUtility::normalizeBrandName((string) $this->configuration->get('PS_SHOP_NAME')),
            'shipping_preference' => self::getShippingPreference($cart),
            'return_url' => $this->link->getModuleLink('validate'),
            'cancel_url' => $this->link->getModuleLink('cancel'),
        ];

        $locale = PayPalLocaleValidator::getValidLocale(
            isset($cart['language']->locale) ? (string) $cart['language']->locale : ''
        );
        if ($locale !== '') {
            $context['locale'] = $locale;
        }

        return $context;
    }

    /**
     * Returns the normalised shop brand name from configuration.
     */
    public function getBrandName(): string
    {
        return StringUtility::normalizeBrandName((string) $this->configuration->get('PS_SHOP_NAME'));
    }

    /**
     * Returns the minimal experience_context containing only return_url and cancel_url.
     * Use this for payment sources (Card, ApplePay, GooglePay) that do not need brand_name or shipping_preference.
     *
     * @return array<string, string>
     */
    public function buildUrlContext(): array
    {
        return [
            'return_url' => $this->link->getModuleLink('validate'),
            'cancel_url' => $this->link->getModuleLink('cancel'),
        ];
    }

    /**
     * Returns the customer's full name from the invoice address, trimmed.
     *
     * @param array<string, mixed> $cart
     */
    public function getInvoiceName(array $cart): string
    {
        $invoiceAddress = isset($cart['addresses']['invoice']) ? $cart['addresses']['invoice'] : null;
        $firstName = isset($invoiceAddress->firstname) ? (string) $invoiceAddress->firstname : '';
        $lastName = isset($invoiceAddress->lastname) ? (string) $invoiceAddress->lastname : '';

        return trim($firstName . ' ' . $lastName);
    }

    /**
     * Returns the customer's email address from cart data, or an empty string when absent.
     *
     * @param array<string, mixed> $cart
     */
    public function getCustomerEmail(array $cart): string
    {
        return isset($cart['customer']->email) ? (string) $cart['customer']->email : '';
    }

    /**
     * Resolves the ISO country code from the invoice address in cart data.
     *
     * @param array<string, mixed> $cart
     */
    public function getInvoiceCountryCode(array $cart): string
    {
        $invoiceAddress = isset($cart['addresses']['invoice']) ? $cart['addresses']['invoice'] : null;
        if (!isset($invoiceAddress->id_country)) {
            return '';
        }

        return $this->countryRepository->getCountryIsoCodeById($invoiceAddress->id_country);
    }

    /**
     * Builds a PayPal-compatible portable billing address from the invoice address in cart data.
     * Applies country-specific state ISO/name resolution and PayPal state code mapping.
     * Returns an empty array when the invoice address is absent or the country code is unknown.
     *
     * @param array<string, mixed> $cart
     *
     * @return array<string, string>
     */
    public function buildInvoicePortableAddress(array $cart): array
    {
        if (!isset($cart['addresses']['invoice'])) {
            return [];
        }

        $address = $cart['addresses']['invoice'];
        $countryIso = $this->getInvoiceCountryCode($cart);

        if ($countryIso === '') {
            return [];
        }

        $stateName = $this->stateNameResolver->resolve($countryIso, (int) $address->id_state);

        return OrderPayloadUtility::getAddressPortable($address, $countryIso, $stateName);
    }

    /**
     * Derives the PayPal shipping_preference from cart data.
     *
     * - NO_SHIPPING          when the cart contains only virtual products
     * - SET_PROVIDED_ADDRESS when a shipping address is already set on the cart
     * - GET_FROM_FILE        fallback: let PayPal collect the address from the buyer's account
     *
     * @param array<string, mixed> $cart
     */
    public static function getShippingPreference(array $cart): string
    {
        if (isset($cart['cart']['is_virtual']) && (bool) $cart['cart']['is_virtual']) {
            return 'NO_SHIPPING';
        }

        if (isset($cart['addresses']['shipping']) && $cart['addresses']['shipping']->id !== null) {
            return 'SET_PROVIDED_ADDRESS';
        }

        return 'GET_FROM_FILE';
    }

    /**
     * Returns the shipping callback URL for the given cart ID.
     */
    public function buildShippingCallbackUrl(int $cartId): string
    {
        return $this->link->getModuleLink('shipping', ['id_cart' => $cartId]);
    }
}
