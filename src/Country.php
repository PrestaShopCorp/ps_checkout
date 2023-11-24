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

use PrestaShop\Module\PrestashopCheckout\Exception\CountryException;

class Country
{
    /** @var string */
    private $name;

    /** @var string */
    private $code;

    /**
     * @param string $name
     * @param string $code
     * @throws CountryException
     */
    public function __construct($name, $code)
    {
        $this->name = $this->assertCountryNameIsValid($name);
        $this->code = $this->assertCountryCodeIsValid($code);
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
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    private function assertCountryCodeIsValid($code)
    {
        if(!is_string($code)){
            throw new CountryException(sprintf('CODE is not a string (%s)',getType($code)),CountryException::WRONG_TYPE_CODE);
        }
        if(preg_match('/^[A-Z]{2}$/',$code) === 0){
            throw new CountryException('Invalid code',CountryException::INVALID_CODE);
        }
        return $code;
    }
    private function assertCountryNameIsValid($name)
    {
        if(!is_string($name)){
            throw new CountryException(sprintf('NAME is not a string (%s)',getType($name)),CountryException::WRONG_TYPE_NAME);
        }
        return $name;
    }
}
