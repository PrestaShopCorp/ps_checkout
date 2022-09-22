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

namespace PrestaShop\Module\PrestashopCheckout\Translations;

use Module;

class OrderValidationTranslation
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    public function getTranslations()
    {
        return [
            'validation' => [
                'general' => [
                    'intent' => $this->module->l('Passed intent is unsupported', 'ordervalidationtranslation'),
                    'currencyCode' => $this->module->l('Passed currency is invalid', 'ordervalidationtranslation'),
                    'amount' => $this->module->l('Passed amount is less or equal to zero', 'ordervalidationtranslation'),
                    'merchantId' => $this->module->l('Passed merchant id is not valid', 'ordervalidationtranslation'),
                    'defaultError' => $this->module->l('Unknown error', 'ordervalidationtranslation'),
                ],
                'payer' => [
                    'firstname' => $this->module->l('Payer given name is empty', 'ordervalidationtranslation'),
                    'surname' => $this->module->l('Payer surname is empty', 'ordervalidationtranslation'),
                    'email' => $this->module->l('Payer email address is empty', 'ordervalidationtranslation'),
                    'streetAddress' => $this->module->l('Payer address street is empty', 'ordervalidationtranslation'),
                    'cityAddress' => $this->module->l('Payer address city is empty', 'ordervalidationtranslation'),
                    'countryCodeAddress' => $this->module->l('Payer address country code is empty', 'ordervalidationtranslation'),
                    'postalCodeAddress' => $this->module->l('Payer address country code is empty', 'ordervalidationtranslation'),
                ],
                'applicationContext' => [
                    'brandName' => $this->module->l('Application context brand name is missed', 'ordervalidationtranslation'),
                    'shippingPreference' => $this->module->l('Application context shipping preference is missed', 'ordervalidationtranslation'),
                ],
                'item' => [
                    'name' => $this->module->l('Item name is empty', 'ordervalidationtranslation'),
                    'unitCurrency' => $this->module->l('Item unit amount currency code is not valid', 'ordervalidationtranslation'),
                    'unitAmount' => $this->module->l('Item unit amount value is empty', 'ordervalidationtranslation'),
                    'taxCurrency' => $this->module->l('Item tax currency code is empty', 'ordervalidationtranslation'),
                    'taxValue' => $this->module->l('Item tax value is empty', 'ordervalidationtranslation'),
                    'quantity' => $this->module->l('Item quantity is empty', 'ordervalidationtranslation'),
                    'category' => $this->module->l('Item category is empty', 'ordervalidationtranslation'),
                ],
                'shipping' => [
                    'name' => $this->module->l('Shipping name is empty', 'ordervalidationtranslation'),
                    'address' => $this->module->l('Shipping address is empty', 'ordervalidationtranslation'),
                    'city' => $this->module->l('Shipping city is empty', 'ordervalidationtranslation'),
                    'countryCode' => $this->module->l('Shipping country code is not valid', 'ordervalidationtranslation'),
                    'postalCode' => $this->module->l('Shipping postal code is empty', 'ordervalidationtranslation'),
                ],
            ],
        ];
    }
}
