<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace PrestaShop\Module\PrestashopCheckout\Order;

use InvalidArgumentException;

class CheckoutAmount
{
    /**
     * Indicates if the number is negative
     * @var bool
     */
    private $isNegative = false;

    /**
     * Integer representation of this number
     * @var string
     */
    private $integerPart = '';

    /**
     * Integer representation of this number
     * @var string
     */
    private $fractionalPart = '';

    /**
     * @param string $number
     *
     * @throws InvalidArgumentException
     */
    public function __construct($number)
    {
        if (!is_string($number)) {
            throw new InvalidArgumentException(
                sprintf('Invalid type - expected string, but got (%s) "%s"', gettype($number), print_r($number, true))
            );
        }
        if (!is_numeric($number)) {
            throw new InvalidArgumentException('Invalid type - expected numeric, but got (%s) "%s');
        }

        $this->parseAmount($number);
    }


    /**
     * @return string
     */
    public function getSign()
    {
        return $this->isNegative ? '-' : '';
    }

    /**
     * @return string|null
     */
    public function getIntegerPart()
    {
        return $this->integerPart;
    }

    /**
     * @return string
     */
    public function getFractionalPart()
    {
        return $this->fractionalPart;
    }

    /**
     * @param $number
     * @return void
     */
    public function parseAmount($number)
    {
        if (strpos($number, '-') !== false) {
            $this->isNegative = true;
        }
        $number = explode('.', str_replace('-', '', $number));
        $this->integerPart = $number[0];
        if (isset($number[1])) {
            $this->fractionalPart = $number[1];
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $output = $this->getSign() . $this->getIntegerPart();

        $fPart = $this->getFractionalPart();

        if ('0' !== $fPart) {
            $output .= '.' . $fPart;
        }

        return $output;
    }

    /**
     * @return bool
     */
    public function isNegative()
    {
        return $this->isNegative;
    }
}
