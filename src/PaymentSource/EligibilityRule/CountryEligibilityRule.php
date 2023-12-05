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

namespace PrestaShop\Module\PrestashopCheckout\PaymentSource\EligibilityRule;

use PrestaShop\Module\PrestashopCheckout\Country\Exception\CountryException;
use PrestaShop\Module\PrestashopCheckout\Country\ValueObject\CountryCode;
use PrestaShop\Module\PrestashopCheckout\Rule\InRule;
use PrestaShop\Module\PrestashopCheckout\Rule\RuleInterface;

class CountryEligibilityRule implements RuleInterface
{
    /** @var RuleInterface */
    private $rule;

    /**
     * @param $countryCode
     * @param $allowedCountry
     *
     * @throws CountryException
     */
    public function __construct($countryCode, $allowedCountry)
    {
        $cCode = new CountryCode($countryCode);
        $this->rule = new InRule($cCode->getValue(), $this->assertIsValidCountryList($allowedCountry));
    }

    /**
     * @param array $allowedCountry
     *
     * @return array
     *
     * @throws CountryException
     */
    private function assertIsValidCountryList($allowedCountry)
    {
        foreach ($allowedCountry as $aCountry) {
            new CountryCode($aCountry); // check if the country is string and valid
        }

        return $allowedCountry;
    }

    public function evaluate()
    {
        return $this->rule->evaluate();
    }
}
