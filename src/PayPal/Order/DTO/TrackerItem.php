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

class TrackerItem
{
    /**
     * The item name or title.
     *
     * @var string|null
     */
    protected $name;
    /**
     * The item quantity. Must be a whole number.
     *
     * @var string|null
     */
    protected $quantity;
    /**
     * The stock keeping unit (SKU) for the item. This can contain unicode characters.
     *
     * @var string|null
     */
    protected $sku;
    /**
     * The URL of the item&#39;s image. File type and size restrictions apply. An image that violates these restrictions will not be honored.
     *
     * @var string|null
     */
    protected $image_url;
    /**
     * @var mixed|null
     */
    protected $upc;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->quantity = isset($data['quantity']) ? $data['quantity'] : null;
        $this->sku = isset($data['sku']) ? $data['sku'] : null;
        $this->image_url = isset($data['image_url']) ? $data['image_url'] : null;
        $this->upc = isset($data['upc']) ? $data['upc'] : null;
    }

    /**
     * Gets name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name.
     *
     * @param string|null $name the item name or title
     *
     * @return $this
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets quantity.
     *
     * @return string|null
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Sets quantity.
     *
     * @param string|null $quantity The item quantity. Must be a whole number.
     *
     * @return $this
     */
    public function setQuantity($quantity = null)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Gets sku.
     *
     * @return string|null
     */
    public function getSku()
    {
        return $this->sku;
    }

    /**
     * Sets sku.
     *
     * @param string|null $sku The stock keeping unit (SKU) for the item. This can contain unicode characters.
     *
     * @return $this
     */
    public function setSku($sku = null)
    {
        $this->sku = $sku;

        return $this;
    }

    /**
     * Gets image_url.
     *
     * @return string|null
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * Sets image_url.
     *
     * @param string|null $image_url The URL of the item's image. File type and size restrictions apply. An image that violates these restrictions will not be honored.
     *
     * @return $this
     */
    public function setImageUrl($image_url = null)
    {
        $this->image_url = $image_url;

        return $this;
    }

    /**
     * Gets upc.
     *
     * @return mixed|null
     */
    public function getUpc()
    {
        return $this->upc;
    }

    /**
     * Sets upc.
     *
     * @param mixed|null $upc
     *
     * @return $this
     */
    public function setUpc($upc = null)
    {
        $this->upc = $upc;

        return $this;
    }
}
