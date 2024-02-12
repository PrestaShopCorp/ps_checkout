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

class CardResponse
{
    /**
     * The card holder&#39;s name as it appears on the card.
     *
     * @var string|null
     */
    protected $name;

    /**
     * The last digits of the payment card.
     *
     * @var string|null
     */
    protected $last_digits;

    /**
     * @var string|null
     */
    protected $brand;

    /**
     * Array of brands or networks associated with the card.
     *
     * @var string[]|null
     */
    protected $available_networks;

    /**
     * The payment card type.
     *
     * @var string|null
     */
    protected $type;

    /**
     * @var AuthenticationResponse|null
     */
    protected $authentication_result;

    /**
     * @var CardAttributesResponse|null
     */
    protected $attributes;

    /**
     * @var CardFromRequest|null
     */
    protected $from_request;

    /**
     * The year and month, in ISO-8601 &#x60;YYYY-MM&#x60; date format. See [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @var string|null
     */
    protected $expiry;

    /**
     * @var BinDetails|null
     */
    protected $bin_details;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->last_digits = isset($data['last_digits']) ? $data['last_digits'] : null;
        $this->brand = isset($data['brand']) ? $data['brand'] : null;
        $this->available_networks = isset($data['available_networks']) ? $data['available_networks'] : null;
        $this->type = isset($data['type']) ? $data['type'] : null;
        $this->authentication_result = isset($data['authentication_result']) ? $data['authentication_result'] : null;
        $this->attributes = isset($data['attributes']) ? $data['attributes'] : null;
        $this->from_request = isset($data['from_request']) ? $data['from_request'] : null;
        $this->expiry = isset($data['expiry']) ? $data['expiry'] : null;
        $this->bin_details = isset($data['bin_details']) ? $data['bin_details'] : null;
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
     * @param string|null $name the card holder's name as it appears on the card
     *
     * @return $this
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets last_digits.
     *
     * @return string|null
     */
    public function getLastDigits()
    {
        return $this->last_digits;
    }

    /**
     * Sets last_digits.
     *
     * @param string|null $last_digits the last digits of the payment card
     *
     * @return $this
     */
    public function setLastDigits($last_digits = null)
    {
        $this->last_digits = $last_digits;

        return $this;
    }

    /**
     * Gets brand.
     *
     * @return string|null
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * Sets brand.
     *
     * @param string|null $brand
     *
     * @return $this
     */
    public function setBrand($brand = null)
    {
        $this->brand = $brand;

        return $this;
    }

    /**
     * Gets available_networks.
     *
     * @return string[]|null
     */
    public function getAvailableNetworks()
    {
        return $this->available_networks;
    }

    /**
     * Sets available_networks.
     *
     * @param string[]|null $available_networks array of brands or networks associated with the card
     *
     * @return $this
     */
    public function setAvailableNetworks(array $available_networks = null)
    {
        $this->available_networks = $available_networks;

        return $this;
    }

    /**
     * Gets type.
     *
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Sets type.
     *
     * @param string|null $type the payment card type
     *
     * @return $this
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Gets authentication_result.
     *
     * @return AuthenticationResponse|null
     */
    public function getAuthenticationResult()
    {
        return $this->authentication_result;
    }

    /**
     * Sets authentication_result.
     *
     * @param AuthenticationResponse|null $authentication_result
     *
     * @return $this
     */
    public function setAuthenticationResult(AuthenticationResponse $authentication_result = null)
    {
        $this->authentication_result = $authentication_result;

        return $this;
    }

    /**
     * Gets attributes.
     *
     * @return CardAttributesResponse|null
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Sets attributes.
     *
     * @param CardAttributesResponse|null $attributes
     *
     * @return $this
     */
    public function setAttributes(CardAttributesResponse $attributes = null)
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Gets from_request.
     *
     * @return CardFromRequest|null
     */
    public function getFromRequest()
    {
        return $this->from_request;
    }

    /**
     * Sets from_request.
     *
     * @param CardFromRequest|null $from_request
     *
     * @return $this
     */
    public function setFromRequest(CardFromRequest $from_request = null)
    {
        $this->from_request = $from_request;

        return $this;
    }

    /**
     * Gets expiry.
     *
     * @return string|null
     */
    public function getExpiry()
    {
        return $this->expiry;
    }

    /**
     * Sets expiry.
     *
     * @param string|null $expiry The year and month, in ISO-8601 `YYYY-MM` date format. See [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return $this
     */
    public function setExpiry($expiry = null)
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Gets bin_details.
     *
     * @return BinDetails|null
     */
    public function getBinDetails()
    {
        return $this->bin_details;
    }

    /**
     * Sets bin_details.
     *
     * @param BinDetails|null $bin_details
     *
     * @return $this
     */
    public function setBinDetails(BinDetails $bin_details = null)
    {
        $this->bin_details = $bin_details;

        return $this;
    }
}
