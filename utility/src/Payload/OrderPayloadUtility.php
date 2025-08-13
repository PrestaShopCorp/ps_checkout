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

namespace PsCheckout\Utility\Payload;

class OrderPayloadUtility
{
    /**
     * @param \Address $address
     * @param string $countryIso
     * @param string $stateName
     *
     * @return array
     */
    public static function getAddressPortable(\Address $address, string $countryIso, string $stateName): array
    {
        return array_filter([
            'address_line_1' => $address->address1,
            'address_line_2' => $address->address2,
            'admin_area_1' => $stateName,
            'admin_area_2' => $address->city,
            'country_code' => PaypalCountryCodeUtility::getPaypalIsoCode(strtoupper($countryIso)),
            'postal_code' => $address->postcode,
        ]);
    }
}
