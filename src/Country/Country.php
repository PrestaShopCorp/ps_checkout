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

namespace PrestaShop\Module\PrestashopCheckout\Country;

use PrestaShop\Module\PrestashopCheckout\Country\Exception\CountryException;
use PrestaShop\Module\PrestashopCheckout\Country\ValueObject\CountryCode;

class Country
{
    /** @var string */
    private $name;

    /** @var CountryCode */
    private $code;

    /**
     * @param string $name
     * @param string $code
     *
     * @throws CountryException
     */
    public function __construct($name, $code)
    {
        $this->name = $this->assertCountryNameIsValid($name);
        $this->code = new CountryCode($code);
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
        return $this->code->getValue();
    }

    /**
     * @param $code
     *
     * @return void
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @param $name
     *
     * @return string
     *
     * @throws CountryException
     */
    private function assertCountryNameIsValid($name)
    {
        if (!is_string($name)) {
            throw new CountryException(sprintf('NAME is not a string (%s)', gettype($name)), CountryException::WRONG_TYPE_NAME);
        }

        return $name;
    }
}
