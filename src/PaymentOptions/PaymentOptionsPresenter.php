<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\PaymentOptions;

/**
 * Class PaymentOptionsPresenter is used to convert payment options to array
 */
class PaymentOptionsPresenter
{
    /**
     * Get the payment order as json to save it in the config or to send it to front
     *
     * @param PaymentOption $paymentOption
     * @param bool false $toDisplay is used to send countries as id or as name
     *
     * @return array
     */
    public function convertToArray($paymentOption, $toDisplay = false)
    {
        return [
            'name' => $paymentOption->getName(),
            'position' => $paymentOption->getPosition(),
            'logo' => $paymentOption->getLogo(),
            'countries' => $toDisplay ? $paymentOption->getCountriesAsName() : $paymentOption->getCountries(),
            'enabled' => $paymentOption->isEnabled(),
        ];
    }
}
