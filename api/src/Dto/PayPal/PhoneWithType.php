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
 * The phone information.
 */
class PhoneWithType
{
    /**
     * @var string|null
     */
    private $phoneType;

    /**
     * @var PhoneNumber
     */
    private $phoneNumber;

    /**
     * @param PhoneNumber $phoneNumber
     */
    public function __construct(PhoneNumber $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * Returns Phone Type.
     * The phone type.
     */
    public function getPhoneType(): ?string
    {
        return $this->phoneType;
    }

    /**
     * Sets Phone Type.
     * The phone type.
     *
     * @maps phone_type
     */
    public function setPhoneType(?string $phoneType): void
    {
        $this->phoneType = $phoneType;
    }

    /**
     * Returns Phone Number.
     * The phone number in its canonical international [E.164 numbering plan format](https://www.itu.
     * int/rec/T-REC-E.164/en).
     */
    public function getPhoneNumber(): PhoneNumber
    {
        return $this->phoneNumber;
    }

    /**
     * Sets Phone Number.
     * The phone number in its canonical international [E.164 numbering plan format](https://www.itu.
     * int/rec/T-REC-E.164/en).
     *
     * @required
     * @maps phone_number
     */
    public function setPhoneNumber(PhoneNumber $phoneNumber): void
    {
        $this->phoneNumber = $phoneNumber;
    }
}
