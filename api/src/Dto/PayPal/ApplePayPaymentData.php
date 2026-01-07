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
 * Information about the decrypted apple pay payment data for the token like cryptogram, eci indicator.
 */
class ApplePayPaymentData
{
    /**
     * @var string|null
     */
    private $cryptogram;

    /**
     * @var string|null
     */
    private $eciIndicator;

    /**
     * @var string|null
     */
    private $emvData;

    /**
     * @var string|null
     */
    private $pin;

    /**
     * Returns Cryptogram.
     * Online payment cryptogram, as defined by 3D Secure. The pattern is defined by an external party and
     * supports Unicode.
     */
    public function getCryptogram(): ?string
    {
        return $this->cryptogram;
    }

    /**
     * Sets Cryptogram.
     * Online payment cryptogram, as defined by 3D Secure. The pattern is defined by an external party and
     * supports Unicode.
     *
     * @maps cryptogram
     * @return self
     */
    public function setCryptogram(?string $cryptogram): self
    {
        $this->cryptogram = $cryptogram;

        return $this;
    }

    /**
     * Returns Eci Indicator.
     * ECI indicator, as defined by 3- Secure. The pattern is defined by an external party and supports
     * Unicode.
     */
    public function getEciIndicator(): ?string
    {
        return $this->eciIndicator;
    }

    /**
     * Sets Eci Indicator.
     * ECI indicator, as defined by 3- Secure. The pattern is defined by an external party and supports
     * Unicode.
     *
     * @maps eci_indicator
     * @return self
     */
    public function setEciIndicator(?string $eciIndicator): self
    {
        $this->eciIndicator = $eciIndicator;

        return $this;
    }

    /**
     * Returns Emv Data.
     * Encoded Apple Pay EMV Payment Structure used for payments in China. The pattern is defined by an
     * external party and supports Unicode.
     */
    public function getEmvData(): ?string
    {
        return $this->emvData;
    }

    /**
     * Sets Emv Data.
     * Encoded Apple Pay EMV Payment Structure used for payments in China. The pattern is defined by an
     * external party and supports Unicode.
     *
     * @maps emv_data
     * @return self
     */
    public function setEmvData(?string $emvData): self
    {
        $this->emvData = $emvData;

        return $this;
    }

    /**
     * Returns Pin.
     * Bank Key encrypted Apple Pay PIN. The pattern is defined by an external party and supports Unicode.
     */
    public function getPin(): ?string
    {
        return $this->pin;
    }

    /**
     * Sets Pin.
     * Bank Key encrypted Apple Pay PIN. The pattern is defined by an external party and supports Unicode.
     *
     * @maps pin
     * @return self
     */
    public function setPin(?string $pin): self
    {
        $this->pin = $pin;

        return $this;
    }
}
