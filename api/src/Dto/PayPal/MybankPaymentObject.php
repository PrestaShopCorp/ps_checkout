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
 * Information used to pay using MyBank.
 */
class MybankPaymentObject
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $countryCode;

    /**
     * @var string|null
     */
    private $bic;

    /**
     * @var string|null
     */
    private $ibanLastChars;

    /**
     * Returns Name.
     * The full name representation like Mr J Smith.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The full name representation like Mr J Smith.
     *
     * @maps name
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns Country Code.
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country
     * or region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
     */
    public function getCountryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * Sets Country Code.
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country
     * or region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
     *
     * @maps country_code
     * @return self
     */
    public function setCountryCode(?string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Returns Bic.
     * The business identification code (BIC). In payments systems, a BIC is used to identify a specific
     * business, most commonly a bank.
     */
    public function getBic(): ?string
    {
        return $this->bic;
    }

    /**
     * Sets Bic.
     * The business identification code (BIC). In payments systems, a BIC is used to identify a specific
     * business, most commonly a bank.
     *
     * @maps bic
     * @return self
     */
    public function setBic(?string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    /**
     * Returns Iban Last Chars.
     * The last characters of the IBAN used to pay.
     */
    public function getIbanLastChars(): ?string
    {
        return $this->ibanLastChars;
    }

    /**
     * Sets Iban Last Chars.
     * The last characters of the IBAN used to pay.
     *
     * @maps iban_last_chars
     * @return self
     */
    public function setIbanLastChars(?string $ibanLastChars): self
    {
        $this->ibanLastChars = $ibanLastChars;

        return $this;
    }
}
