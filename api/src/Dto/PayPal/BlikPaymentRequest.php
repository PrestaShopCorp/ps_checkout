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
 * Information needed to pay using BLIK.
 */
class BlikPaymentRequest
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
     * @var string|null
     */
    private $email;

    /**
     * @var BlikExperienceContext|null
     */
    private $experienceContext;

    /**
     * @var BlikLevel0PaymentObject|null
     */
    private $level0;

    /**
     * @var BlikOneClickPaymentRequest|null
     */
    private $oneClick;

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
     * @return self
     */
    public function setName(string $name): self
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
     * @return self
     */
    public function setCountryCode(string $countryCode): self
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Returns Email.
     * The internationalized email address. Note: Up to 64 characters are allowed before and 255 characters
     * are allowed after the @ sign. However, the generally accepted maximum length for an email address is
     * 254 characters. The pattern verifies that an unquoted @ sign exists.
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Sets Email.
     * The internationalized email address. Note: Up to 64 characters are allowed before and 255 characters
     * are allowed after the @ sign. However, the generally accepted maximum length for an email address is
     * 254 characters. The pattern verifies that an unquoted @ sign exists.
     *
     * @maps email
     * @return self
     */
    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Returns Experience Context.
     * Customizes the payer experience during the approval process for the BLIK payment.
     */
    public function getExperienceContext(): ?BlikExperienceContext
    {
        return $this->experienceContext;
    }

    /**
     * Sets Experience Context.
     * Customizes the payer experience during the approval process for the BLIK payment.
     *
     * @maps experience_context
     * @return self
     */
    public function setExperienceContext(?BlikExperienceContext $experienceContext): self
    {
        $this->experienceContext = $experienceContext;

        return $this;
    }

    /**
     * Returns Level 0.
     * Information used to pay using BLIK level_0 flow.
     */
    public function getLevel0(): ?BlikLevel0PaymentObject
    {
        return $this->level0;
    }

    /**
     * Sets Level 0.
     * Information used to pay using BLIK level_0 flow.
     *
     * @maps level_0
     * @return self
     */
    public function setLevel0(?BlikLevel0PaymentObject $level0): self
    {
        $this->level0 = $level0;

        return $this;
    }

    /**
     * Returns One Click.
     * Information used to pay using BLIK one-click flow.
     */
    public function getOneClick(): ?BlikOneClickPaymentRequest
    {
        return $this->oneClick;
    }

    /**
     * Sets One Click.
     * Information used to pay using BLIK one-click flow.
     *
     * @maps one_click
     * @return self
     */
    public function setOneClick(?BlikOneClickPaymentRequest $oneClick): self
    {
        $this->oneClick = $oneClick;

        return $this;
    }
}
