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

class VaultResponse
{
    /**
     * The PayPal-generated ID for the saved payment source.
     *
     * @var string|null
     */
    protected $id;

    /**
     * The vault status.
     *
     * @var string|null
     */
    protected $status;

    /**
     * @var Customer|null
     */
    protected $customer;

    /**
     * An array of request-related HATEOAS links.
     *
     * @var LinkDescription[]|null
     */
    protected $links;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->status = isset($data['status']) ? $data['status'] : null;
        $this->customer = isset($data['customer']) ? $data['customer'] : null;
        $this->links = isset($data['links']) ? $data['links'] : null;
    }

    /**
     * Gets id.
     *
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets id.
     *
     * @param string|null $id the PayPal-generated ID for the saved payment source
     *
     * @return $this
     */
    public function setId($id = null)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Gets status.
     *
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status.
     *
     * @param string|null $status the vault status
     *
     * @return $this
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets customer.
     *
     * @return Customer|null
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Sets customer.
     *
     * @param Customer|null $customer
     *
     * @return $this
     */
    public function setCustomer(Customer $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Gets links.
     *
     * @return LinkDescription[]|null
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Sets links.
     *
     * @param LinkDescription[]|null $links an array of request-related HATEOAS links
     *
     * @return $this
     */
    public function setLinks(array $links = null)
    {
        $this->links = $links;

        return $this;
    }
}
