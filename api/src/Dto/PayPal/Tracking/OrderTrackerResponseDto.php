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

use PsCheckout\Api\Dto\PayPal\LinkDescription;
use PsCheckout\Api\Dto\PayPal\OrderTrackerItem;
use PsCheckout\Api\Dto\PayPal\OrderTrackerStatus;

/**
 * The tracking response on creation of tracker.
 */
class OrderTrackerResponseDto
{
    /**
     * @var string|null
     */
    private $id;

    /**
     * @var value-of<OrderTrackerStatus::STATUSES>|null
     */
    private $status;

    /**
     * @var OrderTrackerItem[]|null
     */
    private $items;

    /**
     * @var LinkDescription[]|null
     */
    private $links;

    /**
     * @var string|null
     */
    private $createTime;

    /**
     * @var string|null
     */
    private $updateTime;

    /**
     * Returns Id.
     * The tracker id.
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * Sets Id.
     * The tracker id.
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
     * The status of the item shipment.
     *
     * @return value-of<OrderTrackerStatus::STATUSES>|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Sets Status.
     * The status of the item shipment.
     *
     * @param value-of<OrderTrackerStatus::STATUSES>|null $status
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
     * Returns Items.
     * An array of details of items in the shipment.
     *
     * @return OrderTrackerItem[]|null
     */
    public function getItems(): ?array
    {
        return $this->items;
    }

    /**
     * Sets Items.
     * An array of details of items in the shipment.
     *
     * @maps items
     *
     * @param OrderTrackerItem[]|null $items
     * @return self
     */
    public function setItems(?array $items): self
    {
        $this->items = $items;

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
     * Returns Create Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     */
    public function getCreateTime(): ?string
    {
        return $this->createTime;
    }

    /**
     * Sets Create Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     *
     * @maps create_time
     * @return self
     */
    public function setCreateTime(?string $createTime): self
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Returns Update Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     */
    public function getUpdateTime(): ?string
    {
        return $this->updateTime;
    }

    /**
     * Sets Update Time.
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.
     * 6). Seconds are required while fractional seconds are optional. Note: The regular expression
     * provides guidance but does not reject all invalid dates.
     *
     * @maps update_time
     * @return self
     */
    public function setUpdateTime(?string $updateTime): self
    {
        $this->updateTime = $updateTime;

        return $this;
    }
}
