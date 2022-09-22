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

namespace PrestaShop\Module\PrestashopCheckout\PayPal;

class PayPalCurrency
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var bool
     */
    private $isDecimalSupported;

    /**
     * @param string $code
     * @param bool $isDecimalSupported
     */
    public function __construct($code, $isDecimalSupported)
    {
        $this->code = $code;
        $this->isDecimalSupported = $isDecimalSupported;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function isDecimalSupported()
    {
        return $this->isDecimalSupported;
    }

    /**
     * @param array{iso_code: string, isDecimalSupported: bool} $data
     *
     * @return PayPalCurrency
     */
    public static function fromArray(array $data)
    {
        return new self($data['iso_code'], $data['isDecimalSupported']);
    }
}
