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

class PayPalCountry
{
    /**
     * @var string
     */
    private $code;

    /**
     * @var bool
     */
    private $isCityRequired;

    /**
     * @var bool
     */
    private $isStateRequired;

    /**
     * @var bool
     */
    private $isZipCodeRequired;

    /**
     * @param string $code
     * @param bool $isCityRequired
     * @param bool $isStateRequired
     * @param bool $isZipCodeRequired
     */
    public function __construct($code, $isCityRequired, $isStateRequired, $isZipCodeRequired)
    {
        $this->code = $code;
        $this->isCityRequired = $isCityRequired;
        $this->isStateRequired = $isStateRequired;
        $this->isZipCodeRequired = $isZipCodeRequired;
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
    public function isCityRequired()
    {
        return $this->isCityRequired;
    }

    /**
     * @return bool
     */
    public function isStateRequired()
    {
        return $this->isStateRequired;
    }

    /**
     * @return bool
     */
    public function isZipCodeRequired()
    {
        return $this->isZipCodeRequired;
    }

    /**
     * @param array{iso_code: string, isCityRequired: bool, isStateRequired: bool, isZipCodeRequired: bool} $data
     *
     * @return PayPalCountry
     */
    public static function fromArray(array $data)
    {
        return new self($data['iso_code'], $data['isCityRequired'], $data['isStateRequired'], $data['isZipCodeRequired']);
    }
}
