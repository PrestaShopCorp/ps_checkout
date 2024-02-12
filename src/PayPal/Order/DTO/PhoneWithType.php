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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class PhoneWithType
{
    /**
     * @var Phone
     */
    private $phone_number;
    /**
     * @var string
     */
    private $phone_type;

    /**
     * @return Phone
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * @param Phone $phone_number
     *
     * @return void
     */
    public function setPhoneNumber(Phone $phone_number)
    {
        $this->phone_number = $phone_number;
    }

    /**
     * @return string
     */
    public function getPhoneType()
    {
        return $this->phone_type;
    }

    /**
     * @param string $phone_type
     *
     * @return void
     */
    public function setPhoneType($phone_type)
    {
        $this->phone_type = $phone_type;
    }
}
