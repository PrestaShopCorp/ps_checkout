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

namespace PrestaShop\Module\PrestashopCheckout;

use PrestaShop\Module\PrestashopCheckout\Exception\CurrencyException;

class Currency
{
    /** @var string */
    private $name;

    /** @var string */
    private $code;

    /**
     * @param string $name
     * @param string $code
     *
     * @throws CurrencyException
     */
    public function __construct($name, $code)
    {
        $this->name = $this->assertCurrencyNameIsValid($name);
        $this->code = $this->assertCurrencyCodeIsValid($code);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param string $code
     *
     * @return string
     *
     * @throws CurrencyException
     */
    private function assertCurrencyCodeIsValid($code)
    {
        if (!is_string($code)) {
            throw new CurrencyException(sprintf('CODE is not a string (%s)', gettype($code)), CurrencyException::WRONG_TYPE_CODE);
        }
        if (preg_match('/^[A-Z]{3}$/', $code) === 0) {
            throw new CurrencyException('Invalid code', CurrencyException::INVALID_CODE);
        }

        return $code;
    }

    /**
     * @param string $name
     *
     * @return string
     *
     * @throws CurrencyException
     */
    private function assertCurrencyNameIsValid($name)
    {
        if (!is_string($name)) {
            throw new CurrencyException(sprintf('NAME is not a string (%s)', gettype($name)), CurrencyException::WRONG_TYPE_NAME);
        }

        return $name;
    }
}
