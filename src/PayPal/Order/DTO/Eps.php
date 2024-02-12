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

class Eps
{
    /**
     * The full name representation like Mr J Smith.
     *
     * @var string|null
     */
    protected $name;

    /**
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or region.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; The country code for Great Britain is &lt;code&gt;GB&lt;/code&gt; and not &lt;code&gt;UK&lt;/code&gt; as used in the top-level domain names for that country. Use the &#x60;C2&#x60; country code for China worldwide for comparable uncontrolled price (CUP) method, bank card, and cross-border transactions.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $country_code;

    /**
     * The business identification code (BIC). In payments systems, a BIC is used to identify a specific business, most commonly a bank.
     *
     * @var string|null
     */
    protected $bic;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->country_code = isset($data['country_code']) ? $data['country_code'] : null;
        $this->bic = isset($data['bic']) ? $data['bic'] : null;
    }

    /**
     * Gets name.
     *
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name.
     *
     * @param string|null $name the full name representation like Mr J Smith
     *
     * @return $this
     */
    public function setName($name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets country_code.
     *
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Sets country_code.
     *
     * @param string|null $country_code The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or region.<blockquote><strong>Note:</strong> The country code for Great Britain is <code>GB</code> and not <code>UK</code> as used in the top-level domain names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled price (CUP) method, bank card, and cross-border transactions.</blockquote>
     *
     * @return $this
     */
    public function setCountryCode($country_code = null)
    {
        $this->country_code = $country_code;

        return $this;
    }

    /**
     * Gets bic.
     *
     * @return string|null
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * Sets bic.
     *
     * @param string|null $bic The business identification code (BIC). In payments systems, a BIC is used to identify a specific business, most commonly a bank.
     *
     * @return $this
     */
    public function setBic($bic = null)
    {
        $this->bic = $bic;

        return $this;
    }
}
