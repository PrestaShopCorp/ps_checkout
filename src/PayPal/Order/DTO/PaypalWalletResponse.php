<?php

namespace PrestaShop\Module\PrestashopCheckout\PayPal\Order\DTO;

class PaypalWalletResponse
{
    /**
     * The internationalized email address.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; Up to 64 characters are allowed before and 255 characters are allowed after the &lt;code&gt;@&lt;/code&gt; sign. However, the generally accepted maximum length for an email address is 254 characters. The pattern verifies that an unquoted &lt;code&gt;@&lt;/code&gt; sign exists.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $email_address;

    /**
     * The PayPal payer ID, which is a masked version of the PayPal account number intended for use with third parties. The account number is reversibly encrypted and a proprietary variant of Base32 is used to encode the result.
     *
     * @var string|null
     */
    protected $account_id;

    /**
     * @var Name|null
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $phone_type;

    /**
     * @var Phone|null
     */
    protected $phone_number;

    /**
     * The stand-alone date, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). To represent special legal values, such as a date of birth, you should use dates with no associated time or time-zone data. Whenever possible, use the standard &#x60;date_time&#x60; type. This regular expression does not validate all dates. For example, February 31 is valid and nothing is known about leap years.
     *
     * @var string|null
     */
    protected $birth_date;

    /**
     * @var TaxInfo|null
     */
    protected $tax_info;

    /**
     * @var AddressPortable2|null
     */
    protected $address;

    /**
     * @var PaypalWalletAttributesResponse|null
     */
    protected $attributes;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->email_address = isset($data['email_address']) ? $data['email_address'] : null;
        $this->account_id = isset($data['account_id']) ? $data['account_id'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->phone_type = isset($data['phone_type']) ? $data['phone_type'] : null;
        $this->phone_number = isset($data['phone_number']) ? $data['phone_number'] : null;
        $this->birth_date = isset($data['birth_date']) ? $data['birth_date'] : null;
        $this->tax_info = isset($data['tax_info']) ? $data['tax_info'] : null;
        $this->address = isset($data['address']) ? $data['address'] : null;
        $this->attributes = isset($data['attributes']) ? $data['attributes'] : null;
    }

    /**
     * Gets email_address.
     *
     * @return string|null
     */
    public function getEmailAddress()
    {
        return $this->email_address;
    }

    /**
     * Sets email_address.
     *
     * @param string|null $email_address The internationalized email address.<blockquote><strong>Note:</strong> Up to 64 characters are allowed before and 255 characters are allowed after the <code>@</code> sign. However, the generally accepted maximum length for an email address is 254 characters. The pattern verifies that an unquoted <code>@</code> sign exists.</blockquote>
     *
     * @return $this
     */
    public function setEmailAddress($email_address = null)
    {
        $this->email_address = $email_address;

        return $this;
    }

    /**
     * Gets account_id.
     *
     * @return string|null
     */
    public function getAccountId()
    {
        return $this->account_id;
    }

    /**
     * Sets account_id.
     *
     * @param string|null $account_id The PayPal payer ID, which is a masked version of the PayPal account number intended for use with third parties. The account number is reversibly encrypted and a proprietary variant of Base32 is used to encode the result.
     *
     * @return $this
     */
    public function setAccountId($account_id = null)
    {
        $this->account_id = $account_id;

        return $this;
    }

    /**
     * Gets name.
     *
     * @return Name|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets name.
     *
     * @param Name|null $name
     *
     * @return $this
     */
    public function setName(Name $name = null)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Gets phone_type.
     *
     * @return string|null
     */
    public function getPhoneType()
    {
        return $this->phone_type;
    }

    /**
     * Sets phone_type.
     *
     * @param string|null $phone_type
     *
     * @return $this
     */
    public function setPhoneType($phone_type = null)
    {
        $this->phone_type = $phone_type;

        return $this;
    }

    /**
     * Gets phone_number.
     *
     * @return Phone|null
     */
    public function getPhoneNumber()
    {
        return $this->phone_number;
    }

    /**
     * Sets phone_number.
     *
     * @param Phone|null $phone_number
     *
     * @return $this
     */
    public function setPhoneNumber(Phone $phone_number = null)
    {
        $this->phone_number = $phone_number;

        return $this;
    }

    /**
     * Gets birth_date.
     *
     * @return string|null
     */
    public function getBirthDate()
    {
        return $this->birth_date;
    }

    /**
     * Sets birth_date.
     *
     * @param string|null $birth_date The stand-alone date, in [Internet date and time format](https://tools.ietf.org/html/rfc3339#section-5.6). To represent special legal values, such as a date of birth, you should use dates with no associated time or time-zone data. Whenever possible, use the standard `date_time` type. This regular expression does not validate all dates. For example, February 31 is valid and nothing is known about leap years.
     *
     * @return $this
     */
    public function setBirthDate($birth_date = null)
    {
        $this->birth_date = $birth_date;

        return $this;
    }

    /**
     * Gets tax_info.
     *
     * @return TaxInfo|null
     */
    public function getTaxInfo()
    {
        return $this->tax_info;
    }

    /**
     * Sets tax_info.
     *
     * @param TaxInfo|null $tax_info
     *
     * @return $this
     */
    public function setTaxInfo(TaxInfo $tax_info = null)
    {
        $this->tax_info = $tax_info;

        return $this;
    }

    /**
     * Gets address.
     *
     * @return AddressPortable2|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Sets address.
     *
     * @param AddressPortable2|null $address
     *
     * @return $this
     */
    public function setAddress(AddressPortable2 $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Gets attributes.
     *
     * @return PaypalWalletAttributesResponse|null
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Sets attributes.
     *
     * @param PaypalWalletAttributesResponse|null $attributes
     *
     * @return $this
     */
    public function setAttributes(PaypalWalletAttributesResponse $attributes = null)
    {
        $this->attributes = $attributes;

        return $this;
    }
}
