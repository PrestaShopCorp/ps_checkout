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

namespace PrestaShop\Module\PrestashopCheckout\Discount;

use PrestaShop\Module\PrestashopCheckout\Discount\Exception\DiscountException;

class Discount
{
    /** @var string */
    private $name;

    /** @var string */
    private $value;

    /**
     * @param string $name
     * @param string $value
     *
     * @throws DiscountException
     */
    public function __construct($name, $value)
    {
        $this->name = $this->assertDiscountNameIsValid($name);
        $this->value = $this->assertDiscountValueIsValid($value);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws DiscountException
     */
    public function assertDiscountNameIsValid($name)
    {
        if (is_string($name)) {
            return $name;
        }

        throw new DiscountException('Discount name is not a string', DiscountException::INVALID_NAME);
    }

    /**
     * @param string $value
     *
     * @return string
     *
     * @throws DiscountException
     */
    public function assertDiscountValueIsValid($value)
    {
        if (is_string($value) && is_numeric($value)) {
            return $value;
        }

        throw new DiscountException('Discount value is not supported', DiscountException::INVALID_VALUE);
    }
}
