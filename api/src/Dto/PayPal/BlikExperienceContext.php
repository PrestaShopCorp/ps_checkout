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
 * Customizes the payer experience during the approval process for the BLIK payment.
 */
class BlikExperienceContext
{
    /**
     * @var string|null
     */
    private $brandName;

    /**
     * @var string|null
     */
    private $locale;

    /**
     * @var string|null
     */
    private $shippingPreference = ExperienceContextShippingPreference::GET_FROM_FILE;

    /**
     * @var string|null
     */
    private $returnUrl;

    /**
     * @var string|null
     */
    private $cancelUrl;

    /**
     * @var string|null
     */
    private $consumerIp;

    /**
     * @var string|null
     */
    private $consumerUserAgent;

    /**
     * Returns Brand Name.
     * The label that overrides the business name in the PayPal account on the PayPal site. The pattern is
     * defined by an external party and supports Unicode.
     */
    public function getBrandName(): ?string
    {
        return $this->brandName;
    }

    /**
     * Sets Brand Name.
     * The label that overrides the business name in the PayPal account on the PayPal site. The pattern is
     * defined by an external party and supports Unicode.
     *
     * @maps brand_name
     */
    public function setBrandName(?string $brandName): void
    {
        $this->brandName = $brandName;
    }

    /**
     * Returns Locale.
     * The [language tag](https://tools.ietf.org/html/bcp47#section-2) for the language in which to
     * localize the error-related strings, such as messages, issues, and suggested actions. The tag is made
     * up of the [ISO 639-2 language code](https://www.loc.gov/standards/iso639-2/php/code_list.php), the
     * optional [ISO-15924 script tag](https://www.unicode.org/iso15924/codelists.html), and the [ISO-3166
     * alpha-2 country code](/api/rest/reference/country-codes/) or [M49 region code](https://unstats.un.
     * org/unsd/methodology/m49/).
     */
    public function getLocale(): ?string
    {
        return $this->locale;
    }

    /**
     * Sets Locale.
     * The [language tag](https://tools.ietf.org/html/bcp47#section-2) for the language in which to
     * localize the error-related strings, such as messages, issues, and suggested actions. The tag is made
     * up of the [ISO 639-2 language code](https://www.loc.gov/standards/iso639-2/php/code_list.php), the
     * optional [ISO-15924 script tag](https://www.unicode.org/iso15924/codelists.html), and the [ISO-3166
     * alpha-2 country code](/api/rest/reference/country-codes/) or [M49 region code](https://unstats.un.
     * org/unsd/methodology/m49/).
     *
     * @maps locale
     */
    public function setLocale(?string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Returns Shipping Preference.
     * The location from which the shipping address is derived.
     */
    public function getShippingPreference(): ?string
    {
        return $this->shippingPreference;
    }

    /**
     * Sets Shipping Preference.
     * The location from which the shipping address is derived.
     *
     * @maps shipping_preference
     */
    public function setShippingPreference(?string $shippingPreference): void
    {
        $this->shippingPreference = $shippingPreference;
    }

    /**
     * Returns Return Url.
     * Describes the URL.
     */
    public function getReturnUrl(): ?string
    {
        return $this->returnUrl;
    }

    /**
     * Sets Return Url.
     * Describes the URL.
     *
     * @maps return_url
     */
    public function setReturnUrl(?string $returnUrl): void
    {
        $this->returnUrl = $returnUrl;
    }

    /**
     * Returns Cancel Url.
     * Describes the URL.
     */
    public function getCancelUrl(): ?string
    {
        return $this->cancelUrl;
    }

    /**
     * Sets Cancel Url.
     * Describes the URL.
     *
     * @maps cancel_url
     */
    public function setCancelUrl(?string $cancelUrl): void
    {
        $this->cancelUrl = $cancelUrl;
    }

    /**
     * Returns Consumer Ip.
     * An Internet Protocol address (IP address). This address assigns a numerical label to each device
     * that is connected to a computer network through the Internet Protocol. Supports IPv4 and IPv6
     * addresses.
     */
    public function getConsumerIp(): ?string
    {
        return $this->consumerIp;
    }

    /**
     * Sets Consumer Ip.
     * An Internet Protocol address (IP address). This address assigns a numerical label to each device
     * that is connected to a computer network through the Internet Protocol. Supports IPv4 and IPv6
     * addresses.
     *
     * @maps consumer_ip
     */
    public function setConsumerIp(?string $consumerIp): void
    {
        $this->consumerIp = $consumerIp;
    }

    /**
     * Returns Consumer User Agent.
     * The payer's User Agent. For example, Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0).
     */
    public function getConsumerUserAgent(): ?string
    {
        return $this->consumerUserAgent;
    }

    /**
     * Sets Consumer User Agent.
     * The payer's User Agent. For example, Mozilla/5.0 (Macintosh; Intel Mac OS X x.y; rv:42.0).
     *
     * @maps consumer_user_agent
     */
    public function setConsumerUserAgent(?string $consumerUserAgent): void
    {
        $this->consumerUserAgent = $consumerUserAgent;
    }
}
