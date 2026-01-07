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
 * Representation of card details as received in the request.
 */
class CardFromRequest
{
    /**
     * @var string|null
     */
    private $expiry;

    /**
     * @var string|null
     */
    private $lastDigits;

    /**
     * Returns Expiry.
     * The year and month, in ISO-8601 `YYYY-MM` date format. See [Internet date and time format](https:
     * //tools.ietf.org/html/rfc3339#section-5.6).
     */
    public function getExpiry(): ?string
    {
        return $this->expiry;
    }

    /**
     * Sets Expiry.
     * The year and month, in ISO-8601 `YYYY-MM` date format. See [Internet date and time format](https:
     * //tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @maps expiry
     * @return self
     */
    public function setExpiry(?string $expiry): self
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Returns Last Digits.
     * The last digits of the payment card.
     */
    public function getLastDigits(): ?string
    {
        return $this->lastDigits;
    }

    /**
     * Sets Last Digits.
     * The last digits of the payment card.
     *
     * @maps last_digits
     * @return self
     */
    public function setLastDigits(?string $lastDigits): self
    {
        $this->lastDigits = $lastDigits;

        return $this;
    }
}
