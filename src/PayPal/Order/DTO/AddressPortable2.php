<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class AddressPortable2
{
    /**
     * The [2-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or region.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; The country code for Great Britain is &lt;code&gt;GB&lt;/code&gt; and not &lt;code&gt;UK&lt;/code&gt; as used in the top-level domain names for that country. Use the &#x60;C2&#x60; country code for China worldwide for comparable uncontrolled price (CUP) method, bank card, and cross-border transactions.&lt;/blockquote&gt;
     *
     * @var string
     */
    protected $country_code;
    /**
     * The first line of the address, such as number and street, for example, &#x60;173 Drury Lane&#x60;. Needed for data entry, and Compliance and Risk checks. This field needs to pass the full address.
     *
     * @var string|null
     */
    protected $address_line_1;
    /**
     * The second line of the address, for example, a suite or apartment number.
     *
     * @var string|null
     */
    protected $address_line_2;
    /**
     * The third line of the address, if needed. Examples include a street complement for Brazil, direction text, such as &#x60;next to Walmart&#x60;, or a landmark in an Indian address.
     *
     * @var string|null
     */
    protected $address_line_3;
    /**
     * The neighborhood, ward, or district. This is smaller than &#x60;admin_area_level_3&#x60; or &#x60;sub_locality&#x60;. Value is:&lt;ul&gt;&lt;li&gt;The postal sorting code that is used in Guernsey and many French territories, such as French Guiana.&lt;/li&gt;&lt;li&gt;The fine-grained administrative levels in China.&lt;/li&gt;&lt;/ul&gt;
     *
     * @var string|null
     */
    protected $admin_area_4;
    /**
     * The sub-locality, suburb, neighborhood, or district. This is smaller than &#x60;admin_area_level_2&#x60;. Value is:&lt;ul&gt;&lt;li&gt;Brazil. Suburb, *bairro*, or neighborhood.&lt;/li&gt;&lt;li&gt;India. Sub-locality or district. Street name information isn&#39;t always available, but a sub-locality or district can be a very small area.&lt;/li&gt;&lt;/ul&gt;
     *
     * @var string|null
     */
    protected $admin_area_3;
    /**
     * A city, town, or village. Smaller than &#x60;admin_area_level_1&#x60;.
     *
     * @var string|null
     */
    protected $admin_area_2;
    /**
     * The highest-level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision. This data is formatted for postal delivery, for example, &#x60;CA&#x60; and not &#x60;California&#x60;. Value, by country, is:&lt;ul&gt;&lt;li&gt;UK. A county.&lt;/li&gt;&lt;li&gt;US. A state.&lt;/li&gt;&lt;li&gt;Canada. A province.&lt;/li&gt;&lt;li&gt;Japan. A prefecture.&lt;/li&gt;&lt;li&gt;Switzerland. A *kanton*.&lt;/li&gt;&lt;/ul&gt;
     *
     * @var string|null
     */
    protected $admin_area_1;
    /**
     * The postal code, which is the ZIP code or equivalent. Typically required for countries with a postal code or an equivalent. See [postal code](https://en.wikipedia.org/wiki/Postal_code).
     *
     * @var string|null
     */
    protected $postal_code;
    /**
     * @var AddressDetails|null
     */
    protected $address_details;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->country_code = isset($data['country_code']) ? $data['country_code'] : null;
        $this->address_line_1 = isset($data['address_line_1']) ? $data['address_line_1'] : null;
        $this->address_line_2 = isset($data['address_line_2']) ? $data['address_line_2'] : null;
        $this->address_line_3 = isset($data['address_line_3']) ? $data['address_line_3'] : null;
        $this->admin_area_4 = isset($data['admin_area_4']) ? $data['admin_area_4'] : null;
        $this->admin_area_3 = isset($data['admin_area_3']) ? $data['admin_area_3'] : null;
        $this->admin_area_2 = isset($data['admin_area_2']) ? $data['admin_area_2'] : null;
        $this->admin_area_1 = isset($data['admin_area_1']) ? $data['admin_area_1'] : null;
        $this->postal_code = isset($data['postal_code']) ? $data['postal_code'] : null;
        $this->address_details = isset($data['address_details']) ? $data['address_details'] : null;
    }

    /**
     * Gets country_code.
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * Sets country_code.
     *
     * @param string $country_code The [2-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or region.<blockquote><strong>Note:</strong> The country code for Great Britain is <code>GB</code> and not <code>UK</code> as used in the top-level domain names for that country. Use the `C2` country code for China worldwide for comparable uncontrolled price (CUP) method, bank card, and cross-border transactions.</blockquote>
     *
     * @return $this
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;

        return $this;
    }

    /**
     * Gets address_line_1.
     *
     * @return string|null
     */
    public function getAddressLine1()
    {
        return $this->address_line_1;
    }

    /**
     * Sets address_line_1.
     *
     * @param string|null $address_line_1 The first line of the address, such as number and street, for example, `173 Drury Lane`. Needed for data entry, and Compliance and Risk checks. This field needs to pass the full address.
     *
     * @return $this
     */
    public function setAddressLine1($address_line_1 = null)
    {
        $this->address_line_1 = $address_line_1;

        return $this;
    }

    /**
     * Gets address_line_2.
     *
     * @return string|null
     */
    public function getAddressLine2()
    {
        return $this->address_line_2;
    }

    /**
     * Sets address_line_2.
     *
     * @param string|null $address_line_2 the second line of the address, for example, a suite or apartment number
     *
     * @return $this
     */
    public function setAddressLine2($address_line_2 = null)
    {
        $this->address_line_2 = $address_line_2;

        return $this;
    }

    /**
     * Gets address_line_3.
     *
     * @return string|null
     */
    public function getAddressLine3()
    {
        return $this->address_line_3;
    }

    /**
     * Sets address_line_3.
     *
     * @param string|null $address_line_3 The third line of the address, if needed. Examples include a street complement for Brazil, direction text, such as `next to Walmart`, or a landmark in an Indian address.
     *
     * @return $this
     */
    public function setAddressLine3($address_line_3 = null)
    {
        $this->address_line_3 = $address_line_3;

        return $this;
    }

    /**
     * Gets admin_area_4.
     *
     * @return string|null
     */
    public function getAdminArea4()
    {
        return $this->admin_area_4;
    }

    /**
     * Sets admin_area_4.
     *
     * @param string|null $admin_area_4 The neighborhood, ward, or district. This is smaller than `admin_area_level_3` or `sub_locality`. Value is:<ul><li>The postal sorting code that is used in Guernsey and many French territories, such as French Guiana.</li><li>The fine-grained administrative levels in China.</li></ul>
     *
     * @return $this
     */
    public function setAdminArea4($admin_area_4 = null)
    {
        $this->admin_area_4 = $admin_area_4;

        return $this;
    }

    /**
     * Gets admin_area_3.
     *
     * @return string|null
     */
    public function getAdminArea3()
    {
        return $this->admin_area_3;
    }

    /**
     * Sets admin_area_3.
     *
     * @param string|null $admin_area_3 The sub-locality, suburb, neighborhood, or district. This is smaller than `admin_area_level_2`. Value is:<ul><li>Brazil. Suburb, *bairro*, or neighborhood.</li><li>India. Sub-locality or district. Street name information isn't always available, but a sub-locality or district can be a very small area.</li></ul>
     *
     * @return $this
     */
    public function setAdminArea3($admin_area_3 = null)
    {
        $this->admin_area_3 = $admin_area_3;

        return $this;
    }

    /**
     * Gets admin_area_2.
     *
     * @return string|null
     */
    public function getAdminArea2()
    {
        return $this->admin_area_2;
    }

    /**
     * Sets admin_area_2.
     *
     * @param string|null $admin_area_2 A city, town, or village. Smaller than `admin_area_level_1`.
     *
     * @return $this
     */
    public function setAdminArea2($admin_area_2 = null)
    {
        $this->admin_area_2 = $admin_area_2;

        return $this;
    }

    /**
     * Gets admin_area_1.
     *
     * @return string|null
     */
    public function getAdminArea1()
    {
        return $this->admin_area_1;
    }

    /**
     * Sets admin_area_1.
     *
     * @param string|null $admin_area_1 The highest-level sub-division in a country, which is usually a province, state, or ISO-3166-2 subdivision. This data is formatted for postal delivery, for example, `CA` and not `California`. Value, by country, is:<ul><li>UK. A county.</li><li>US. A state.</li><li>Canada. A province.</li><li>Japan. A prefecture.</li><li>Switzerland. A *kanton*.</li></ul>
     *
     * @return $this
     */
    public function setAdminArea1($admin_area_1 = null)
    {
        $this->admin_area_1 = $admin_area_1;

        return $this;
    }

    /**
     * Gets postal_code.
     *
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * Sets postal_code.
     *
     * @param string|null $postal_code The postal code, which is the ZIP code or equivalent. Typically required for countries with a postal code or an equivalent. See [postal code](https://en.wikipedia.org/wiki/Postal_code).
     *
     * @return $this
     */
    public function setPostalCode($postal_code = null)
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    /**
     * Gets address_details.
     *
     * @return AddressDetails|null
     */
    public function getAddressDetails()
    {
        return $this->address_details;
    }

    /**
     * Sets address_details.
     *
     * @param AddressDetails|null $address_details
     *
     * @return $this
     */
    public function setAddressDetails(AddressDetails $address_details = null)
    {
        $this->address_details = $address_details;

        return $this;
    }
}
