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

namespace PsCheckout\Api\Dto\PayPal;

/**
 * The details about a saved PayPal Wallet payment source.
 */
class PaypalWalletVaultResponse
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
     * @var PaypalWalletCustomer|null
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
     * @return self
     */
    public function setId(?string $id): self
    {
        $this->id = $id;

        return $this;
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
     * @return self
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
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
     * @return self
     */
    public function setLinks(?array $links): self
    {
        $this->links = $links;

        return $this;
    }

    /**
     * Returns Customer.
     * The details about a customer in PayPal's system of record.
     */
    public function getCustomer(): ?PaypalWalletCustomer
    {
        return $this->customer;
    }

    /**
     * Sets Customer.
     * The details about a customer in PayPal's system of record.
     *
     * @maps customer
     * @return self
     */
    public function setCustomer(?PaypalWalletCustomer $customer): self
    {
        $this->customer = $customer;

        return $this;
    }
}
