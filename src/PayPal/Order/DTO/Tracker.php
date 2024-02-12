<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class Tracker
{
    /**
     * The tracker id.
     *
     * @var string|null
     */
    protected $id;

    /**
     * @var mixed|null
     */
    protected $status;

    /**
     * An array of details of items in the shipment.
     *
     * @var TrackerItem[]|null
     */
    protected $items;

    /**
     * An array of request-related HATEOAS links.
     *
     * @var LinkDescription[]|null
     */
    protected $links;

    /**
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). Seconds are required while fractional seconds are optional.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; The regular expression provides guidance but does not reject all invalid dates.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $create_time;

    /**
     * The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). Seconds are required while fractional seconds are optional.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; The regular expression provides guidance but does not reject all invalid dates.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $update_time;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->id = isset($data['id']) ? $data['id'] : null;
        $this->status = isset($data['status']) ? $data['status'] : null;
        $this->items = isset($data['items']) ? $data['items'] : null;
        $this->links = isset($data['links']) ? $data['links'] : null;
        $this->create_time = isset($data['create_time']) ? $data['create_time'] : null;
        $this->update_time = isset($data['update_time']) ? $data['update_time'] : null;
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
     * @param string|null $id the tracker id
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
     * @return mixed|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Sets status.
     *
     * @param mixed|null $status
     *
     * @return $this
     */
    public function setStatus($status = null)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Gets items.
     *
     * @return TrackerItem[]|null
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Sets items.
     *
     * @param TrackerItem[]|null $items an array of details of items in the shipment
     *
     * @return $this
     */
    public function setItems(array $items = null)
    {
        $this->items = $items;

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

    /**
     * Gets create_time.
     *
     * @return string|null
     */
    public function getCreateTime()
    {
        return $this->create_time;
    }

    /**
     * Sets create_time.
     *
     * @param string|null $create_time The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). Seconds are required while fractional seconds are optional.<blockquote><strong>Note:</strong> The regular expression provides guidance but does not reject all invalid dates.</blockquote>
     *
     * @return $this
     */
    public function setCreateTime($create_time = null)
    {
        $this->create_time = $create_time;

        return $this;
    }

    /**
     * Gets update_time.
     *
     * @return string|null
     */
    public function getUpdateTime()
    {
        return $this->update_time;
    }

    /**
     * Sets update_time.
     *
     * @param string|null $update_time The date and time, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). Seconds are required while fractional seconds are optional.<blockquote><strong>Note:</strong> The regular expression provides guidance but does not reject all invalid dates.</blockquote>
     *
     * @return $this
     */
    public function setUpdateTime($update_time = null)
    {
        $this->update_time = $update_time;

        return $this;
    }
}
