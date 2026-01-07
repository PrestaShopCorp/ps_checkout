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
 * The phone number in its canonical international [E.164 numbering plan format](https://www.itu.
 * int/rec/T-REC-E.164/en)., The phone number, in its canonical international [E.164 numbering plan
 * format](https://www.itu.int/rec/T-REC-E.164/en)., The phone number, in its canonical international
 * [E.164 numbering plan format](https://www.itu.int/rec/T-REC-E.164/en).
 */
class PhoneNumberWithCountryCode
{
    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var string
     */
    private $nationalNumber;

    /**
     * @param string $countryCode
     * @param string $nationalNumber
     */
    public function __construct(string $countryCode, string $nationalNumber)
    {
        $this->countryCode = $countryCode;
        $this->nationalNumber = $nationalNumber;
    }

    /**
     * Returns Country Code.
     * The country calling code (CC), in its canonical international [E.164 numbering plan format](https:
     * //www.itu.int/rec/T-REC-E.164/en). The combined length of the CC and the national number must not be
     * greater than 15 digits. The national number consists of a national destination code (NDC) and
     * subscriber number (SN).
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Sets Country Code.
     * The country calling code (CC), in its canonical international [E.164 numbering plan format](https:
     * //www.itu.int/rec/T-REC-E.164/en). The combined length of the CC and the national number must not be
     * greater than 15 digits. The national number consists of a national destination code (NDC) and
     * subscriber number (SN).
     *
     * @required
     * @maps country_code
     * @return self
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Returns National Number.
     * The national number, in its canonical international [E.164 numbering plan format](https://www.itu.
     * int/rec/T-REC-E.164/en). The combined length of the country calling code (CC) and the national
     * number must not be greater than 15 digits. The national number consists of a national destination
     * code (NDC) and subscriber number (SN).
     */
    public function getNationalNumber(): string
    {
        return $this->nationalNumber;
    }

    /**
     * Sets National Number.
     * The national number, in its canonical international [E.164 numbering plan format](https://www.itu.
     * int/rec/T-REC-E.164/en). The combined length of the country calling code (CC) and the national
     * number must not be greater than 15 digits. The national number consists of a national destination
     * code (NDC) and subscriber number (SN).
     *
     * @required
     * @maps national_number
     * @return self
     */
    public function setNationalNumber(string $nationalNumber): self
    {
        $this->nationalNumber = $nationalNumber;

        return $this;
    }
}
