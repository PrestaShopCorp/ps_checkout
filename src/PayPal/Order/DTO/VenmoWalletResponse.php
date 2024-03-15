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

class VenmoWalletResponse
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
     * The Venmo user name chosen by the user, also know as a Venmo handle.
     *
     * @var string|null
     */
    protected $user_name;

    /**
     * @var Name|null
     */
    protected $name;

    /**
     * @var Phone|null
     */
    protected $phone_number;

    /**
     * @var AddressPortable2|null
     */
    protected $address;

    /**
     * @var VenmoWalletAttributesResponse|null
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
        $this->user_name = isset($data['user_name']) ? $data['user_name'] : null;
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->phone_number = isset($data['phone_number']) ? $data['phone_number'] : null;
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
     * Gets user_name.
     *
     * @return string|null
     */
    public function getUserName()
    {
        return $this->user_name;
    }

    /**
     * Sets user_name.
     *
     * @param string|null $user_name the Venmo user name chosen by the user, also know as a Venmo handle
     *
     * @return $this
     */
    public function setUserName($user_name = null)
    {
        $this->user_name = $user_name;

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
     * @return VenmoWalletAttributesResponse|null
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Sets attributes.
     *
     * @param VenmoWalletAttributesResponse|null $attributes
     *
     * @return $this
     */
    public function setAttributes(VenmoWalletAttributesResponse $attributes = null)
    {
        $this->attributes = $attributes;

        return $this;
    }
}
