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

class AddressDetails
{
    /**
     * The street number.
     *
     * @var string|null
     */
    protected $street_number;
    /**
     * The street name. Just &#x60;Drury&#x60; in &#x60;Drury Lane&#x60;.
     *
     * @var string|null
     */
    protected $street_name;
    /**
     * The street type. For example, avenue, boulevard, road, or expressway.
     *
     * @var string|null
     */
    protected $street_type;
    /**
     * The delivery service. Post office box, bag number, or post office name.
     *
     * @var string|null
     */
    protected $delivery_service;
    /**
     * A named locations that represents the premise. Usually a building name or number or collection of buildings with a common name or number. For example, &lt;code&gt;Craven House&lt;/code&gt;.
     *
     * @var string|null
     */
    protected $building_name;
    /**
     * The first-order entity below a named building or location that represents the sub-premises. Usually a single building within a collection of buildings with a common name. Can be a flat, story, floor, room, or apartment.
     *
     * @var string|null
     */
    protected $sub_building;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->street_number = isset($data['street_number']) ? $data['street_number'] : null;
        $this->street_name = isset($data['street_name']) ? $data['street_name'] : null;
        $this->street_type = isset($data['street_type']) ? $data['street_type'] : null;
        $this->delivery_service = isset($data['delivery_service']) ? $data['delivery_service'] : null;
        $this->building_name = isset($data['building_name']) ? $data['building_name'] : null;
        $this->sub_building = isset($data['sub_building']) ? $data['sub_building'] : null;
    }

    /**
     * Gets street_number.
     *
     * @return string|null
     */
    public function getStreetNumber()
    {
        return $this->street_number;
    }

    /**
     * Sets street_number.
     *
     * @param string|null $street_number the street number
     *
     * @return $this
     */
    public function setStreetNumber($street_number = null)
    {
        $this->street_number = $street_number;

        return $this;
    }

    /**
     * Gets street_name.
     *
     * @return string|null
     */
    public function getStreetName()
    {
        return $this->street_name;
    }

    /**
     * Sets street_name.
     *
     * @param string|null $street_name The street name. Just `Drury` in `Drury Lane`.
     *
     * @return $this
     */
    public function setStreetName($street_name = null)
    {
        $this->street_name = $street_name;

        return $this;
    }

    /**
     * Gets street_type.
     *
     * @return string|null
     */
    public function getStreetType()
    {
        return $this->street_type;
    }

    /**
     * Sets street_type.
     *
     * @param string|null $street_type The street type. For example, avenue, boulevard, road, or expressway.
     *
     * @return $this
     */
    public function setStreetType($street_type = null)
    {
        $this->street_type = $street_type;

        return $this;
    }

    /**
     * Gets delivery_service.
     *
     * @return string|null
     */
    public function getDeliveryService()
    {
        return $this->delivery_service;
    }

    /**
     * Sets delivery_service.
     *
     * @param string|null $delivery_service The delivery service. Post office box, bag number, or post office name.
     *
     * @return $this
     */
    public function setDeliveryService($delivery_service = null)
    {
        $this->delivery_service = $delivery_service;

        return $this;
    }

    /**
     * Gets building_name.
     *
     * @return string|null
     */
    public function getBuildingName()
    {
        return $this->building_name;
    }

    /**
     * Sets building_name.
     *
     * @param string|null $building_name A named locations that represents the premise. Usually a building name or number or collection of buildings with a common name or number. For example, <code>Craven House</code>.
     *
     * @return $this
     */
    public function setBuildingName($building_name = null)
    {
        $this->building_name = $building_name;

        return $this;
    }

    /**
     * Gets sub_building.
     *
     * @return string|null
     */
    public function getSubBuilding()
    {
        return $this->sub_building;
    }

    /**
     * Sets sub_building.
     *
     * @param string|null $sub_building The first-order entity below a named building or location that represents the sub-premises. Usually a single building within a collection of buildings with a common name. Can be a flat, story, floor, room, or apartment.
     *
     * @return $this
     */
    public function setSubBuilding($sub_building = null)
    {
        $this->sub_building = $sub_building;

        return $this;
    }
}
