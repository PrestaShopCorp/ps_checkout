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

use PrestaShop\Module\PrestashopCheckout\Intent\Exception\IntentException;
use PrestaShop\Module\PrestashopCheckout\Intent\ValueObject\Intent;
use PrestaShop\Module\PrestashopCheckout\Rule\InRule;
use PrestaShop\Module\PrestashopCheckout\Rule\RuleInterface;

class IntentEligibilityRule implements RuleInterface
{
    /**
     * @var RuleInterface
     */
    private $rule;

    /**
     * @param Intent $intent
     * @param array $allowedIntent
     *
     * @throws IntentException
     */
    public function __construct(Intent $intent, array $allowedIntent)
    {
        $this->rule = new InRule($intent->getValue(), $this->assertIsValidIntentList($allowedIntent));
    }

    /**
     * {@inheritDoc}
     */
    public function evaluate()
    {
        return $this->rule->evaluate();
    }

    /**
     * @param array $allowedIntent
     *
     * @return array
     *
     * @throws IntentException
     */
    private function assertIsValidIntentList(array $allowedIntent)
    {
        foreach ($allowedIntent as $aIntent) {
            new Intent($aIntent); // check if the intent is string and valid
        }

        return $allowedIntent;
    }
}
