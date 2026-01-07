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

namespace PsCheckout\Api\Dto\PayPal\Order;

/**
 * Bank Identification Number (BIN) details used to fund a payment.
 */
class BinDetails
{
    /**
     * @var string|null
     */
    private $bin;

    /**
     * @var string|null
     */
    private $issuingBank;

    /**
     * @var string|null
     */
    private $binCountryCode;

    /**
     * @var string[]|null
     */
    private $products;

    /**
     * Returns Bin.
     * The Bank Identification Number (BIN) signifies the number that is being used to identify the
     * granular level details (except the PII information) of the card.
     */
    public function getBin(): ?string
    {
        return $this->bin;
    }

    /**
     * Sets Bin.
     * The Bank Identification Number (BIN) signifies the number that is being used to identify the
     * granular level details (except the PII information) of the card.
     *
     * @maps bin
     */
    public function setBin(?string $bin): void
    {
        $this->bin = $bin;
    }

    /**
     * Returns Issuing Bank.
     * The issuer of the card instrument.
     */
    public function getIssuingBank(): ?string
    {
        return $this->issuingBank;
    }

    /**
     * Sets Issuing Bank.
     * The issuer of the card instrument.
     *
     * @maps issuing_bank
     */
    public function setIssuingBank(?string $issuingBank): void
    {
        $this->issuingBank = $issuingBank;
    }

    /**
     * Returns Bin Country Code.
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country
     * or region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
     */
    public function getBinCountryCode(): ?string
    {
        return $this->binCountryCode;
    }

    /**
     * Sets Bin Country Code.
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country
     * or region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
     *
     * @maps bin_country_code
     */
    public function setBinCountryCode(?string $binCountryCode): void
    {
        $this->binCountryCode = $binCountryCode;
    }

    /**
     * Returns Products.
     * The type of card product assigned to the BIN by the issuer. These values are defined by the issuer
     * and may change over time. Some examples include: PREPAID_GIFT, CONSUMER, CORPORATE.
     *
     * @return string[]|null
     */
    public function getProducts(): ?array
    {
        return $this->products;
    }

    /**
     * Sets Products.
     * The type of card product assigned to the BIN by the issuer. These values are defined by the issuer
     * and may change over time. Some examples include: PREPAID_GIFT, CONSUMER, CORPORATE.
     *
     * @maps products
     *
     * @param string[]|null $products
     */
    public function setProducts(?array $products): void
    {
        $this->products = $products;
    }
}
