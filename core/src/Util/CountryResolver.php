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
use PsCheckout\Infrastructure\Adapter\ContextInterface;

class CountryResolver implements CountryResolverInterface
{
    /** @var ContextInterface */
    private $context;

    /** @var ConfigurationInterface */
    private $configuration;

    public function __construct(ContextInterface $context, ConfigurationInterface $configuration)
    {
        $this->context = $context;
        $this->configuration = $configuration;
    }

    public function getBuyerCountryIsoCode(): string
    {
        $code = '';

        $country = $this->context->getCountry();
        if ($country && \Validate::isLoadedObject($country)) {
            $code = \strtoupper($country->iso_code);
        }

        $cart = $this->context->getCart();
        if ($cart && $country && \Validate::isLoadedObject($cart)) {
            $taxAddressType = $this->configuration->get('PS_TAX_ADDRESS_TYPE');
            $taxAddressId = \property_exists($cart, $taxAddressType) ? $cart->{$taxAddressType} : $cart->id_address_delivery;
            $address = new \Address($taxAddressId);
            $country = new \Country($address->id_country);

            if ($country->id && $country->iso_code) {
                $code = \strtoupper($country->iso_code);
            }
        }

        if ($code === 'UK') {
            $code = 'GB';
        }

        return $code;
    }
}
