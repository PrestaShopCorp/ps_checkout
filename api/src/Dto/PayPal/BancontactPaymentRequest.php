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
 * Information needed to pay using Bancontact.
 */
class BancontactPaymentRequest
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $countryCode;

    /**
     * @var ExperienceContext|null
     */
    private $experienceContext;

    /**
     * @param string $name
     * @param string $countryCode
     */
    public function __construct(string $name, string $countryCode)
    {
        $this->name = $name;
        $this->countryCode = $countryCode;
    }

    /**
     * Returns Name.
     * The full name representation like Mr J Smith.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets Name.
     * The full name representation like Mr J Smith.
     *
     * @required
     * @maps name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns Country Code.
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country
     * or region. Note: The country code for Great Britain is GB and not UK as used in the top-level domain
     * names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled
     * price (CUP) method, bank card, and cross-border transactions.
     */
    public function getCountryCode(): string
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
     * @required
     * @maps country_code
     */
    public function setCountryCode(string $countryCode): void
    {
        $this->countryCode = $countryCode;
    }

    /**
     * Returns Experience Context.
     * Customizes the payer experience during the approval process for the payment.
     */
    public function getExperienceContext(): ?ExperienceContext
    {
        return $this->experienceContext;
    }

    /**
     * Sets Experience Context.
     * Customizes the payer experience during the approval process for the payment.
     *
     * @maps experience_context
     */
    public function setExperienceContext(?ExperienceContext $experienceContext): void
    {
        $this->experienceContext = $experienceContext;
    }
}
