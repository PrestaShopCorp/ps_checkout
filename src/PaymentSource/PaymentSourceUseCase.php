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

use PrestaShop\Module\PrestashopCheckout\PaymentSourceUseCase\Exception\PaymentSourceUseCaseException;
use PrestaShop\Module\PrestashopCheckout\Rule\RuleInterface;

class PaymentSourceUseCase
{
    /** @var string */
    private $type;

    /** @var RuleInterface[] */
    private $rules;

    /**
     * @param string $type
     * @param RuleInterface[] $rules
     *
     * @throws PaymentSourceUseCaseException
     */
    public function __construct($type, $rules)
    {
        $this->type = $this->assertTypeIsValid($type);
        $this->rules = $this->assertRulesIsValid($rules);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return RuleInterface[]
     */
    public function getRules()
    {
        return $this->rules;
    }

    /**
     * @param string $type
     *
     * @return string
     *
     * @throws PaymentSourceUseCaseException
     */
    private function assertTypeIsValid($type)
    {
        if (!is_string($type)) {
            throw new PaymentSourceUseCaseException(sprintf('TYPE is not a string (%s)', gettype($type)), PaymentSourceUseCaseException::WRONG_TYPE_TYPE);
        }

        return $type;
    }

    /**
     * @param $rules
     *
     * @return array
     *
     * @throws PaymentSourceUseCaseException
     */
    private function assertRulesIsValid($rules)
    {
        if (!is_array($rules)) {
            throw new PaymentSourceUseCaseException(sprintf('RULES is not an array (%s)', gettype($rules)), PaymentSourceUseCaseException::WRONG_TYPE_RULES);
        }
        foreach ($rules as $rule) {
            if (!($rule instanceof RuleInterface)) {
                throw new PaymentSourceUseCaseException(sprintf('Invalid rule (%s)', gettype($rule)), PaymentSourceUseCaseException::INVALID_RULE);
            }
        }

        return $rules;
    }
}
