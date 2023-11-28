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

use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\Module\PrestashopCheckout\Rule\RuleInterface;

class AmountEligibilityRule implements RuleInterface
{
    /**
     * @var DecimalNumber
     */
    private $amount;

    /**
     * @var DecimalNumber
     */
    private $minAmount;

    /**
     * @var DecimalNumber|null
     */
    private $maxAmount;

    /**
     * @param string $amount
     * @param string $minAmount
     * @param string|null $maxAmount
     */
    public function __construct($amount, $minAmount, $maxAmount = null)
    {
        $this->amount = new DecimalNumber($amount);
        $this->minAmount = new DecimalNumber($minAmount);
        $this->maxAmount = $maxAmount ? new DecimalNumber($maxAmount) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function evaluate()
    {
        return $this->amount->isGreaterOrEqualThan($this->minAmount)
            && ($this->maxAmount === null || $this->amount->isLowerOrEqualThan($this->maxAmount));
    }
}
