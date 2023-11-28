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

namespace PrestaShop\Module\PrestashopCheckout\PaymentSource;

use PrestaShop\Module\PrestashopCheckout\Rule\RuleInterface;

class PaymentSource
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $isEnabled;

    /**
     * @var int
     */
    private $position;

    /**
     * @var RuleInterface[]
     */
    private $eligibilityRules;

    /**
     * @param string $name
     * @param bool $isEnabled
     * @param int $position
     * @param RuleInterface[] $eligibilityRules
     */
    public function __construct($name, $isEnabled, $position, array $eligibilityRules)
    {
        $this->name = $name;
        $this->isEnabled = $isEnabled;
        $this->position = $position;
        $this->eligibilityRules = $eligibilityRules;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return RuleInterface[]
     */
    public function getEligibilityRules()
    {
        return $this->eligibilityRules;
    }
}
