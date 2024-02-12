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

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class BinDetails
{
    /**
     * The Bank Identification Number (BIN) signifies the number that is being used to identify the granular level details (except the PII information) of the card.
     *
     * @var string|null
     */
    protected $bin;

    /**
     * The issuer of the card instrument.
     *
     * @var string|null
     */
    protected $issuing_bank;

    /**
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or region.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; The country code for Great Britain is &lt;code&gt;GB&lt;/code&gt; and not &lt;code&gt;UK&lt;/code&gt; as used in the top-level domain names for that country. Use the &#x60;C2&#x60; country code for China worldwide for comparable uncontrolled price (CUP) method, bank card, and cross-border transactions.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $bin_country_code;

    /**
     * The type of card product assigned to the BIN by the issuer. These values are defined by the issuer and may change over time. Some examples include: PREPAID_GIFT, CONSUMER, CORPORATE.
     *
     * @var string[]|null
     */
    protected $products;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->bin = isset($data['bin']) ? $data['bin'] : null;
        $this->issuing_bank = isset($data['issuing_bank']) ? $data['issuing_bank'] : null;
        $this->bin_country_code = isset($data['bin_country_code']) ? $data['bin_country_code'] : null;
        $this->products = isset($data['products']) ? $data['products'] : null;
    }

    /**
     * Gets bin.
     *
     * @return string|null
     */
    public function getBin()
    {
        return $this->bin;
    }

    /**
     * Sets bin.
     *
     * @param string|null $bin the Bank Identification Number (BIN) signifies the number that is being used to identify the granular level details (except the PII information) of the card
     *
     * @return $this
     */
    public function setBin($bin = null)
    {
        $this->bin = $bin;

        return $this;
    }

    /**
     * Gets issuing_bank.
     *
     * @return string|null
     */
    public function getIssuingBank()
    {
        return $this->issuing_bank;
    }

    /**
     * Sets issuing_bank.
     *
     * @param string|null $issuing_bank the issuer of the card instrument
     *
     * @return $this
     */
    public function setIssuingBank($issuing_bank = null)
    {
        $this->issuing_bank = $issuing_bank;

        return $this;
    }

    /**
     * Gets bin_country_code.
     *
     * @return string|null
     */
    public function getBinCountryCode()
    {
        return $this->bin_country_code;
    }

    /**
     * Sets bin_country_code.
     *
     * @param string|null $bin_country_code The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or region.<blockquote><strong>Note:</strong> The country code for Great Britain is <code>GB</code> and not <code>UK</code> as used in the top-level domain names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled price (CUP) method, bank card, and cross-border transactions.</blockquote>
     *
     * @return $this
     */
    public function setBinCountryCode($bin_country_code = null)
    {
        $this->bin_country_code = $bin_country_code;

        return $this;
    }

    /**
     * Gets products.
     *
     * @return string[]|null
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Sets products.
     *
     * @param string[]|null $products The type of card product assigned to the BIN by the issuer. These values are defined by the issuer and may change over time. Some examples include: PREPAID_GIFT, CONSUMER, CORPORATE.
     *
     * @return $this
     */
    public function setProducts(array $products = null)
    {
        $this->products = $products;

        return $this;
    }
}
