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

class ExchangeRate
{
    /**
     * The [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies the currency.
     *
     * @var string|null
     */
    protected $source_currency;

    /**
     * The [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies the currency.
     *
     * @var string|null
     */
    protected $target_currency;

    /**
     * The target currency amount. Equivalent to one unit of the source currency. Formatted as integer or decimal value with one to 15 digits to the right of the decimal point.
     *
     * @var string|null
     */
    protected $value;

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->source_currency = isset($data['source_currency']) ? $data['source_currency'] : null;
        $this->target_currency = isset($data['target_currency']) ? $data['target_currency'] : null;
        $this->value = isset($data['value']) ? $data['value'] : null;
    }

    /**
     * Gets source_currency.
     *
     * @return string|null
     */
    public function getSourceCurrency()
    {
        return $this->source_currency;
    }

    /**
     * Sets source_currency.
     *
     * @param string|null $source_currency the [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies the currency
     *
     * @return $this
     */
    public function setSourceCurrency($source_currency = null)
    {
        $this->source_currency = $source_currency;

        return $this;
    }

    /**
     * Gets target_currency.
     *
     * @return string|null
     */
    public function getTargetCurrency()
    {
        return $this->target_currency;
    }

    /**
     * Sets target_currency.
     *
     * @param string|null $target_currency the [three-character ISO-4217 currency code](/api/rest/reference/currency-codes/) that identifies the currency
     *
     * @return $this
     */
    public function setTargetCurrency($target_currency = null)
    {
        $this->target_currency = $target_currency;

        return $this;
    }

    /**
     * Gets value.
     *
     * @return string|null
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Sets value.
     *
     * @param string|null $value The target currency amount. Equivalent to one unit of the source currency. Formatted as integer or decimal value with one to 15 digits to the right of the decimal point.
     *
     * @return $this
     */
    public function setValue($value = null)
    {
        $this->value = $value;

        return $this;
    }
}
