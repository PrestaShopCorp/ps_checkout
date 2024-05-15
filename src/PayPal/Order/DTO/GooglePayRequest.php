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

class GooglePayRequest
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $email_address;
    /**
     * @var Phone
     */
    private $phone_number;
    /**
     * @var CardAttributesRequest
     */
    private $attributes;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * @param string $email_address
     *
     * @return void
     */
    public function setEmailAddress($email_address)
    {
        $this->email_address = $email_address;
    }

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
     * @return CardAttributesRequest
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param CardAttributesRequest $attributes
     *
     * @return void
     */
    public function setAttributes(CardAttributesRequest $attributes)
    {
        $this->attributes = $attributes;
    }
}
