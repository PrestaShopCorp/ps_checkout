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

class Payee
{
    /**
     * The internationalized email address.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; Up to 64 characters are allowed before and 255 characters are allowed after the &lt;code&gt;@&lt;/code&gt; sign. However, the generally accepted maximum length for an email address is 254 characters. The pattern verifies that an unquoted &lt;code&gt;@&lt;/code&gt; sign exists.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $email_address;

    /**
     * The account identifier for a PayPal account.
     *
     * @var string|null
     */
    protected $merchant_id;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->email_address = isset($data['email_address']) ? $data['email_address'] : null;
        $this->merchant_id = isset($data['merchant_id']) ? $data['merchant_id'] : null;
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
     * Gets merchant_id.
     *
     * @return string|null
     */
    public function getMerchantId()
    {
        return $this->merchant_id;
    }

    /**
     * Sets merchant_id.
     *
     * @param string|null $merchant_id the account identifier for a PayPal account
     *
     * @return $this
     */
    public function setMerchantId($merchant_id = null)
    {
        $this->merchant_id = $merchant_id;

        return $this;
    }
}
