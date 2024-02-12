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

class CardFromRequest
{
    /**
     * The year and month, in ISO-8601 &#x60;YYYY-MM&#x60; date format. See [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @var string|null
     */
    protected $expiry;

    /**
     * The last digits of the payment card.
     *
     * @var string|null
     */
    protected $last_digits;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->expiry = isset($data['expiry']) ? $data['expiry'] : null;
        $this->last_digits = isset($data['last_digits']) ? $data['last_digits'] : null;
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
}
