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

class P24
{
    /**
     * The full name representation like Mr J Smith.
     *
     * @var string|null
     */
    protected $name;

    /**
     * The internationalized email address.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; Up to 64 characters are allowed before and 255 characters are allowed after the &lt;code&gt;@&lt;/code&gt; sign. However, the generally accepted maximum length for an email address is 254 characters. The pattern verifies that an unquoted &lt;code&gt;@&lt;/code&gt; sign exists.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $email;

    /**
     * The [two-character ISO 3166-1 code](/api/rest/reference/country-codes/) that identifies the country or region.&lt;blockquote&gt;&lt;strong&gt;Note:&lt;/strong&gt; The country code for Great Britain is &lt;code&gt;GB&lt;/code&gt; and not &lt;code&gt;UK&lt;/code&gt; as used in the top-level domain names for that country. Use the &#x60;C2&#x60; country code for China worldwide for comparable uncontrolled price (CUP) method, bank card, and cross-border transactions.&lt;/blockquote&gt;
     *
     * @var string|null
     */
    protected $country_code;

    /**
     * P24 generated payment description.
     *
     * @var string|null
     */
    protected $payment_descriptor;

    /**
     * Numeric identifier of the payment scheme or bank used for the payment.
     *
     * @var string|null
     */
    protected $method_id;

    /**
     * Friendly name of the payment scheme or bank used for the payment.
     *
     * @var string|null
     */
    protected $method_description;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->name = isset($data['name']) ? $data['name'] : null;
        $this->email = isset($data['email']) ? $data['email'] : null;
        $this->country_code = isset($data['country_code']) ? $data['country_code'] : null;
        $this->payment_descriptor = isset($data['payment_descriptor']) ? $data['payment_descriptor'] : null;
        $this->method_id = isset($data['method_id']) ? $data['method_id'] : null;
        $this->method_description = isset($data['method_description']) ? $data['method_description'] : null;
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
     * Gets email.
     *
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets email.
     *
     * @param string|null $email The internationalized email address.<blockquote><strong>Note:</strong> Up to 64 characters are allowed before and 255 characters are allowed after the <code>@</code> sign. However, the generally accepted maximum length for an email address is 254 characters. The pattern verifies that an unquoted <code>@</code> sign exists.</blockquote>
     *
     * @return $this
     */
    public function setEmail($email = null)
    {
        $this->email = $email;

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
     * Gets payment_descriptor.
     *
     * @return string|null
     */
    public function getPaymentDescriptor()
    {
        return $this->payment_descriptor;
    }

    /**
     * Sets payment_descriptor.
     *
     * @param string|null $payment_descriptor P24 generated payment description
     *
     * @return $this
     */
    public function setPaymentDescriptor($payment_descriptor = null)
    {
        $this->payment_descriptor = $payment_descriptor;

        return $this;
    }

    /**
     * Gets method_id.
     *
     * @return string|null
     */
    public function getMethodId()
    {
        return $this->method_id;
    }

    /**
     * Sets method_id.
     *
     * @param string|null $method_id numeric identifier of the payment scheme or bank used for the payment
     *
     * @return $this
     */
    public function setMethodId($method_id = null)
    {
        $this->method_id = $method_id;

        return $this;
    }

    /**
     * Gets method_description.
     *
     * @return string|null
     */
    public function getMethodDescription()
    {
        return $this->method_description;
    }

    /**
     * Sets method_description.
     *
     * @param string|null $method_description friendly name of the payment scheme or bank used for the payment
     *
     * @return $this
     */
    public function setMethodDescription($method_description = null)
    {
        $this->method_description = $method_description;

        return $this;
    }
}
