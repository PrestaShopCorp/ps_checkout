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
 * The Third Party Network token used to fund a payment.
 */
class NetworkToken
{
    /**
     * @var string
     */
    private $number;

    /**
     * @var string
     */
    private $expiry;

    /**
     * @var string|null
     */
    private $cryptogram;

    /**
     * @var string|null
     */
    private $eciFlag;

    /**
     * @var string|null
     */
    private $tokenRequestorId;

    /**
     * @param string $number
     * @param string $expiry
     */
    public function __construct(string $number, string $expiry)
    {
        $this->number = $number;
        $this->expiry = $expiry;
    }

    /**
     * Returns Number.
     * Third party network token number.
     */
    public function getNumber(): string
    {
        return $this->number;
    }

    /**
     * Sets Number.
     * Third party network token number.
     *
     * @required
     * @maps number
     * @return self
     */
    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Returns Expiry.
     * The year and month, in ISO-8601 `YYYY-MM` date format. See [Internet date and time format](https:
     * //tools.ietf.org/html/rfc3339#section-5.6).
     */
    public function getExpiry(): string
    {
        return $this->expiry;
    }

    /**
     * Sets Expiry.
     * The year and month, in ISO-8601 `YYYY-MM` date format. See [Internet date and time format](https:
     * //tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @required
     * @maps expiry
     * @return self
     */
    public function setExpiry(string $expiry): self
    {
        $this->expiry = $expiry;

        return $this;
    }

    /**
     * Returns Cryptogram.
     * An Encrypted one-time use value that's sent along with Network Token. This field is not required to
     * be present for recurring transactions.
     */
    public function getCryptogram(): ?string
    {
        return $this->cryptogram;
    }

    /**
     * Sets Cryptogram.
     * An Encrypted one-time use value that's sent along with Network Token. This field is not required to
     * be present for recurring transactions.
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
     * Returns Eci Flag.
     * Electronic Commerce Indicator (ECI). The ECI value is part of the 2 data elements that indicate the
     * transaction was processed electronically. This should be passed on the authorization transaction to
     * the Gateway/Processor.
     */
    public function getEciFlag(): ?string
    {
        return $this->eciFlag;
    }

    /**
     * Sets Eci Flag.
     * Electronic Commerce Indicator (ECI). The ECI value is part of the 2 data elements that indicate the
     * transaction was processed electronically. This should be passed on the authorization transaction to
     * the Gateway/Processor.
     *
     * @maps eci_flag
     * @return self
     */
    public function setEciFlag(?string $eciFlag): self
    {
        $this->eciFlag = $eciFlag;

        return $this;
    }

    /**
     * Returns Token Requestor Id.
     * A TRID, or a Token Requestor ID, is an identifier used by merchants to request network tokens from
     * card networks. A TRID is a precursor to obtaining a network token for a credit card primary account
     * number (PAN), and will aid in enabling secure card on file (COF) payments and reducing fraud.
     */
    public function getTokenRequestorId(): ?string
    {
        return $this->tokenRequestorId;
    }

    /**
     * Sets Token Requestor Id.
     * A TRID, or a Token Requestor ID, is an identifier used by merchants to request network tokens from
     * card networks. A TRID is a precursor to obtaining a network token for a credit card primary account
     * number (PAN), and will aid in enabling secure card on file (COF) payments and reducing fraud.
     *
     * @maps token_requestor_id
     * @return self
     */
    public function setTokenRequestorId(?string $tokenRequestorId): self
    {
        $this->tokenRequestorId = $tokenRequestorId;

        return $this;
    }
}
