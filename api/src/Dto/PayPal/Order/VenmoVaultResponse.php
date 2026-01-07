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

namespace PsCheckout\Api\Dto\PayPal\Order;

use PsCheckout\Api\Dto\PayPal\CustomerInformation;
use PsCheckout\Api\Dto\PayPal\LinkDescription;

/**
 * The details about a saved venmo payment source.
 */
class VenmoVaultResponse
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $status;

    /**
     * @var LinkDescription[]|null
     */
    private $links;

    /**
     * @var CustomerInformation|null
     */
    private $customer;

    /**
     * Returns Id.
     * The PayPal-generated ID for the saved payment source.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The PayPal-generated ID for the saved payment source.
     *
     * @maps id
     */
    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    /**
     * Returns Status.
     * The vault status.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The vault status.
     *
     * @maps status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

    /**
     * Returns Links.
     * An array of request-related HATEOAS links.
     *
     * @return LinkDescription[]|null
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * Sets Links.
     * An array of request-related HATEOAS links.
     *
     * @maps links
     *
     * @param LinkDescription[]|null $links
     */
    public function setLinks(?array $links): void
    {
        $this->links = $links;
    }

    /**
     * Returns Customer.
     * This object represents a merchant’s customer, allowing them to store contact details, and track all
     * payments associated with the same customer.
     */
    public function getCustomer(): ?CustomerInformation
    {
        return $this->customer;
    }

    /**
     * Sets Customer.
     * This object represents a merchant’s customer, allowing them to store contact details, and track all
     * payments associated with the same customer.
     *
     * @maps customer
     */
    public function setCustomer(?CustomerInformation $customer): void
    {
        $this->customer = $customer;
    }
}
