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

namespace PsCheckout\Core\Customer\Request\ValueObject;

class ExpressCheckoutRequest
{
    /**
     * @var array
     */
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * @return string|null
     */
    public function getOrderId()
    {
        return $this->data['orderID'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getFundingSource()
    {
        return $this->data['fundingSource'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPayerEmail()
    {
        return $this->data['order']['payer']['email_address'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPayerFirstName()
    {
        return $this->data['order']['payer']['name']['given_name'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPayerLastName()
    {
        return $this->data['order']['payer']['name']['surname'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getPayerPhone()
    {
        return $this->data['order']['payer']['phone']['phone_number']['national_number'] ?? null;
    }

    /**
     * @return array|null
     */
    public function getShippingAddress()
    {
        return $this->data['order']['shipping']['address'] ?? [];
    }

    /**
     * @return string|null
     */
    public function getShippingStreet()
    {
        return $this->getShippingAddress()['address_line_1'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getShippingStreet2()
    {
        return $this->getShippingAddress()['address_line_2'] ?? '';
    }

    /**
     * @return string|null
     */
    public function getShippingPostalCode()
    {
        return $this->getShippingAddress()['postal_code'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getShippingState()
    {
        return $this->getShippingAddress()['admin_area_1'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getShippingCity()
    {
        return $this->getShippingAddress()['admin_area_2'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getShippingCountryCode()
    {
        return $this->getShippingAddress()['country_code'] ?? null;
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->data;
    }
}
