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

class ShippingRequest
{
    /**
     * @var Name
     */
    private $name;
    /**
     * @var string
     */
    private $type;
    /**
     * @var ShippingOptionRequest[]
     */
    private $options;
    /**
     * @var AddressRequest
     */
    private $address;

    /**
     * @return Name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param Name $name
     *
     * @return void
     */
    public function setName(Name $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return void
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return ShippingOptionRequest[]
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param ShippingOptionRequest[] $options
     *
     * @return void
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }

    /**
     * @return AddressRequest
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param AddressRequest $address
     *
     * @return void
     */
    public function setAddress(AddressRequest $address)
    {
        $this->address = $address;
    }
}
