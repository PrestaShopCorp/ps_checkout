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
 * The portable international postal address. Maps to [AddressValidationMetadata](https://github.
 * com/googlei18n/libaddressinput/wiki/AddressValidationMetadata) and HTML 5.1 [Autofilling form
 * controls: the autocomplete attribute](https://www.w3.org/TR/html51/sec-forms.html#autofilling-form-
 * controls-the-autocomplete-attribute).
 */
class Address
{
    /**
     * @var string|null
     */
    private $addressLine1;

    /**
     * @var string|null
     */
    private $addressLine2;

    /**
     * @var string|null
     */
    private $adminArea2;

    /**
     * @var string|null
     */
    private $adminArea1;

    /**
     * @var string|null
     */
    private $postalCode;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @param string $countryCode
     */
    public function __construct(
        string $countryCode,
        ?string $addressLine1 = null,
        ?string $addressLine2 = null,
        ?string $adminArea2 = null,
        ?string $adminArea1 = null,
        ?string $postalCode = null
    ) {
        $this->countryCode = $countryCode;
        $this->addressLine1 = $addressLine1;
        $this->addressLine2 = $addressLine2;
        $this->adminArea2 = $adminArea2;
        $this->adminArea1 = $adminArea1;
        $this->postalCode = $postalCode;
    }

    /**
     * Returns Address Line 1.
     * The first line of the address, such as number and street, for example, `173 Drury Lane`. Needed for
     * data entry, and Compliance and Risk checks. This field needs to pass the full address.
     */
    public function getAddressLine1(): ?string
    {
        return $this->addressLine1;
    }

    /**
     * Sets Address Line 1.
     * The first line of the address, such as number and street, for example, `173 Drury Lane`. Needed for
     * data entry, and Compliance and Risk checks. This field needs to pass the full address.
     *
     * @maps address_line_1
     * @return self
     */
    public function setAddressLine1(?string $addressLine1): self
    {
        $this->addressLine1 = $addressLine1;

        return $this;
    }

    /**
     * Returns Address Line 2.
     * The second line of the address, for example, a suite or apartment number.
     */
    public function getAddressLine2(): ?string
    {
        return $this->addressLine2;
    }

    /**
     * Sets Address Line 2.
     * The second line of the address, for example, a suite or apartment number.
     *
     * @maps address_line_2
     * @return self
     */
    public function setAddressLine2(?string $addressLine2): self
    {
        $this->addressLine2 = $addressLine2;

        return $this;
    }

    /**
     * Returns Admin Area 2.
     * A city, town, or village. Smaller than `admin_area_level_1`.
     */
    public function getAdminArea2(): ?string
    {
        return $this->adminArea2;
    }

    /**
     * Sets Admin Area 2.
     * A city, town, or village. Smaller than `admin_area_level_1`.
     *
     * @maps admin_area_2
     * @return self
     */
    public function setAdminArea2(?string $adminArea2): self
    {
        $this->adminArea2 = $adminArea2;

        return $this;
    }

    /**
     * Returns Admin Area 1.
     * The highest-level sub-division in a country, which is usually a province, state, or ISO-3166-2
     * subdivision. This data is formatted for postal delivery, for example, `CA` and not `California`.
     * Value, by country, is: UK. A county. US. A state. Canada. A province. Japan. A prefecture.
     * Switzerland. A *kanton*.
     */
    public function getAdminArea1(): ?string
    {
        return $this->adminArea1;
    }

    /**
     * Sets Admin Area 1.
     * The highest-level sub-division in a country, which is usually a province, state, or ISO-3166-2
     * subdivision. This data is formatted for postal delivery, for example, `CA` and not `California`.
     * Value, by country, is: UK. A county. US. A state. Canada. A province. Japan. A prefecture.
     * Switzerland. A *kanton*.
     *
     * @maps admin_area_1
     * @return self
     */
    public function setAdminArea1(?string $adminArea1): self
    {
        $this->adminArea1 = $adminArea1;

        return $this;
    }

    /**
     * Returns Postal Code.
     * The postal code, which is the ZIP code or equivalent. Typically required for countries with a postal
     * code or an equivalent. See [postal code](https://en.wikipedia.org/wiki/Postal_code).
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * Sets Postal Code.
     * The postal code, which is the ZIP code or equivalent. Typically required for countries with a postal
     * code or an equivalent. See [postal code](https://en.wikipedia.org/wiki/Postal_code).
     *
     * @maps postal_code
     * @return self
     */
    public function setPostalCode(?string $postalCode): self
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * Returns Country Code.
     * The [2-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or
     * region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
     */
    public function getCountryCode(): string
    {
        return $this->countryCode;
    }

    /**
     * Sets Country Code.
     * The [2-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or
     * region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
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
}
